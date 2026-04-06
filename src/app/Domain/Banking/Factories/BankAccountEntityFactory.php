<?php

namespace App\Domain\Banking\Factories;

use App\Domain\Banking\Entities\BankAccount;
use App\Domain\Banking\Entities\CheckingAccount;
use App\Domain\Banking\Entities\InvestmentsAccount;
use App\Domain\Banking\Entities\SavingsAccount;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;

final class BankAccountEntityFactory
{
    public static function create(
        ?BankAccountId $id,
        UserId $userId,
        AccountType $type,
        Money $balance,
    ): BankAccount {
        return match ($type) {
            AccountType::Savings => new SavingsAccount($id, $userId, $balance),
            AccountType::Checking => new CheckingAccount($id, $userId, $balance),
            AccountType::Investments => new InvestmentsAccount($id, $userId, $balance),
        };
    }
}
