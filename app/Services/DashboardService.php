<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * How long a dashboard snapshot stays cached before being recomputed.
     * Kept short because sale/payment observers also bust it on write,
     * this is just a safety net for anything that slips through.
     */
    private const TTL_SECONDS = 300;

    /**
     * Build the dashboard payload for a given user + optional date range.
     * $from/$to are 'Y-m-d' strings or null (null = default to "today" view).
     */
    public function build(User $user, ?string $from, ?string $to): array
    {
        $role = $user->getRoleNames()->first() ?? 'User';

        [$start, $end] = $this->resolveRange($from, $to);

        $cacheKey = sprintf(
            'dashboard:%s:%s:%s:%s',
            $role,
            $start->toDateString(),
            $end->toDateString(),
            // Only scope the cache key to the user for roles whose view
            // includes "my performance" data; everyone else shares one
            // cached snapshot for the role.
            in_array($role, ['Sales Staff', 'Delivery Staff', 'Procurement Staff']) ? $user->id : 'shared'
        );

        return Cache::remember($cacheKey, self::TTL_SECONDS, function () use ($role, $user, $start, $end) {
            $payload = match ($role) {
                'Admin' => $this->adminDashboard($start, $end),
                'Manager' => $this->managerDashboard($start, $end),
                'Sales Staff' => $this->salesDashboard($start, $end, $user),
                'Inventory Staff' => $this->inventoryDashboard(),
                'Procurement Staff' => $this->procurementDashboard($start, $end),
                'Delivery Staff' => $this->deliveryDashboard($start, $end, $user),
                default => [
                    'role' => $role,
                    'dashboard' => 'basic',
                    'message' => 'Dashboard available for your account.',
                ],
            };

            // IMPORTANT: convert to plain arrays before this gets cached.
            // CACHE_STORE=database (see .env) serializes cache values with
            // PHP's serialize(), and serializing/unserializing hydrated
            // Eloquent Collections that way is unreliable across requests —
            // it can come back as __PHP_Incomplete_Class_Name on a cache
            // hit. Plain arrays serialize/unserialize cleanly every time.
            return $this->toCacheableArray($payload);
        });
    }

    /**
     * Recursively converts Eloquent/Support Collections (and anything else
     * Arrayable) into plain arrays, so only primitives/arrays ever get
     * handed to the cache store. See the comment in build() for why.
     */
    private function toCacheableArray(mixed $value): mixed
    {
        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->toCacheableArray($item), $value);
        }

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Role dashboards
    |--------------------------------------------------------------------------
    */

    private function adminDashboard(Carbon $start, Carbon $end): array
    {
        return [
            'role' => 'Admin',
            'dashboard' => 'admin',
            'period' => $this->periodMeta($start, $end),

            'summary' => [
                'total_products' => Product::count(),
                'total_customers' => Customer::count(),
                'total_suppliers' => Supplier::count(),
                'total_orders' => Order::count(),
                'total_sales' => Sale::count(),
                'total_purchases' => Purchase::count(),
                'total_deliveries' => Delivery::count(),
            ],

            'sales' => $this->salesForRange($start, $end),
            'profit' => $this->profitForRange($start, $end),
            'payments' => $this->paymentsForRange($start, $end),
            'orders' => $this->orderStatusCounts(),
            'deliveries' => $this->deliveryStatusCounts(),
            'inventory' => $this->inventoryCounts(),

            'sales_by_payment_method' => $this->salesByPaymentMethod($start, $end),
            'sales_trend' => $this->salesTrend($start, $end),
            'sales_by_category' => $this->salesByCategory($start, $end),
            'top_products' => $this->topProducts($start, $end),
            'top_customers' => $this->topCustomers($start, $end),
            'staff_performance' => $this->staffPerformance($start, $end),
            'delivery_performance' => $this->deliveryPerformance($start, $end),
            'alerts' => $this->alertsFeed(),

            'low_stock_items' => $this->lowStockItems(),

            'recent_orders' => Order::with(['customer', 'items.product'])->latest()->take(5)->get(),
            'recent_sales' => Sale::with(['customer', 'items.product'])->latest()->take(5)->get(),
            'recent_payments' => Payment::with(['sale', 'user'])->latest()->take(5)->get(),
        ];
    }

    private function managerDashboard(Carbon $start, Carbon $end): array
    {
        return [
            'role' => 'Manager',
            'dashboard' => 'manager',
            'period' => $this->periodMeta($start, $end),

            'summary' => [
                'total_orders' => Order::count(),
                'total_sales' => Sale::count(),
                'total_purchases' => Purchase::count(),
                'total_customers' => Customer::count(),
                'total_products' => Product::count(),
                'total_deliveries' => Delivery::count(),
            ],

            'sales' => $this->salesForRange($start, $end),
            'orders' => $this->orderStatusCounts(),
            'inventory' => $this->inventoryCounts(),
            'payments' => $this->paymentsForRange($start, $end),
            'deliveries' => $this->deliveryStatusCounts(),

            'top_products' => $this->topProducts($start, $end),
            'staff_performance' => $this->staffPerformance($start, $end),
            'sales_trend' => $this->salesTrend($start, $end),
            'sales_by_category' => $this->salesByCategory($start, $end),
            'delivery_performance' => $this->deliveryPerformance($start, $end),
            'alerts' => $this->alertsFeed(),

            'recent_orders' => Order::with('customer')->latest()->take(5)->get(),
            'low_stock_items' => $this->lowStockItems(),
        ];
    }

    private function salesDashboard(Carbon $start, Carbon $end, User $user): array
    {
        return [
            'role' => 'Sales Staff',
            'dashboard' => 'sales',

            'summary' => [
                'total_orders' => Order::count(),
                'total_sales' => Sale::count(),
                'total_customers' => Customer::count(),
            ],

            'today' => [
                'orders' => Order::whereDate('created_at', today())->count(),
                'sales' => Sale::whereDate('created_at', today())->count(),
                'sales_amount' => Sale::whereDate('created_at', today())->sum('grand_total'),
                'payments' => Payment::where('status', 'completed')
                    ->whereDate('created_at', today())
                    ->sum('amount'),
            ],

            // Scoped to the logged-in staff member, not the whole shop —
            // the old version showed every sale to every "Sales Staff" account.
            'my_performance' => [
                'period' => $this->periodMeta($start, $end),
                'my_sales_count' => Sale::where('created_by', $user->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
                'my_sales_amount' => Sale::where('created_by', $user->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('grand_total'),
            ],

            'orders' => $this->orderStatusCounts(),

            'recent_orders' => Order::with('customer')->latest()->take(8)->get(),
            'recent_sales' => Sale::with('customer')->latest()->take(8)->get(),
        ];
    }

    private function inventoryDashboard(): array
    {
        return [
            'role' => 'Inventory Staff',
            'dashboard' => 'inventory',

            'summary' => [
                'total_products' => Product::count(),
                'low_stock' => Product::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
                'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
                'active_products' => Product::where('status', true)->count(),
            ],

            'stock' => [
                'low_stock_items' => $this->lowStockItems(15),
                'out_of_stock_items' => Product::where('stock_quantity', '<=', 0)
                    ->orderBy('name')
                    ->get(),
            ],
        ];
    }

    private function procurementDashboard(Carbon $start, Carbon $end): array
    {
        return [
            'role' => 'Procurement Staff',
            'dashboard' => 'procurement',

            'summary' => [
                'total_suppliers' => Supplier::count(),
                'total_purchases' => Purchase::count(),
                'total_products' => Product::count(),
            ],

            'purchases' => [
                // NOTE: fixed from the original code, which called
                // ->sum('grand_total') here — Purchase only has
                // total_amount (see purchases migration), so that call
                // would have thrown a "column not found" error.
                'total_amount' => Purchase::whereBetween('created_at', [$start, $end])->sum('total_amount'),
                'today_amount' => Purchase::whereDate('created_at', today())->sum('total_amount'),
                'today_count' => Purchase::whereDate('created_at', today())->count(),
            ],

            'stock_needs' => $this->inventoryCounts(),
            'low_stock_items' => $this->lowStockItems(15),
            'recent_purchases' => Purchase::latest()->take(8)->get(),
        ];
    }

    private function deliveryDashboard(Carbon $start, Carbon $end, User $user): array
    {
        return [
            'role' => 'Delivery Staff',
            'dashboard' => 'delivery',

            'summary' => [
                'total_deliveries' => Delivery::count(),
                'pending' => Delivery::where('status', 'pending')->count(),
                'assigned' => Delivery::where('status', 'assigned')->count(),
                'delivered' => Delivery::where('status', 'delivered')->count(),
            ],

            // What this specific rider actually has on their plate today.
            'my_assignments' => Delivery::where('assigned_to', $user->id)
                ->whereIn('status', ['assigned', 'out_for_delivery'])
                ->latest()
                ->get(),

            // This rider's own on-time / average-duration numbers, not the
            // whole team's — scoped by assigned_to.
            'my_performance' => array_merge(
                ['period' => $this->periodMeta($start, $end)],
                $this->deliveryPerformance($start, $end, $user->id)
            ),

            'deliveries' => [
                'pending' => Delivery::where('status', 'pending')->latest()->take(10)->get(),
                'assigned' => Delivery::where('status', 'assigned')->latest()->take(10)->get(),
                'completed' => Delivery::where('status', 'delivered')->latest()->take(10)->get(),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Shared building blocks
    |--------------------------------------------------------------------------
    */

    private function periodMeta(Carbon $start, Carbon $end): array
    {
        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
        ];
    }

    /**
     * Resolve the requested range, defaulting to "today" when nothing
     * is supplied so the response shape stays close to the old behaviour.
     */
    private function resolveRange(?string $from, ?string $to): array
    {
        $start = $from ? Carbon::parse($from)->startOfDay() : today()->startOfDay();
        $end = $to ? Carbon::parse($to)->endOfDay() : today()->endOfDay();

        return [$start, $end];
    }

    /**
     * The equivalent-length period immediately before $start/$end,
     * used to compute trend deltas (e.g. "this week vs last week").
     */
    private function previousRange(Carbon $start, Carbon $end): array
    {
        $days = $start->diffInDays($end) + 1;

        return [
            $start->copy()->subDays($days),
            $start->copy()->subDay()->endOfDay(),
        ];
    }

    private function salesForRange(Carbon $start, Carbon $end): array
    {
        $currentAmount = Sale::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $currentCount = Sale::whereBetween('created_at', [$start, $end])->count();

        [$prevStart, $prevEnd] = $this->previousRange($start, $end);
        $previousAmount = Sale::whereBetween('created_at', [$prevStart, $prevEnd])->sum('grand_total');

        return [
            'total_amount' => Sale::sum('grand_total'),
            'period_amount' => $currentAmount,
            'period_count' => $currentCount,
            'today_amount' => Sale::whereDate('created_at', today())->sum('grand_total'),
            'today_count' => Sale::whereDate('created_at', today())->count(),
            'trend_vs_previous_period_pct' => $this->percentChange($previousAmount, $currentAmount),
        ];
    }

    private function paymentsForRange(Carbon $start, Carbon $end): array
    {
        return [
            'total_received' => Payment::where('status', 'completed')->sum('amount'),
            'period_received' => Payment::where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount'),
            'today_received' => Payment::where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('amount'),
        ];
    }

    /**
     * Gross profit for the range using the product's CURRENT cost_price.
     * NOTE: sale_items doesn't snapshot cost_price at the time of sale,
     * so this is an approximation that drifts if cost prices change over
     * time. For accurate historical margin, add a cost_price column to
     * sale_items (captured at checkout) and sum that instead.
     */
    private function profitForRange(Carbon $start, Carbon $end): array
    {
        $row = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->selectRaw('SUM(sale_items.subtotal) as revenue')
            ->selectRaw('SUM(sale_items.quantity * products.cost_price) as cost')
            ->first();

        $revenue = (float) ($row->revenue ?? 0);
        $cost = (float) ($row->cost ?? 0);

        return [
            'revenue' => round($revenue, 2),
            'estimated_cost' => round($cost, 2),
            'estimated_profit' => round($revenue - $cost, 2),
            'margin_pct' => $revenue > 0 ? round((($revenue - $cost) / $revenue) * 100, 1) : 0,
            'note' => 'Estimated using current product cost_price, not the price at time of sale.',
        ];
    }

    private function topProducts(Carbon $start, Carbon $end, int $limit = 5)
    {
        return SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('products.id', 'products.name')
            ->selectRaw('SUM(sale_items.quantity) as units_sold')
            ->selectRaw('SUM(sale_items.subtotal) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->take($limit)
            ->get();
    }

    private function topCustomers(Carbon $start, Carbon $end, int $limit = 5)
    {
        return Sale::join('customers', 'customers.id', '=', 'sales.customer_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('customers.id', 'customers.name')
            ->selectRaw('COUNT(sales.id) as orders_count')
            ->selectRaw('SUM(sales.grand_total) as total_spent')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_spent')
            ->take($limit)
            ->get();
    }

    private function staffPerformance(Carbon $start, Carbon $end, int $limit = 5)
    {
        return Sale::join('users', 'users.id', '=', 'sales.created_by')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('users.id', 'users.name')
            ->selectRaw('COUNT(sales.id) as sales_count')
            ->selectRaw('SUM(sales.grand_total) as sales_amount')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('sales_amount')
            ->take($limit)
            ->get();
    }

    private function salesByPaymentMethod(Carbon $start, Carbon $end)
    {
        return Payment::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();
    }

    private function orderStatusCounts(): array
    {
        return [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
        ];
    }

    private function deliveryStatusCounts(): array
    {
        return [
            'pending' => Delivery::where('status', 'pending')->count(),
            'assigned' => Delivery::where('status', 'assigned')->count(),
            'delivered' => Delivery::where('status', 'delivered')->count(),
        ];
    }

    private function inventoryCounts(): array
    {
        return [
            'low_stock' => Product::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
        ];
    }

    private function lowStockItems(int $limit = 10)
    {
        return Product::whereColumn('stock_quantity', '<=', 'reorder_level')
            ->orderBy('stock_quantity')
            ->take($limit)
            ->get();
    }

    /**
     * Daily sales for the range — {date, count, total} per day, in order.
     * Shape is chart-ready: feed date -> total straight into a line chart.
     */
    private function salesTrend(Carbon $start, Carbon $end)
    {
        return Sale::whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(grand_total) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Revenue and units sold per category within the range. Products with
     * no category (category_id null) are grouped under 'Uncategorized'
     * rather than silently dropped.
     */
    private function salesByCategory(Carbon $start, Carbon $end, int $limit = 10)
    {
        return SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as category")
            ->selectRaw('SUM(sale_items.quantity) as units_sold')
            ->selectRaw('SUM(sale_items.subtotal) as revenue')
            ->groupBy('category')
            ->orderByDesc('revenue')
            ->take($limit)
            ->get();
    }

    /**
     * Average delivery duration + on-time rate for deliveries completed
     * within the range. Pass $assignedTo to scope it to one rider.
     *
     * Computed in PHP (via Carbon) rather than raw SQL date-diff functions
     * on purpose — TIMESTAMPDIFF is MySQL-specific and this project's
     * .env.example defaults to sqlite for local dev, so a portable
     * approach avoids "works in prod, breaks locally."
     */
    private function deliveryPerformance(Carbon $start, Carbon $end, ?int $assignedTo = null): array
    {
        $query = Delivery::whereBetween('delivered_at', [$start, $end])
            ->whereNotNull('delivered_at');

        if ($assignedTo) {
            $query->where('assigned_to', $assignedTo);
        }

        $delivered = $query->get(['created_at', 'scheduled_at', 'delivered_at']);

        if ($delivered->isEmpty()) {
            return [
                'delivered_count' => 0,
                'avg_delivery_hours' => null,
                'on_time_rate_pct' => null,
            ];
        }

        $totalHours = $delivered->sum(
            fn ($delivery) => $delivery->created_at->diffInMinutes($delivery->delivered_at) / 60
        );

        $withSchedule = $delivered->whereNotNull('scheduled_at');
        $onTime = $withSchedule->filter(
            fn ($delivery) => $delivery->delivered_at->lte($delivery->scheduled_at)
        );

        return [
            'delivered_count' => $delivered->count(),
            'avg_delivery_hours' => round($totalHours / $delivered->count(), 1),
            'on_time_rate_pct' => $withSchedule->count() > 0
                ? round(($onTime->count() / $withSchedule->count()) * 100, 1)
                : null, // null, not 0 — means "no scheduled deliveries to judge against," not "always late"
            'scheduled_deliveries_considered' => $withSchedule->count(),
        ];
    }

    /**
     * One combined feed of things that need attention right now: low
     * stock, deliveries running late, and sales that haven't been fully
     * paid off. Not date-range scoped — these are always "as of now."
     *
     * NOTE on scale: each category is capped at a handful of items for
     * display, and 'unpaid_sales' count reflects up to 50 fetched rows,
     * not a true unlimited count. Fine for a single bakery's live
     * operational view; if this needs to be exhaustive/paginated later,
     * split it into its own /api/alerts endpoint instead of embedding
     * in the dashboard payload.
     */
    private function alertsFeed(int $displayLimit = 20): array
    {
        $alerts = collect();

        foreach ($this->lowStockItems(5) as $product) {
            $alerts->push([
                'type' => 'low_stock',
                'severity' => $product->stock_quantity <= 0 ? 'critical' : 'warning',
                'message' => "{$product->name} — {$product->stock_quantity} left (reorder at {$product->reorder_level})",
                'reference_id' => $product->id,
            ]);
        }

        $overdueDeliveriesQuery = Delivery::whereNotIn('status', ['delivered', 'cancelled'])
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', now());

        $overdueCount = (clone $overdueDeliveriesQuery)->count();

        foreach ($overdueDeliveriesQuery->orderBy('scheduled_at')->take(5)->get() as $delivery) {
            $alerts->push([
                'type' => 'overdue_delivery',
                'severity' => 'warning',
                'message' => "Delivery to {$delivery->recipient_name} was due "
                    . $delivery->scheduled_at->diffForHumans() . ' and is still ' . $delivery->status . '.',
                'reference_id' => $delivery->id,
            ]);
        }

        $unpaidSales = Sale::select('sales.id', 'sales.sale_number')
            ->selectRaw("sales.grand_total - COALESCE(SUM(CASE WHEN payments.status = 'completed' THEN payments.amount ELSE 0 END), 0) as balance")
            ->leftJoin('payments', 'payments.sale_id', '=', 'sales.id')
            ->groupBy('sales.id', 'sales.sale_number', 'sales.grand_total')
            ->havingRaw("sales.grand_total - COALESCE(SUM(CASE WHEN payments.status = 'completed' THEN payments.amount ELSE 0 END), 0) > 0")
            ->orderByDesc('sales.created_at')
            ->take(50)
            ->get();

        foreach ($unpaidSales->take(5) as $sale) {
            $alerts->push([
                'type' => 'unpaid_sale',
                'severity' => 'info',
                'message' => "Sale {$sale->sale_number} has " . number_format((float) $sale->balance, 2) . ' outstanding.',
                'reference_id' => $sale->id,
            ]);
        }

        return [
            'counts' => [
                'low_stock' => Product::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
                'overdue_deliveries' => $overdueCount,
                'unpaid_sales' => $unpaidSales->count(), // capped at 50, see note above
            ],
            'items' => $alerts->take($displayLimit)->values(),
        ];
    }

    private function percentChange(float $previous, float $current): ?float
    {
        if ($previous == 0.0) {
            return null; // no meaningful percentage when there's nothing to compare against
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}