<?php

namespace App\Providers;

use App\Domain\Banking\Repositories\BankAccountRepositoryInterface;
use App\Infrastructure\Banking\EloquentBankAccountRepository;
use Carbon\CarbonInterval;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BankAccountRepositoryInterface::class, EloquentBankAccountRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Authenticate::redirectUsing(fn () => route('login'));

        RedirectIfAuthenticated::redirectUsing(fn () => route('dashboard'));

        if (! $this->app->isProduction()) {
            Passport::$validateKeyPermissions = false;
        }

        Passport::$deviceCodeGrantEnabled = false;

        Passport::tokensExpireIn(CarbonInterval::days(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));

        Passport::tokensCan([
            'read:accounts' => 'View account balances and related data',
            'write:accounts' => 'Create or update account data (e.g. deposits)',
            'read:profile' => 'View your basic profile information',
        ]);

        Passport::authorizationView('auth.oauth.authorize');
    }
}
