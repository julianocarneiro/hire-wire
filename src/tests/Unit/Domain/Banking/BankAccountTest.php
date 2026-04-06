<?php

namespace Tests\Unit\Domain\Banking;

use App\Domain\Banking\Entities\BankAccount;
use App\Domain\Banking\Exceptions\InvalidDepositAmountException;
use App\Domain\Banking\Policies\AccountDepositPolicy;
use App\Domain\Banking\Policies\MonthlyAdjustmentPolicy;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class BankAccountTest extends TestCase
{
    public function test_deposit_on_checking_applies_bonus_and_returns_balance(): void
    {
        $account = new BankAccount(
            null,
            new UserId(1),
            AccountType::Checking,
            Money::zero(),
        );

        $account->deposit(Money::fromDecimal('100.00'), new AccountDepositPolicy);

        $this->assertTrue($account->balance()->equalsAmount(Money::fromDecimal('100.50')));
    }

    public function test_deposit_on_savings_does_not_apply_bonus(): void
    {
        $account = new BankAccount(
            null,
            new UserId(1),
            AccountType::Savings,
            Money::zero(),
        );

        $account->deposit(Money::fromDecimal('50.00'), new AccountDepositPolicy);

        $this->assertTrue($account->balance()->equalsAmount(Money::fromDecimal('50.00')));
    }

    public function test_it_rejects_non_positive_deposit(): void
    {
        $account = new BankAccount(
            null,
            new UserId(1),
            AccountType::Savings,
            Money::zero(),
        );

        $this->expectException(InvalidDepositAmountException::class);

        $account->deposit(Money::zero(), new AccountDepositPolicy);
    }

    public function test_monthly_adjustment_on_savings(): void
    {
        $account = new BankAccount(
            null,
            new UserId(1),
            AccountType::Savings,
            Money::fromDecimal('10000.00'),
        );

        $account->applyMonthlyAdjustment(new MonthlyAdjustmentPolicy);

        $this->assertTrue($account->balance()->equalsAmount(Money::fromDecimal('10000.10')));
    }
}
