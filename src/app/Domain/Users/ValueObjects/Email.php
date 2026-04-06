<?php

namespace App\Domain\Users\ValueObjects;

use App\Domain\Users\Exceptions\InvalidEmailException;

final readonly class Email implements \Stringable
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidEmailException('E-mail cannot be empty.');
        }

        $normalized = mb_strtolower($trimmed);
        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmailException('Invalid e-mail address.');
        }

        if (strlen($normalized) > 255) {
            throw new InvalidEmailException('E-mail must not exceed 255 characters.');
        }

        return new self($normalized);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
