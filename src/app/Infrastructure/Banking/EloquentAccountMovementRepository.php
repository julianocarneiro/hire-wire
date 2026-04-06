<?php

namespace App\Infrastructure\Banking;

use App\Domain\Banking\Repositories\AccountMovementRepositoryInterface;
use App\Domain\Banking\ValueObjects\AccountMovementType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;
use App\Models\AccountMovement;
use App\Models\BankAccount as BankAccountModel;

final class EloquentAccountMovementRepository implements AccountMovementRepositoryInterface
{
    public function append(
        BankAccountId $bankAccountId,
        UserId $userId,
        AccountMovementType $type,
        Money $amount,
        Money $balanceAfter,
        ?array $metadata,
    ): void {
        $owned = BankAccountModel::query()
            ->whereKey($bankAccountId->value)
            ->where('user_id', $userId->value)
            ->exists();

        if (! $owned) {
            throw new \LogicException('Cannot append movement for account not owned by user.');
        }

        AccountMovement::query()->create([
            'bank_account_id' => $bankAccountId->value,
            'type' => $type->value,
            'amount' => $amount->toDecimal(),
            'balance_after' => $balanceAfter->toDecimal(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * @return list<array{id: int, type: string, amount: string, balance_after: string|null, created_at: string}>
     */
    public function listForAccount(BankAccountId $id, UserId $userId): array
    {
        return AccountMovement::query()
            ->where('bank_account_id', $id->value)
            ->whereHas('bankAccount', static function ($q) use ($userId): void {
                $q->where('user_id', $userId->value);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(static function (AccountMovement $row): array {
                return [
                    'id' => (int) $row->getKey(),
                    'type' => $row->type,
                    'amount' => (string) $row->amount,
                    'balance_after' => $row->balance_after !== null ? (string) $row->balance_after : null,
                    'created_at' => $row->created_at->toAtomString(),
                ];
            })
            ->all();
    }
}
