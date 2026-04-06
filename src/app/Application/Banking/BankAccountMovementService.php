<?php

namespace App\Application\Banking;

use App\Domain\Banking\Exceptions\InvalidDepositAmountException;
use App\Domain\Banking\Policies\AccountDepositPolicy;
use App\Domain\Banking\Policies\MonthlyAdjustmentPolicy;
use App\Domain\Banking\Repositories\AccountMovementRepositoryInterface;
use App\Domain\Banking\Repositories\BankAccountRepositoryInterface;
use App\Domain\Banking\ValueObjects\AccountMovementType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BankAccountMovementService
{
    public function __construct(
        private BankAccountRepositoryInterface $accounts,
        private AccountMovementRepositoryInterface $movements,
    ) {}

    public function recordDeposit(BankAccountId $accountId, UserId $userId, string $amountDecimal): void
    {
        DB::transaction(function () use ($accountId, $userId, $amountDecimal): void {
            $entity = $this->accounts->findByIdForUpdate($accountId, $userId);
            if ($entity === null) {
                abort(404);
            }

            $before = $entity->balance();
            $stated = Money::fromDecimal($amountDecimal);

            try {
                $entity->deposit($stated, new AccountDepositPolicy);
            } catch (InvalidDepositAmountException $e) {
                throw ValidationException::withMessages([
                    'amount' => [$e->getMessage()],
                ]);
            }

            $after = $entity->balance();
            $this->accounts->save($entity);

            $credited = $this->deltaMoney($after, $before);

            $this->movements->append(
                $accountId,
                $userId,
                AccountMovementType::Deposit,
                $credited,
                $after,
                ['stated_amount' => $stated->toDecimal()],
            );
        });
    }

    public function applyMonthlyAdjustment(BankAccountId $accountId, UserId $userId): void
    {
        DB::transaction(function () use ($accountId, $userId): void {
            $entity = $this->accounts->findByIdForUpdate($accountId, $userId);
            if ($entity === null) {
                abort(404);
            }

            $before = $entity->balance();
            $entity->applyMonthlyAdjustment(new MonthlyAdjustmentPolicy);
            $after = $entity->balance();
            $this->accounts->save($entity);

            $delta = $this->deltaMoney($after, $before);

            $this->movements->append(
                $accountId,
                $userId,
                AccountMovementType::MonthlyAdjustment,
                $delta,
                $after,
                null,
            );
        });
    }

    private function deltaMoney(Money $after, Money $before): Money
    {
        $raw = bcsub($after->toDecimal(), $before->toDecimal(), 2);
        if (bccomp($raw, '0', 2) < 0) {
            return Money::zero();
        }

        return Money::fromDecimal($raw);
    }
}
