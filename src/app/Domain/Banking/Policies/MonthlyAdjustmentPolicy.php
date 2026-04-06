<?php

namespace App\Domain\Banking\Policies;

use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\Money;

final class MonthlyAdjustmentPolicy
{
    /** 0,001% a.m. */
    private const SAVINGS_RATE = '0.00001';

    /** 0,1% a.m. */
    private const CHECKING_AND_INVESTMENTS_RATE = '0.001';

    public function adjust(AccountType $type, Money $balance): Money
    {
        $rate = match ($type) {
            AccountType::Savings => self::SAVINGS_RATE,
            AccountType::Checking, AccountType::Investments => self::CHECKING_AND_INVESTMENTS_RATE,
        };

        return $balance->multiplyByOnePlusRate($rate);
    }
}
