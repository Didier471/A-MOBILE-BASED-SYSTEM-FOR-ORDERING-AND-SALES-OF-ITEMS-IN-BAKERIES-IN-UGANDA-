<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hot Loaf - Orders</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200">

        <div class="p-6 border-b">
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
               class="block px-4 py-3 rounded-lg bg-gray-900 text-white font-medium">
                Orders
            </a>

            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">Sales</a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">Payments</a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">Customers</a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">Suppliers</a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">Purchases</a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">Deliveries</a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">Reports</a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">Users</a>

        </nav>

    </aside>


    <!-- Main -->
    <main class="flex-1">

        <header class="bg-white border-b px-8 py-5">

            <h2 class="text-2xl font-bold text-gray-900">
                Orders
            </h2>

            <p class="text-sm text-gray-500">
                Manage bakery orders and their status
            </p>

        </header>


        <section class="p-8">

            <!-- Status cards -->

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

                <div class="bg-white rounded-xl border p-6">
                    <p class="text-sm text-gray-500">Total Orders</p>

                    <p id="ordersTotal"
                       class="text-3xl font-bold mt-2">
                        0
                    </p>
                </div>

                <div class="bg-white rounded-xl border p-6">
                    <p class="text-sm text-gray-500">Pending</p>

                    <p id="ordersPending"
                       class="text-3xl font-bold text-yellow-600 mt-2">
                        0
                    </p>
                </div>

                <div class="bg-white rounded-xl border p-6">
                    <p class="text-sm text-gray-500">Processing</p>

                    <p id="ordersProcessing"
                       class="text-3xl font-bold text-blue-600 mt-2">
                        0
                    </p>
                </div>

                <div class="bg-white rounded-xl border p-6">
                    <p class="text-sm text-gray-500">Completed</p>

                    <p id="ordersCompleted"
                       class="text-3xl font-bold text-green-600 mt-2">
                        0
                    </p>
                </div>

            </div>


            <!-- Search -->

            <div class="bg-white rounded-xl border p-5 mb-6">

                <input
                    id="orderSearch"
                    type="text"
                    placeholder="Search order number..."
                    class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-3"
                >

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
                                <td colspan="6"
                                    class="text-center px-6 py-10 text-gray-500">
                                    Loading orders...
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