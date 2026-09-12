<?php

namespace App\Observers;

use App\Models\Payment;
use App\Support\DashboardCache;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        DashboardCache::forgetToday();
    }

    public function updated(Payment $payment): void
    {
        DashboardCache::forgetToday();
    }
}