@extends('layouts.app')

@section('content')

<header class="bg-white border-b px-8 py-5">
<h2 class="text-2xl font-bold text-gray-900">
                Orders
            </h2>
<p class="text-sm text-gray-500">
                Manage bakery orders and their status
            </p>
</header><section class="p-8">
<!-- Status cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">Total Orders</p>
<p class="text-3xl font-bold mt-2" id="ordersTotal">
                        0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">Pending</p>
<p class="text-3xl font-bold text-yellow-600 mt-2" id="ordersPending">
                        0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">Processing</p>
<p class="text-3xl font-bold text-blue-600 mt-2" id="ordersProcessing">
                        0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">Completed</p>
<p class="text-3xl font-bold text-green-600 mt-2" id="ordersCompleted">
                        0
                    </p>
</div>
</div>
<!-- Search -->
<div class="bg-white rounded-xl border p-5 mb-6">
<input class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-3" id="orderSearch" placeholder="Search order number..." type="text"/>
</div>
<!-- Orders table -->
<div class="bg-white rounded-xl border overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full">
<thead class="bg-gray-50 border-b">
<tr>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Order #
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Customer
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Items
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Total
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Status
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Actions
                                </th>
</tr>
</thead>
<tbody id="ordersTable">
<tr>
<td class="text-center px-6 py-10 text-gray-500" colspan="6">
                                    Loading orders...
                                </td>
</tr>
</tbody>
</table>
</div>
</div>
</section>

@endsection
