<?php

namespace App\Domain\Users\Entities;

use App\Domain\Users\ValueObjects\Cpf;
use App\Domain\Users\ValueObjects\Email;

final readonly class User
{
    public function __construct(
        public ?int $id,
        public string $name,
        public Cpf $cpf,
        public Email $email,
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Name cannot be empty.');
        }
    }

    public function withId(int $id): self
    {
        if ($this->id !== null) {
            throw new \LogicException('Identity has already been assigned.');
        }
        if ($id <= 0) {
            throw new \InvalidArgumentException('User id must be positive.');
        }

        return new self($id, $this->name, $this->cpf, $this->email);
    }
}
