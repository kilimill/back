<?php

namespace Tests\Unit;

use App\Rules\MobilePhoneRule;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class MobilePhoneRuleTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @dataProvider dataInvalidPhone
     */
    public function testMobilePhoneRuleDoesNotPassWhenWrongFormat(string $phone)
    {
       $result = (new MobilePhoneRule())->passes('phone', $phone);
       $this->assertFalse($result);
    }

    public function testMobilePhoneRulePassesWhenCorrectFormat()
    {
        $result = (new MobilePhoneRule())->passes('phone', '79991112233');
        $this->assertTrue($result);
    }

    public function dataInvalidPhone(): array
    {
        return [
            'wrong_phone_1' => ['89991112233'],
            'wrong_phone_2' => ['+79991112233'],
            'wrong_phone_3' => ['9991112233'],
            'wrong_phone_4' => ['test'],
            'wrong_phone_5' => [''],
            'wrong_phone_6' => ['+7 (999) 888-11-22'],
            'wrong_phone_7' => ['+7(999)888-11-22'],
            'wrong_phone_9' => ['+7 999 888 11 22'],
            'wrong_phone_10' => ['+1 999 888 11 22'],
            'wrong_phone_11' => ['123123'],
            'wrong_phone_12' => ['123-123'],
            'wrong_phone_13' => ['12-31-23'],
            'wrong_phone_14' => ['8800'],
            'wrong_phone_15' => ['7'],
        ];
    }
}
