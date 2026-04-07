<?php

namespace App\Http\Controllers;

use App\Domain\Banking\Factories\BankAccountEntityFactory;
use App\Domain\Banking\Repositories\BankAccountRepositoryInterface;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\Money;
use App\Domain\Banking\ValueObjects\UserId;
use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD for the authenticated user's bank accounts (show, create, update, delete).
 */
class BankAccountController extends Controller
{
    public function show(Request $request, int $id, BankAccountRepositoryInterface $accounts): Response
    {
        $entity = $accounts->findByIdForUser(
            new BankAccountId($id),
            new UserId((int) $request->user()->id),
        );

        if ($entity === null) {
            abort(404);
        }

        return Inertia::render('Dashboard/BankAccountShow', [
            'account' => [
                'id' => $entity->id()->value,
                'type' => $entity->type()->value,
                'balance' => $entity->balance()->toDecimal(),
            ],
        ]);
    }

    public function store(StoreBankAccountRequest $request, BankAccountRepositoryInterface $accounts): RedirectResponse
    {
        $validated = $request->validated();
        $balanceRaw = $validated['balance'] ?? '0';
        $userId = new UserId((int) $request->user()->id);
        $type = AccountType::from($validated['type']);

        $account = BankAccountEntityFactory::create(
            null,
            $userId,
            $type,
            Money::fromDecimal(is_numeric($balanceRaw) ? (string) $balanceRaw : '0'),
        );

        $accounts->save($account);

        return back();
    }

    public function update(
        UpdateBankAccountRequest $request,
        int $id,
        BankAccountRepositoryInterface $accounts,
    ): RedirectResponse {
        $entity = $accounts->findByIdForUser(
            new BankAccountId($id),
            new UserId((int) $request->user()->id),
        );

        if ($entity === null) {
            abort(404);
        }

        $entity->replaceBalance(Money::fromDecimal((string) $request->validated('balance')));
        $accounts->save($entity);

        return back();
    }

    public function destroy(Request $request, int $id, BankAccountRepositoryInterface $accounts): RedirectResponse
    {
        $deleted = $accounts->delete(
            new BankAccountId($id),
            new UserId((int) $request->user()->id),
        );

        if (! $deleted) {
            abort(404);
        }

        return redirect()->route('dashboard');
    }
}
