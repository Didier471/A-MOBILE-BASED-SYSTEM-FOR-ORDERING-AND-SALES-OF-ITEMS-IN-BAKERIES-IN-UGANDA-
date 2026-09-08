@extends('layouts.app')

@section('content')

<!-- Header -->
<header class="bg-white border-b border-gray-200 px-8 py-5">

    <h2 class="text-2xl font-bold text-gray-900">
        Inventory
    </h2>

    <p class="text-sm text-gray-500">
        Monitor and manage bakery stock
    </p>

</header>


<!-- Inventory Content -->
<section class="p-8">

    <!-- Inventory Summary -->

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="bg-white rounded-xl border p-6">
            <p class="text-sm text-gray-500">
                Total Items
            </p>

            <p id="inventoryTotal"
               class="text-3xl font-bold mt-2">
                0
            </p>
        </div>


        <div class="bg-white rounded-xl border p-6">
            <p class="text-sm text-gray-500">
                Low Stock
            </p>

            <p id="inventoryLowStock"
               class="text-3xl font-bold text-yellow-600 mt-2">
                0
            </p>
        </div>


        <div class="bg-white rounded-xl border p-6">
            <p class="text-sm text-gray-500">
                Out of Stock
            </p>

            <p id="inventoryOutOfStock"
               class="text-3xl font-bold text-red-600 mt-2">
                0
            </p>
        </div>

    </div>


    <!-- Search -->

    <div class="bg-white rounded-xl border p-5 mb-6">

        <input
            id="inventorySearch"
            type="text"
            placeholder="Search inventory..."
            class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-3"
        >

    </div>


    <!-- Table -->

    <div class="bg-white rounded-xl border overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-gray-50 border-b">

                    <tr>

                        <th class="text-left px-6 py-4 text-sm font-semibold">
                            Product
                        </th>

                        <th class="text-left px-6 py-4 text-sm font-semibold">
                            Quantity
                        </th>

                        <th class="text-left px-6 py-4 text-sm font-semibold">
                            Reorder Level
                        </th>

                        <th class="text-left px-6 py-4 text-sm font-semibold">
                            Status
                        </th>

                    </tr>

                </thead>

                <tbody id="inventoryTable">

                    <tr>
                        <td colspan="4"
                            class="text-center px-6 py-10 text-gray-500">
                            Loading inventory...
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</section>

@endsection