<?php

namespace App\Domain\Banking\Entities;

use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;

final class InvestmentsAccount extends BankAccount
{
    public function __construct(
        ?BankAccountId $id,
        UserId $userId,
        Money $balance,
    ) {
        parent::__construct($id, $userId, $balance);
    }

    public function accountType(): AccountType
    {
        return AccountType::Investments;
    }
}
