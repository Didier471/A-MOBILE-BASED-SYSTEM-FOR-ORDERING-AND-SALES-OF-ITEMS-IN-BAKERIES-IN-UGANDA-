@extends('layouts.app')

@section('content')

<header class="bg-white border-b border-gray-200 px-8 py-5">
<h2 class="text-2xl font-bold text-gray-900">
                Suppliers
            </h2>
<p class="text-sm text-gray-500">
                Manage bakery suppliers
            </p>
</header><section class="p-8">
<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Total Suppliers
                    </p>
<p class="text-3xl font-bold mt-2" id="suppliersTotal">
                        0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Active Suppliers
                    </p>
<p class="text-3xl font-bold text-green-600 mt-2" id="suppliersActive">
                        0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Inactive Suppliers
                    </p>
<p class="text-3xl font-bold text-gray-500 mt-2" id="suppliersInactive">
                        0
                    </p>
</div>
</div>
<!-- Search -->
<div class="bg-white rounded-xl border p-5 mb-6">
<input class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-3" id="suppliersSearch" placeholder="Search suppliers..." type="text"/>
</div>
<!-- Table -->
<div class="bg-white rounded-xl border overflow-hidden">
<div class="px-6 py-5 border-b">
<h3 class="text-lg font-bold">
                        Supplier Records
                    </h3>
</div>
<div class="overflow-x-auto">
<table class="w-full">
<thead class="bg-gray-50 border-b">
<tr>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Name
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Phone
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Email
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Address
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Status
                                </th>
</tr>
</thead>
<tbody id="suppliersTable">
<tr>
<td class="text-center px-6 py-10 text-gray-500" colspan="5">

                                    Loading suppliers...

                                </td>
</tr>
</tbody>
</table>
</div>
</div>
</section>

@endsection
