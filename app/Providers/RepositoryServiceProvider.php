<?php

namespace App\Providers;

use App\Contracts\AuthRepositoryInterface;
use App\Contracts\AuthServiceInterface;
use App\Contracts\OrderRepositoryInterface;
use App\Contracts\OrderServiceInterface;
use App\Contracts\PaymentRepositoryInterface;
use App\Contracts\PaymentServiceInterface;
use App\Repositories\AuthRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Services\AuthService;
use App\Services\BusinessRulesService;
use App\Services\OrderService;
use App\Services\Payments\PaymentGatewayFactory;
use App\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);

        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);

        $this->app->singleton(BusinessRulesService::class);

        $this->app->singleton(PaymentGatewayFactory::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
