@extends('layouts.app')

@section('content')

<header class="bg-white border-b border-gray-200 px-8 py-5">
<h2 class="text-2xl font-bold text-gray-900">
                Reports
            </h2>
<p class="text-sm text-gray-500">
                View bakery sales, purchases, orders and payment reports
            </p>
</header><section class="p-8">
<!-- Date Filters -->
<div class="bg-white rounded-xl border p-6 mb-8">
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div>
<label class="block text-sm font-medium mb-2">
                            From
                        </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="reportFrom" type="date"/>
</div>
<div>
<label class="block text-sm font-medium mb-2">
                            To
                        </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="reportTo" type="date"/>
</div>
<div class="flex items-end">
<button class="w-full bg-gray-900 text-white rounded-lg px-4 py-3 hover:bg-gray-800" id="generateReport">
                            Generate Report
                        </button>
</div>
</div>
</div>
<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Total Sales
                    </p>
<p class="text-3xl font-bold mt-2" id="reportSales">
                        UGX 0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Total Payments
                    </p>
<p class="text-3xl font-bold mt-2" id="reportPayments">
                        UGX 0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Total Purchases
                    </p>
<p class="text-3xl font-bold mt-2" id="reportPurchases">
                        UGX 0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Total Orders
                    </p>
<p class="text-3xl font-bold mt-2" id="reportOrders">
                        0
                    </p>
</div>
</div>

<!-- Performance Charts -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl border p-6 xl:col-span-2">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold">Monthly Sales Performance</h3>
                <p class="text-sm text-gray-500">Sales and purchases by month</p>
            </div>
        </div>
        <div class="h-80">
            <canvas id="monthlyPerformanceChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-lg font-bold">Payment Distribution</h3>
        <p class="text-sm text-gray-500 mb-5">Completed payments by method</p>
        <div class="h-64">
            <canvas id="paymentMethodsChart" class="w-full h-full"></canvas>
        </div>
        <div id="paymentLegend" class="mt-4 space-y-2"></div>
    </div>
</div>

<!-- Monthly Performance Descriptions -->
<div class="bg-white rounded-xl border overflow-hidden mb-8">
    <div class="px-6 py-5 border-b">
        <h3 class="text-lg font-bold">Monthly Performance Analysis</h3>
        <p class="text-sm text-gray-500 mt-1">A short interpretation of sales performance for each month in the selected period.</p>
    </div>
    <div id="monthlyPerformance" class="divide-y divide-gray-100">
        <div class="px-6 py-6 text-gray-500">Loading monthly performance...</div>
    </div>
</div>

<!-- Report Results -->
<div class="bg-white rounded-xl border overflow-hidden">
<div class="px-6 py-5 border-b">
<h3 class="text-lg font-bold">
                        Report Results
                    </h3>
</div>
<div class="overflow-x-auto">
<table class="w-full">
<thead class="bg-gray-50 border-b">
<tr>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Report
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Value
                                </th>
</tr>
</thead>
<tbody id="reportsTable">
<tr>
<td class="text-center px-6 py-10 text-gray-500" colspan="2">

                                    Loading reports...

                                </td>
</tr>
</tbody>
</table>
</div>
</div>
</section>

@endsection
