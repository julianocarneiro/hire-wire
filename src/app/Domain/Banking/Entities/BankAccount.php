<?php

namespace App\Domain\Banking\Entities;

use App\Domain\Banking\Exceptions\InvalidDepositAmountException;
use App\Domain\Banking\Policies\AccountDepositPolicy;
use App\Domain\Banking\Policies\MonthlyAdjustmentPolicy;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;

final class BankAccount
{
    public function __construct(
        private ?BankAccountId $id,
        private readonly UserId $userId,
        private readonly AccountType $type,
        private Money $balance,
    ) {}

    public function id(): ?BankAccountId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function type(): AccountType
    {
        return $this->type;
    }

    public function balance(): Money
    {
        return $this->balance;
    }

    public function assignId(BankAccountId $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('Bank account already has an identity.');
        }
        $this->id = $id;
    }

    public function deposit(Money $statedAmount, AccountDepositPolicy $depositPolicy): void
    {
        if (! $statedAmount->greaterThanZero()) {
            throw new InvalidDepositAmountException('Deposit amount must be greater than zero.');
        }

        $credited = $depositPolicy->creditedAmount($this->type, $statedAmount);
        $this->balance = $this->balance->add($credited);
    }

    public function applyMonthlyAdjustment(MonthlyAdjustmentPolicy $policy): void
    {
        $this->balance = $policy->adjust($this->type, $this->balance);
    }
}
