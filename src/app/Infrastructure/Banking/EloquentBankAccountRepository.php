<?php

namespace App\Infrastructure\Banking;

use App\Domain\Banking\Entities\BankAccount as BankAccountEntity;
use App\Domain\Banking\Factories\BankAccountEntityFactory;
use App\Domain\Banking\Repositories\BankAccountRepositoryInterface;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;
use App\Models\BankAccount as BankAccountModel;

final class EloquentBankAccountRepository implements BankAccountRepositoryInterface
{
    public function listForUser(UserId $userId): array
    {
        return BankAccountModel::query()
            ->where('user_id', $userId->value)
            ->orderBy('type')
            ->get()
            ->map(fn (BankAccountModel $row) => $this->toDomain($row))
            ->all();
    }

    public function findByIdForUser(BankAccountId $id, UserId $userId): ?BankAccountEntity
    {
        $row = BankAccountModel::query()
            ->whereKey($id->value)
            ->where('user_id', $userId->value)
            ->first();

        return $row ? $this->toDomain($row) : null;
    }

    public function findByIdForUpdate(BankAccountId $id, UserId $userId): ?BankAccountEntity
    {
        $row = BankAccountModel::query()
            ->whereKey($id->value)
            ->where('user_id', $userId->value)
            ->lockForUpdate()
            ->first();

        return $row ? $this->toDomain($row) : null;
    }

    public function save(BankAccountEntity $account): void
    {
        $id = $account->id();
        $attributes = [
            'user_id' => $account->userId()->value,
            'type' => $account->type()->value,
            'balance' => $account->balance()->toDecimal(),
        ];

        if ($id === null) {
            $model = new BankAccountModel($attributes);
            $model->save();
            $account->assignId(new BankAccountId((int) $model->getKey()));

            return;
        }

        BankAccountModel::query()->whereKey($id->value)->update($attributes);
    }

    public function delete(BankAccountId $id, UserId $userId): bool
    {
        return BankAccountModel::query()
            ->whereKey($id->value)
            ->where('user_id', $userId->value)
            ->delete() > 0;
    }

    private function toDomain(BankAccountModel $model): BankAccountEntity
    {
        $type = AccountType::from($model->type);

        return BankAccountEntityFactory::create(
            new BankAccountId((int) $model->getKey()),
            new UserId((int) $model->user_id),
            $type,
            Money::fromDecimal((string) $model->balance),
        );
    }
}
