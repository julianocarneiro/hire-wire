<?php

namespace App\Domain\Users\ValueObjects;

use App\Domain\Users\Exceptions\InvalidCpfException;

final readonly class Cpf implements \Stringable
{
    private function __construct(private string $digits) {}

    public static function fromString(string $value): self
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        if (strlen($digits) !== 11) {
            throw new InvalidCpfException('CPF must contain exactly 11 digits.');
        }

        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            throw new InvalidCpfException('Invalid CPF.');
        }

        if (! self::checkDigits($digits)) {
            throw new InvalidCpfException('Invalid CPF verification digits.');
        }

        return new self($digits);
    }

    public function equals(self $other): bool
    {
        return $this->digits === $other->digits;
    }

    public function toString(): string
    {
        return $this->digits;
    }

    public function __toString(): string
    {
        return $this->digits;
    }

    private static function checkDigits(string $cpf): bool
    {
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($d !== (int) $cpf[$t]) {
                return false;
            }
        }

        return true;
    }
}
