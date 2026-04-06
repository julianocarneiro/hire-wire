<?php

namespace App\Domain\Banking\Repositories;

use App\Domain\Banking\Entities\BankAccount;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\UserId;

interface BankAccountRepositoryInterface
{
    /**
     * @return list<BankAccount>
     */
    public function listForUser(UserId $userId): array;

    public function findByIdForUser(BankAccountId $id, UserId $userId): ?BankAccount;

    public function findByIdForUpdate(BankAccountId $id, UserId $userId): ?BankAccount;

    public function save(BankAccount $account): void;

    public function delete(BankAccountId $id, UserId $userId): bool;
}
