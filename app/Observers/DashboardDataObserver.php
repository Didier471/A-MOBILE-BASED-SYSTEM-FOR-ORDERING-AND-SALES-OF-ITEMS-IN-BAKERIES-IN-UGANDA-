<?php

namespace App\Observers;

use App\Support\DashboardCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Keeps dashboard snapshots fresh after operational records change.
 */
class DashboardDataObserver
{
    public function created(Model $model): void
    {
        DashboardCache::forgetToday();
    }

    public function updated(Model $model): void
    {
        DashboardCache::forgetToday();
    }

    public function deleted(Model $model): void
    {
        DashboardCache::forgetToday();
    }
}
