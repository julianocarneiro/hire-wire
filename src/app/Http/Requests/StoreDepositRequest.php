<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepositRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Indique o valor do depósito.',
            'amount.numeric' => 'O valor deve ser numérico.',
            'amount.gt' => 'O valor do depósito deve ser superior a zero.',
            'amount.decimal' => 'Utilize no máximo duas casas decimais.',
        ];
    }
}
