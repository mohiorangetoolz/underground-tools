<?php

namespace App\Providers;

use App\Contracts\Repositories\AdminRepository;
use App\Contracts\Repositories\GatewayProviderRepository;
use App\Contracts\Repositories\ResetPasswordRepository;
use App\Contracts\Repositories\UserRepository;
use App\Repositories\AdminRepositoryEloquent;
use App\Repositories\GatewayProviderRepositoryEloquent;
use App\Repositories\ResetPasswordRepositoryEloquent;
use App\Repositories\UserRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepository::class, UserRepositoryEloquent::class);
        $this->app->bind(AdminRepository::class, AdminRepositoryEloquent::class);
        $this->app->bind(ResetPasswordRepository::class, ResetPasswordRepositoryEloquent::class);
        $this->app->bind(GatewayProviderRepository::class, GatewayProviderRepositoryEloquent::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
