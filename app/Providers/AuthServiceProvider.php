<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Gate::before(function($user, $ability){

        // });
        Gate::define('isSAdmin', function($user){
            return $user->hasRole('Super Admin');
        });
        Gate::define('isDirektur', function($user){
            return $user->hasRole('Direktur');
        });
        Gate::define('isAdmin', function($user){
            return $user->hasRole('Admin');
        });
        Gate::define('isKaryawan', function($user){
            return $user->hasRole('Karyawan');
        });

        Gate::define('create.unitkerja', function($user){
            return $user->hasPermission('create.unitkerja');
        });
    }
}
