<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $bank_account_id
 * @property string $type
 * @property string $amount
 * @property string|null $balance_after
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 */
class AccountMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'bank_account_id',
        'type',
        'amount',
        'balance_after',
        'metadata',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<BankAccount, $this>
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
