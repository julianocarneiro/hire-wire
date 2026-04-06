<?php

namespace Tests\Unit\Domain\Banking;

use App\Domain\Banking\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_add_rounds_to_two_decimals(): void
    {
        $a = Money::fromDecimal('100.00');
        $b = Money::fromDecimal('0.50');

        $this->assertTrue($a->add($b)->equalsAmount(Money::fromDecimal('100.50')));
    }

    public function test_monthly_adjustment_savings_rate(): void
    {
        $balance = Money::fromDecimal('10000.00');
        $adjusted = $balance->multiplyByOnePlusRate('0.00001');

        $this->assertTrue($adjusted->equalsAmount(Money::fromDecimal('10000.10')));
    }

    public function test_monthly_adjustment_checking_rate(): void
    {
        $balance = Money::fromDecimal('10000.00');
        $adjusted = $balance->multiplyByOnePlusRate('0.001');

        $this->assertTrue($adjusted->equalsAmount(Money::fromDecimal('10010.00')));
    }

    public function test_zero_is_not_greater_than_zero(): void
    {
        $this->assertFalse(Money::zero()->greaterThanZero());
    }
}
