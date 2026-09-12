<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard)
    {
    }

    /**
     * GET /api/dashboard
     * GET /api/dashboard?from=2026-08-01&to=2026-08-31
     *
     * Role is resolved from the authenticated user; from/to are optional
     * and default to "today" so existing callers keep working unchanged.
     */
    public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return response()->json(
            $this->dashboard->build(
                $request->user(),
                $request->input('from'),
                $request->input('to'),
            )
        );
    }
}