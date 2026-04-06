<?php

namespace App\Http\Requests;

use App\Domain\Banking\ValueObjects\AccountType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::enum(AccountType::class),
                Rule::unique('bank_accounts', 'type')->where('user_id', (int) $this->user()->id),
            ],
            'balance' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.unique' => 'Já existe uma conta deste tipo para o seu utilizador.',
        ];
    }
}
