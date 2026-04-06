<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Users\Exceptions\InvalidCpfException;
use App\Domain\Users\ValueObjects\Cpf;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\LocalUserProfileProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request, LocalUserProfileProvisioner $provisioner): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'cpf' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ], [], [
            'name' => 'nome',
            'email' => 'e-mail',
            'cpf' => 'CPF',
            'password' => 'senha',
        ]);

        $cpfDigits = preg_replace('/\D/', '', $validated['cpf']) ?? '';

        try {
            $cpf = Cpf::fromString($cpfDigits);
        } catch (InvalidCpfException) {
            throw ValidationException::withMessages([
                'cpf' => 'O CPF informado é inválido.',
            ]);
        }

        if (User::query()->where('cpf', $cpf->toString())->exists()) {
            throw ValidationException::withMessages([
                'cpf' => 'Este CPF já está registado.',
            ]);
        }

        $user = DB::transaction(function () use ($validated, $cpf) {
            return User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'cpf' => $cpf->toString(),
                'password' => $validated['password'],
            ]);
        });

        Auth::login($user);

        $request->session()->regenerate();

        $provisioner->upsertAfterAuthenticatedLogin($user->fresh());

        return redirect()->intended(route('dashboard'));
    }
}
