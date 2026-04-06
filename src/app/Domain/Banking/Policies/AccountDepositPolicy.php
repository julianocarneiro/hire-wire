<?php

namespace App\Domain\Banking\Policies;

use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\Money;

final class AccountDepositPolicy
{
    private const BONUS_REAIS = '0.50';

    public function creditedAmount(AccountType $type, Money $statedDeposit): Money
    {
        return match ($type) {
            AccountType::Savings => $statedDeposit,
            AccountType::Checking, AccountType::Investments => $statedDeposit->add(
                Money::fromDecimal(self::BONUS_REAIS)
            ),
        };
    }
}
