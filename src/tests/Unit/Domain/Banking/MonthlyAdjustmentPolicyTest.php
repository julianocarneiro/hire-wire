<?php

namespace Tests\Unit\Domain\Banking;

use App\Domain\Banking\Policies\MonthlyAdjustmentPolicy;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MonthlyAdjustmentPolicyTest extends TestCase
{
    public function test_savings_applies_zero_point_zero_zero_one_percent(): void
    {
        $policy = new MonthlyAdjustmentPolicy;
        $balance = Money::fromDecimal('10000.00');

        $adjusted = $policy->adjust(AccountType::Savings, $balance);

        $this->assertTrue($adjusted->equalsAmount(Money::fromDecimal('10000.10')));
    }

    public function test_checking_applies_zero_point_one_percent(): void
    {
        $policy = new MonthlyAdjustmentPolicy;
        $balance = Money::fromDecimal('10000.00');

        $adjusted = $policy->adjust(AccountType::Checking, $balance);

        $this->assertTrue($adjusted->equalsAmount(Money::fromDecimal('10010.00')));
    }

    public function test_investments_applies_zero_point_one_percent(): void
    {
        $policy = new MonthlyAdjustmentPolicy;
        $balance = Money::fromDecimal('1000.00');

        $adjusted = $policy->adjust(AccountType::Investments, $balance);

        $this->assertTrue($adjusted->equalsAmount(Money::fromDecimal('1001.00')));
    }
}
