<?php

namespace App\Http\Services;

use App\Exceptions\SmscSendException;
use App\Models\ConfirmationCode;
use Illuminate\Support\Facades\Http;

class SmscService
{
    use ServiceInstance;

    const ERROR_CODE_1 = 1;
    const ERROR_CODE_2 = 2;
    const ERROR_CODE_3 = 3;
    const ERROR_CODE_4 = 4;
    const ERROR_CODE_5 = 5;
    const ERROR_CODE_6 = 6;
    const ERROR_CODE_7 = 7;
    const ERROR_CODE_8 = 8;
    const ERROR_CODE_9 = 9;

    const ERRORS = [
        self::ERROR_CODE_1 => 'Ошибка в параметрах.',
        self::ERROR_CODE_2 => 'Неверный логин или пароль. Также возникает при попытке отправки сообщения с IP-адреса, не входящего в список разрешенных Клиентом (если такой список был настроен Клиентом ранее).',
        self::ERROR_CODE_3 => 'Недостаточно средств на счете Клиента.',
        self::ERROR_CODE_4 => 'IP-адрес временно заблокирован из-за частых ошибок в запросах.',
        self::ERROR_CODE_5 => 'Неверный формат даты.',
        self::ERROR_CODE_6 => 'Сообщение запрещено (по тексту или по имени отправителя). Также данная ошибка возникает при попытке отправки массовых и (или) рекламных сообщений без заключенного договора.',
        self::ERROR_CODE_7 => 'Неверный формат номера телефона.',
        self::ERROR_CODE_8 => 'Сообщение на указанный номер не может быть доставлено.',
        self::ERROR_CODE_9 => 'Отправка более одного одинакового запроса на передачу SMS-сообщения либо более пяти одинаковых запросов на получение стоимости сообщения в течение минуты. Данная ошибка возникает также при попытке отправки пятнадцати и более запросов одновременно с разных подключений под одним логином (too many concurrent requests).',
    ];

    /**
     * @throws SmscSendException
     */
    public function sendCode(string $phone): int
    {
        return match (config('nollo.smsc.confirmation_type')) {
            'sms' => $this->sendSms($phone),
            'call' => $this->sendPhoneCall($phone),
            default => 11111,
        };
    }

    /**
     * @throws SmscSendException
     */
    private function sendSms(string $phone): int
    {
        $code = rand(10000, 99999);
        $message = "Ваш код: $code";

        $confirmationCode = $this->initConfirmationCode(ConfirmationCode::TYPE_ID_SMS, $phone, $message);

        $request = [
            'id' => $confirmationCode->getKey(),
            'charset' => 'utf-8',
            'phones' => $phone,
            'mes' => $message,
            'sender' => config('nollo.smsc.sender'),
        ];

        $this->send($request, $confirmationCode);
        $confirmationCode->code = $code;
        $confirmationCode->save();

        return $code;
    }

    /**
     * @throws SmscSendException
     */
    private function sendPhoneCall(string $phone): int
    {
        $confirmationCode = $this->initConfirmationCode(ConfirmationCode::TYPE_ID_CALL, $phone);

        $request = [
            'id' => $confirmationCode->getKey(),
            'phones' => $phone,
            'mes' => 'code',
            'call' => 1,
        ];

        $code = $this->send($request, $confirmationCode);
        $confirmationCode->code = $code;
        $confirmationCode->save();

        return $code;
    }

    private function initConfirmationCode(int $typeId, string $phone, ?string $message = null): ConfirmationCode
    {
        $confirmationCode = new ConfirmationCode();
        $confirmationCode->user_id = auth_user_or_null()?->getKey();
        $confirmationCode->status_id = ConfirmationCode::STATUS_ID_INIT;
        $confirmationCode->type_id = $typeId;
        $confirmationCode->phone = $phone;
        $confirmationCode->message = $message;
        $confirmationCode->save();

        return $confirmationCode;
    }

    /**
     * @throws SmscSendException
     */
    private function send(array $request, ConfirmationCode $confirmationCode): ?int
    {
        $confirmationCode->status_id = ConfirmationCode::STATUS_ID_SEND;

        $response = Http::acceptJson()->get('https://smsc.ru/sys/send.php', [
            'fmt' => 3,
            'login' => config('nollo.smsc.login'),
            'psw' => config('nollo.smsc.password'),
        ] + $request)->json();

        if ($response['error_code'] ?? null) {
            $confirmationCode->status_id = ConfirmationCode::STATUS_ID_ERROR;
            $confirmationCode->error = self::ERRORS[$response['error_code']];
            $confirmationCode->save();

            throw new SmscSendException();
        }

        return $response['code'] ?? null;
    }
}
