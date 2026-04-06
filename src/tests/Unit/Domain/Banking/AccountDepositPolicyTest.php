<?php

namespace Tests\Unit\Domain\Banking;

use App\Domain\Banking\Policies\AccountDepositPolicy;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class AccountDepositPolicyTest extends TestCase
{
    public function test_savings_credits_stated_amount_only(): void
    {
        $policy = new AccountDepositPolicy;
        $stated = Money::fromDecimal('100.00');

        $credited = $policy->creditedAmount(AccountType::Savings, $stated);

        $this->assertTrue($credited->equalsAmount(Money::fromDecimal('100.00')));
    }

    public function test_checking_adds_fifty_cents_bonus(): void
    {
        $policy = new AccountDepositPolicy;
        $stated = Money::fromDecimal('100.00');

        $credited = $policy->creditedAmount(AccountType::Checking, $stated);

        $this->assertTrue($credited->equalsAmount(Money::fromDecimal('100.50')));
    }

    public function test_investments_adds_fifty_cents_bonus(): void
    {
        $policy = new AccountDepositPolicy;
        $stated = Money::fromDecimal('200.00');

        $credited = $policy->creditedAmount(AccountType::Investments, $stated);

        $this->assertTrue($credited->equalsAmount(Money::fromDecimal('200.50')));
    }
}
