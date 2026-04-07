<?php

namespace Tests\Unit\Application\Banking;

use App\Application\Banking\BankAccountMovementService;
use App\Domain\Banking\Factories\BankAccountEntityFactory;
use App\Domain\Banking\Repositories\AccountMovementRepositoryInterface;
use App\Domain\Banking\Repositories\BankAccountRepositoryInterface;
use App\Domain\Banking\ValueObjects\AccountMovementType;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;
use Tests\TestCase;

class BankAccountMovementServiceTest extends TestCase
{
    public function test_record_deposit_saves_account_and_appends_movement(): void
    {
        $accountId = new BankAccountId(5);
        $userId = new UserId(9);
        $entity = BankAccountEntityFactory::create(
            $accountId,
            $userId,
            AccountType::Savings,
            Money::fromDecimal('100.00'),
        );

        $accounts = $this->createMock(BankAccountRepositoryInterface::class);
        $accounts->expects($this->once())
            ->method('findByIdForUpdate')
            ->with($accountId, $userId)
            ->willReturn($entity);
        $accounts->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($saved) {
                return $saved->balance()->toDecimal() === '150.00';
            }));

        $movements = $this->createMock(AccountMovementRepositoryInterface::class);
        $movements->expects($this->once())
            ->method('append')
            ->with(
                $accountId,
                $userId,
                AccountMovementType::Deposit,
                $this->callback(fn (Money $m) => $m->toDecimal() === '50.00'),
                $this->callback(fn (Money $m) => $m->toDecimal() === '150.00'),
                ['stated_amount' => '50.00'],
            );

        $service = new BankAccountMovementService($accounts, $movements);
        $service->recordDeposit($accountId, $userId, '50.00');
    }

    public function test_apply_monthly_adjustment_saves_account_and_appends_delta(): void
    {
        $accountId = new BankAccountId(2);
        $userId = new UserId(3);
        $entity = BankAccountEntityFactory::create(
            $accountId,
            $userId,
            AccountType::Savings,
            Money::fromDecimal('10000.00'),
        );

        $accounts = $this->createMock(BankAccountRepositoryInterface::class);
        $accounts->expects($this->once())
            ->method('findByIdForUpdate')
            ->with($accountId, $userId)
            ->willReturn($entity);
        $accounts->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($saved) {
                return $saved->balance()->toDecimal() === '10000.10';
            }));

        $movements = $this->createMock(AccountMovementRepositoryInterface::class);
        $movements->expects($this->once())
            ->method('append')
            ->with(
                $accountId,
                $userId,
                AccountMovementType::MonthlyAdjustment,
                $this->callback(fn (Money $m) => $m->toDecimal() === '0.10'),
                $this->callback(fn (Money $m) => $m->toDecimal() === '10000.10'),
                null,
            );

        $service = new BankAccountMovementService($accounts, $movements);
        $service->applyMonthlyAdjustment($accountId, $userId);
    }
}
