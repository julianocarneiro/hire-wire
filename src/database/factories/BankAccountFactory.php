<?php

namespace Database\Factories;

use App\Domain\Banking\ValueObjects\AccountType;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => AccountType::Savings->value,
            'balance' => '0.00',
        ];
    }
}
