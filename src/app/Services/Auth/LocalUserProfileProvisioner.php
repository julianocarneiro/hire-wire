<?php

namespace App\Services\Auth;

use App\Models\User;

/**
 * Idempotent local profile sync after successful web authentication.
 *
 * The canonical deduplication key for this phase is {@see User::$email}
 * (unique in the `users` table). Upserts must target this column so the same
 * identity never produces a second row.
 */
final class LocalUserProfileProvisioner
{
    public function upsertAfterAuthenticatedLogin(User $user): void
    {
        User::query()->updateOrCreate(
            ['email' => $user->email],
            [
                'name' => $user->name,
                'cpf' => $user->cpf,
            ]
        );
    }
}
