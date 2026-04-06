<?php

namespace App\Domain\Banking\ValueObjects;

final readonly class UserId
{
    public function __construct(public int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('User id must be positive.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
