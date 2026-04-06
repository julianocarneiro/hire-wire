<?php

namespace App\Infrastructure\Banking;

use App\Domain\Banking\Entities\BankAccount as BankAccountEntity;
use App\Domain\Banking\Repositories\BankAccountRepositoryInterface;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;
use App\Models\BankAccount as BankAccountModel;

final class EloquentBankAccountRepository implements BankAccountRepositoryInterface
{
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

    private function toDomain(BankAccountModel $model): BankAccountEntity
    {
        $type = AccountType::from($model->type);

        return new BankAccountEntity(
            new BankAccountId((int) $model->getKey()),
            new UserId((int) $model->user_id),
            $type,
            Money::fromDecimal((string) $model->balance),
        );
    }
}
