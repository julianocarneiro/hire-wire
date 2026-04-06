<?php

namespace App\Domain\Users\Repositories;

use App\Domain\Users\Entities\User;
use App\Domain\Users\ValueObjects\Cpf;
use App\Domain\Users\ValueObjects\Email;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByCpf(Cpf $cpf): ?User;

    public function findByEmail(Email $email): ?User;

    public function existsWithCpf(Cpf $cpf, ?int $exceptUserId = null): bool;

    public function existsWithEmail(Email $email, ?int $exceptUserId = null): bool;

    public function save(User $user): User;
}
