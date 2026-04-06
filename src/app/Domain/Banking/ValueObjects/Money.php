<?php

namespace App\Domain\Banking\ValueObjects;

final readonly class Money implements \Stringable
{
    private function __construct(private string $amount) {}

    public static function zero(): self
    {
        return new self('0.00');
    }

    /**
     * @param  non-empty-string  $decimal  Amount in BRL with up to two fractional digits (e.g. "100", "100.5", "100.50")
     */
    public static function fromDecimal(string $decimal): self
    {
        $normalized = str_replace(',', '.', trim($decimal));
        if ($normalized === '' || ! preg_match('/^\d+(\.\d+)?$/', $normalized)) {
            throw new \InvalidArgumentException('Invalid money amount.');
        }

        $rounded = self::normalizeToTwoDecimals($normalized);
        if (bccomp($rounded, '0', 2) < 0) {
            throw new \InvalidArgumentException('Money amount cannot be negative.');
        }

        return new self(bcadd($rounded, '0', 2));
    }

    public function add(self $other): self
    {
        return new self(bcadd($this->amount, $other->amount, 2));
    }

    /**
     * @param  non-empty-string  $rate  Decimal rate applied as balance × (1 + rate); e.g. "0.001" for 0,1%.
     */
    public function multiplyByOnePlusRate(string $rate): self
    {
        $factor = bcadd('1', $rate, 12);
        $raw = bcmul($this->amount, $factor, 12);

        return new self(self::roundHalfUpToTwoDecimals($raw));
    }

    public function greaterThanZero(): bool
    {
        return bccomp($this->amount, '0', 2) === 1;
    }

    public function equalsAmount(self $other): bool
    {
        return bccomp($this->amount, $other->amount, 2) === 0;
    }

    public function toDecimal(): string
    {
        return $this->amount;
    }

    public function __toString(): string
    {
        return $this->amount;
    }

    private static function normalizeToTwoDecimals(string $decimal): string
    {
        if (! str_contains($decimal, '.')) {
            return bcadd($decimal, '0', 2);
        }

        [$whole, $frac] = explode('.', $decimal, 2);
        $frac = substr(str_pad($frac, 2, '0', STR_PAD_RIGHT), 0, 2);

        return bcadd($whole.'.'.$frac, '0', 2);
    }

    private static function roundHalfUpToTwoDecimals(string $positiveAmount): string
    {
        if (bccomp($positiveAmount, '0', 12) === 0) {
            return '0.00';
        }

        $negative = bccomp($positiveAmount, '0', 12) < 0;
        $value = $negative ? bcmul($positiveAmount, '-1', 12) : $positiveAmount;

        $scaled = bcmul($value, '100', 10);
        $rounded = bcadd($scaled, '0.5', 0);
        $integerPart = bcdiv($rounded, '1', 0);
        $result = bcdiv($integerPart, '100', 2);

        if ($negative) {
            $result = bcmul($result, '-1', 2);
        }

        if (bccomp($result, '0', 2) < 0) {
            return '0.00';
        }

        return $result;
    }
}
