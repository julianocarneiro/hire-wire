<?php

namespace App\Http\Controllers;

use App\Application\Banking\BankAccountMovementService;
use App\Domain\Banking\Repositories\AccountMovementRepositoryInterface;
use App\Domain\Banking\Repositories\BankAccountRepositoryInterface;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\UserId;
use App\Http\Requests\ApplyMonthlyAdjustmentRequest;
use App\Http\Requests\StoreDepositRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP actions for account movements: listing, deposits, and monthly adjustment.
 */
class BankAccountMovementController extends Controller
{
    public function movements(
        Request $request,
        int $id,
        BankAccountRepositoryInterface $accounts,
        AccountMovementRepositoryInterface $movements,
    ): Response {
        $userId = new UserId((int) $request->user()->id);
        $accountId = new BankAccountId($id);

        $entity = $accounts->findByIdForUser($accountId, $userId);

        if ($entity === null) {
            abort(404);
        }

        return Inertia::render('Dashboard/BankAccountMovements', [
            'account' => [
                'id' => $entity->id()->value,
                'type' => $entity->type()->value,
                'balance' => $entity->balance()->toDecimal(),
            ],
            'movements' => $movements->listForAccount($accountId, $userId),
        ]);
    }

    public function deposit(
        StoreDepositRequest $request,
        int $id,
        BankAccountMovementService $service,
    ): RedirectResponse {
        $amount = $request->validated('amount');
        $service->recordDeposit(
            new BankAccountId($id),
            new UserId((int) $request->user()->id),
            is_float($amount) || is_int($amount)
                ? number_format((float) $amount, 2, '.', '')
                : (string) $amount,
        );

        return back();
    }

    public function applyMonthlyAdjustment(
        ApplyMonthlyAdjustmentRequest $request,
        int $id,
        BankAccountMovementService $service,
    ): RedirectResponse {
        $service->applyMonthlyAdjustment(
            new BankAccountId($id),
            new UserId((int) $request->user()->id),
        );

        return back();
    }
}
