<?php

namespace App\Http\Middleware;

use App\Domain\Banking\Repositories\BankAccountRepositoryInterface;
use App\Domain\Banking\ValueObjects\AccountType;
use App\Domain\Banking\ValueObjects\UserId;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? $request->user()->only(['id', 'name', 'email'])
                    : null,
            ],
            'bankAccounts' => $this->sharedBankAccounts($request),
            'accountTypeOptions' => $this->sharedAccountTypeOptions($request),
        ];
    }

    /**
     * @return list<array{id: int, type: string, balance: string}>
     */
    private function sharedBankAccounts(Request $request): array
    {
        if (! $request->user()) {
            return [];
        }

        $accounts = app(BankAccountRepositoryInterface::class)
            ->listForUser(new UserId((int) $request->user()->id));

        return array_map(static function ($account): array {
            $id = $account->id();
            if ($id === null) {
                throw new \LogicException('Persisted bank account must carry an id.');
            }

            return [
                'id' => $id->value,
                'type' => $account->type()->value,
                'balance' => $account->balance()->toDecimal(),
            ];
        }, $accounts);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function sharedAccountTypeOptions(Request $request): array
    {
        if (! $request->user()) {
            return [];
        }

        return [
            ['value' => AccountType::Savings->value, 'label' => 'Poupança'],
            ['value' => AccountType::Checking->value, 'label' => 'Corrente'],
            ['value' => AccountType::Investments->value, 'label' => 'Investimentos'],
        ];
    }
}
