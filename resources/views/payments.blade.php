<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hot Loaf - Payments</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200">

        <div class="p-6 border-b border-gray-200">

            <h1 class="text-2xl font-bold text-gray-900">
                Hot Loaf
            </h1>

            <p class="text-sm text-gray-500">
                Bakery Management
            </p>

        </div>


        <nav class="p-4 space-y-2">

            <a href="/dashboard"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Dashboard
            </a>

            <a href="/products"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Products
            </a>

            <a href="/inventory"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Inventory
            </a>

            <a href="/orders"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Orders
            </a>

            <a href="/sales"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Sales
            </a>

            <a href="/payments"
               class="block px-4 py-3 rounded-lg bg-gray-900 text-white font-medium">
                Payments
            </a>

            <a href="#"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Customers
            </a>

            <a href="#"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Suppliers
            </a>

            <a href="#"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Purchases
            </a>

            <a href="#"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Deliveries
            </a>

            <a href="#"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Reports
            </a>

            <a href="#"
               class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                Users
            </a>

        </nav>

    </aside>


    <!-- Main Content -->
    <main class="flex-1">

        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-8 py-5">

            <h2 class="text-2xl font-bold text-gray-900">
                Payments
            </h2>

            <p class="text-sm text-gray-500">
                Track and manage customer payments
            </p>

        </header>


        <section class="p-8">


            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">


                <!-- Total Received -->
                <div class="bg-white rounded-xl border p-6">

                    <p class="text-sm text-gray-500">
                        Total Received
                    </p>

                    <p id="paymentsTotal"
                       class="text-3xl font-bold mt-2">
                        UGX 0
                    </p>

                </div>


                <!-- Number of Payments -->
                <div class="bg-white rounded-xl border p-6">

                    <p class="text-sm text-gray-500">
                        Number of Payments
                    </p>

                    <p id="paymentsCount"
                       class="text-3xl font-bold mt-2">
                        0
                    </p>

                </div>


                <!-- Completed Payments -->
                <div class="bg-white rounded-xl border p-6">

                    <p class="text-sm text-gray-500">
                        Completed Payments
                    </p>

                    <p id="paymentsCompleted"
                       class="text-3xl font-bold text-green-600 mt-2">
                        0
                    </p>

                </div>

            </div>


            <!-- Search -->
            <div class="bg-white rounded-xl border p-5 mb-6">

                <input
                    id="paymentsSearch"
                    type="text"
                    placeholder="Search payments..."
                    class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-3"
                >

            </div>


            <!-- Payments Table -->
            <div class="bg-white rounded-xl border overflow-hidden">

                <div class="px-6 py-5 border-b">

                    <h3 class="text-lg font-bold">
                        Payment Records
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
                                    Amount
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold">
                                    Method
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold">
                                    Status
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold">
                                    Date
                                </th>

                            </tr>

                        </thead>


                        <tbody id="paymentsTable">

                            <tr>

                                <td colspan="5"
                                    class="text-center px-6 py-10 text-gray-500">

                                    Loading payments...

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

    </main>

</div>

</body>
</html>