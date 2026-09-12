<?php

namespace App\Observers;

use App\Models\Sale;
use App\Support\DashboardCache;

class SaleObserver
{
    public function created(Sale $sale): void
    {
        DashboardCache::forgetToday($sale->created_by);
    }

    public function updated(Sale $sale): void
    {
        DashboardCache::forgetToday($sale->created_by);
    }

    public function deleted(Sale $sale): void
    {
        DashboardCache::forgetToday($sale->created_by);
    }
}