<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Observers\PaymentObserver;
use App\Observers\DashboardDataObserver;
use App\Observers\SaleObserver;
use Illuminate\Support\ServiceProvider;

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
        Sale::observe(SaleObserver::class);
        Payment::observe(PaymentObserver::class);

        foreach ([Customer::class, Delivery::class, InventoryTransaction::class, Order::class, Product::class, Purchase::class, Supplier::class, User::class] as $model) {
            $model::observe(DashboardDataObserver::class);
        }
    }
}