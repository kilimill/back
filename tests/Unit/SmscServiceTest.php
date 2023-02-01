<?php

namespace Tests\Unit;

use App\Exceptions\SmscSendException;
use App\Http\Services\SmscService;
use App\Models\ConfirmationCode;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class SmscServiceTest extends TestCase
{
    use DatabaseMigrations;

    private SmscService $smscService;

    public function setUp(): void
    {
        parent::setUp();

        $this->smscService = SmscService::create();
    }

    /**
     * @throws SmscSendException
     */
    public function testSmscServiceCanSendCodeForTesting(): void
    {
        $this->assertCount(0, ConfirmationCode::all());
        $code = $this->smscService->sendCode('78888888888');
        $this->assertEquals(11111, $code);
        $this->assertCount(0, ConfirmationCode::all());
    }

    /**
     * @throws SmscSendException
     */
    public function testSmscServiceCanSendCodeBySmsAndResponseSuccess(): void
    {
        config(['nollo.smsc.confirmation_type' => 'sms']);

        Http::fake([
            '*' => Http::response([
                'id' => 1,
                'cnt' => 1,
            ]),
        ]);

        $phone = '78888888888';

        $this->assertCount(0, ConfirmationCode::all());
        $code = $this->smscService->sendCode($phone);
        Http::assertSentCount(1);
        $this->assertCount(1, ConfirmationCode::all());
        $this->assertDatabaseHas((new ConfirmationCode())->getTable(), [
            'id' => 1,
            'user_id' => null,
            'status_id' => ConfirmationCode::STATUS_ID_SEND,
            'type_id' => ConfirmationCode::TYPE_ID_SMS,
            'phone' =>  $phone,
            'code' => $code,
            'message' => "Ваш код: $code",
            'error' => null,
        ]);

        config(['nollo.smsc.confirmation_type' => 'test']);
    }

    /**
     * @throws SmscSendException
     */
    public function testSmscServiceCanSendCodeBySmsAndResponseError(): void
    {
        config(['nollo.smsc.confirmation_type' => 'sms']);

        $this->withoutExceptionHandling();
        $this->expectException(SmscSendException::class);
        $this->expectExceptionMessage('Ошибка при отправке кода.');
        $this->expectExceptionCode(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);

        Http::fake([
            '*' => Http::response([
                'error' => "some text",
                'error_code' => SmscService::ERROR_CODE_3,
                'id' => 1,
            ]),
        ]);

        $phone = '78888888888';

        $this->assertCount(0, ConfirmationCode::all());
        $this->smscService->sendCode($phone);
        Http::assertSentCount(1);
        $this->assertCount(1, ConfirmationCode::all());
        $this->assertDatabaseHas((new ConfirmationCode())->getTable(), [
            'id' => 1,
            'user_id' => null,
            'status_id' => ConfirmationCode::STATUS_ID_ERROR,
            'type_id' => ConfirmationCode::TYPE_ID_SMS,
            'phone' =>  $phone,
            'code' => null,
            'message' => null,
            'error' => SmscService::ERRORS[SmscService::ERROR_CODE_3],
        ]);

        config(['nollo.smsc.confirmation_type' => 'test']);
    }

    /**
     * @throws SmscSendException
     */
    public function testSmscServiceCanSendCodeByCallAndResponseSuccess(): void
    {
        config(['nollo.smsc.confirmation_type' => 'call']);

        Http::fake([
            '*' => Http::response([
                'id' => 1,
                'cnt' => 1,
                'code' => $code = 123123,
            ]),
        ]);

        $phone = '78888888888';

        $this->assertCount(0, ConfirmationCode::all());
        $this->smscService->sendCode($phone);
        Http::assertSentCount(1);
        $this->assertCount(1, ConfirmationCode::all());
        $this->assertDatabaseHas((new ConfirmationCode())->getTable(), [
            'id' => 1,
            'user_id' => null,
            'status_id' => ConfirmationCode::STATUS_ID_SEND,
            'type_id' => ConfirmationCode::TYPE_ID_CALL,
            'phone' =>  $phone,
            'code' => $code,
            'message' => null,
            'error' => null,
        ]);

        config(['nollo.smsc.confirmation_type' => 'test']);
    }

    /**
     * @throws SmscSendException
     */
    public function testSmscServiceCanSendCodeByCallAndResponseError(): void
    {
        config(['nollo.smsc.confirmation_type' => 'call']);

        $this->withoutExceptionHandling();
        $this->expectException(SmscSendException::class);
        $this->expectExceptionMessage('Ошибка при отправке кода.');
        $this->expectExceptionCode(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);

        Http::fake([
            '*' => Http::response([
                'error' => "some text",
                'error_code' => SmscService::ERROR_CODE_3,
                'id' => 1,
            ]),
        ]);

        $phone = '78888888888';

        $this->assertCount(0, ConfirmationCode::all());
        $this->smscService->sendCode($phone);
        Http::assertSentCount(1);
        $this->assertCount(1, ConfirmationCode::all());
        $this->assertDatabaseHas((new ConfirmationCode())->getTable(), [
            'id' => 1,
            'user_id' => null,
            'status_id' => ConfirmationCode::STATUS_ID_ERROR,
            'type_id' => ConfirmationCode::TYPE_ID_CALL,
            'phone' =>  $phone,
            'code' => null,
            'message' => null,
            'error' => SmscService::ERRORS[SmscService::ERROR_CODE_3],
        ]);

        config(['nollo.smsc.confirmation_type' => 'test']);
    }
}
