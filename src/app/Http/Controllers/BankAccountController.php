<?php

namespace App\Http\Controllers;

use App\Domain\Banking\Repositories\BankAccountRepositoryInterface;
use App\Domain\Banking\ValueObjects\BankAccountId;
use App\Domain\Banking\ValueObjects\UserId;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
}
