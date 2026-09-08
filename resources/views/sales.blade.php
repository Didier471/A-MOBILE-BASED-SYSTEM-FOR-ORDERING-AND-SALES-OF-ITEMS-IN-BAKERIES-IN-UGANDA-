@extends('layouts.app')

@section('content')

<header class="bg-white border-b border-gray-200 px-8 py-5">
<h2 class="text-2xl font-bold text-gray-900">
                Sales
            </h2>
<p class="text-sm text-gray-500">
                View and manage bakery sales
            </p>
</header><section class="p-8">
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
<!-- Total Sales -->
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Total Sales
                    </p>
<p class="text-3xl font-bold mt-2" id="salesTotal">
                        UGX 0
                    </p>
</div>
<!-- Number of Sales -->
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Number of Sales
                    </p>
<p class="text-3xl font-bold mt-2" id="salesCount">
                        0
                    </p>
</div>
<!-- Average Sale -->
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Average Sale
                    </p>
<p class="text-3xl font-bold mt-2" id="salesAverage">
                        UGX 0
                    </p>
</div>
</div>
<!-- Search -->
<div class="bg-white rounded-xl border p-5 mb-6">
<input class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-3" id="salesSearch" placeholder="Search sales..." type="text"/>
</div>
<!-- Sales Table -->
<div class="bg-white rounded-xl border overflow-hidden">
<div class="px-6 py-5 border-b">
<h3 class="text-lg font-bold">
                        Sales Records
                    </h3>
</div>
<div class="overflow-x-auto">
<table class="w-full">
<thead class="bg-gray-50 border-b">
<tr>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Sale Number
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Customer
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Amount
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Date
                                </th>
</tr>
</thead>
<tbody id="salesTable">
<tr>
<td class="text-center px-6 py-10 text-gray-500" colspan="4">

                                    Loading sales...

                                </td>
</tr>
</tbody>
</table>
</div>
</div>
</section>

@endsection
