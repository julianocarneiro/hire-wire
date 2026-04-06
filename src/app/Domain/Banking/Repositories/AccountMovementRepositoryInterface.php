<?php

namespace App\Domain\Banking\Repositories;

use App\Domain\Banking\ValueObjects\AccountMovementType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;

interface AccountMovementRepositoryInterface
{
    /**
     * Persiste um movimento. A conta deve pertencer ao utilizador indicado.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function append(
        BankAccountId $bankAccountId,
        UserId $userId,
        AccountMovementType $type,
        Money $amount,
        Money $balanceAfter,
        ?array $metadata,
    ): void;

    /**
     * @return list<array{id: int, type: string, amount: string, balance_after: string|null, created_at: string}>
     */
    public function listForAccount(BankAccountId $id, UserId $userId): array;
}
