<?php

namespace App\Domain\Banking\ValueObjects;

final readonly class BankAccountId
{
    public function __construct(public int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Bank account id must be positive.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
