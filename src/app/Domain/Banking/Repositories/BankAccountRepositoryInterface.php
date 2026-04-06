<?php

namespace App\Domain\Banking\Repositories;

use App\Domain\Banking\Entities\BankAccount;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\UserId;

interface BankAccountRepositoryInterface
{
    public function findByIdForUser(BankAccountId $id, UserId $userId): ?BankAccount;

    public function findByIdForUpdate(BankAccountId $id, UserId $userId): ?BankAccount;

    public function save(BankAccount $account): void;
}
