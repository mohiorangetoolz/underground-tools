<?php

namespace App\Providers;

use App\Contracts\Services\AdminContract;
use App\Contracts\Services\AdminDashboardContract;
use App\Contracts\Services\AdminSettingContract;
use App\Contracts\Services\UserContract;
use App\Services\AdminDashboardService;
use App\Services\AdminService;
use App\Services\AdminSettingService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserContract::class, UserService::class);
        $this->app->bind(AdminContract::class, AdminService::class);
        $this->app->bind(AdminDashboardContract::class, AdminDashboardService::class);
        $this->app->bind(AdminSettingContract::class, AdminSettingService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') != 'local') {
            $this->app['request']->server->set('HTTPS', true);
        }
    }
}
