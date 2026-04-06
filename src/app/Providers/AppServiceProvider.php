<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
