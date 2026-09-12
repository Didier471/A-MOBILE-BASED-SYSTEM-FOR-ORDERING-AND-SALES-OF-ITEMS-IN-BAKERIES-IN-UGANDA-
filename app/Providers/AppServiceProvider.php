<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Sale;
use App\Observers\PaymentObserver;
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
    }
}