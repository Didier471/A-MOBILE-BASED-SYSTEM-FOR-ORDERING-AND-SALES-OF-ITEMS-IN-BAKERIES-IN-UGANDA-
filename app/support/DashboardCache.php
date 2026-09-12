<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Invalidates the dashboard cache keys built by DashboardService.
 *
 * Honest limitation: the 'database' cache driver this project uses
 * (see .env: CACHE_STORE=database) doesn't support tagged cache flushing,
 * so we can't wildcard-delete "every dashboard key." Instead we forget the
 * specific keys for the *default* ("today") view, per role, since that's
 * what nearly every dashboard load actually requests.
 *
 * Custom date-range dashboards (?from=...&to=...) are NOT force-invalidated
 * here — they simply expire via DashboardService::TTL_SECONDS (5 min).
 * That's a deliberate tradeoff, not an oversight: enumerating every
 * possible from/to combination isn't practical. If you later move to
 * redis/memcached, switch DashboardService to Cache::tags('dashboard')
 * and replace this class with a single Cache::tags('dashboard')->flush().
 */
class DashboardCache
{
    private const ROLES = [
        'Admin', 'Manager', 'Sales Staff',
        'Inventory Staff', 'Procurement Staff', 'Delivery Staff',
    ];

    public static function forgetToday(?int $affectedUserId = null): void
    {
        $today = today()->toDateString();

        foreach (self::ROLES as $role) {
            Cache::forget("dashboard:{$role}:{$today}:{$today}:shared");

            if (in_array($role, ['Sales Staff', 'Delivery Staff', 'Procurement Staff'])) {
                $userIds = $affectedUserId
                    ? [$affectedUserId]
                    : User::role($role)->pluck('id')->all();

                foreach ($userIds as $userId) {
                    Cache::forget("dashboard:{$role}:{$today}:{$today}:{$userId}");
                }
            }
        }
    }
}