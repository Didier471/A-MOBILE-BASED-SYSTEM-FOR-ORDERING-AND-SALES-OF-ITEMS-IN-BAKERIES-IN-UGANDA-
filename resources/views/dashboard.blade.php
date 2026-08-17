<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hot Loaf Bakery - Dashboard</title>

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
                   class="block px-4 py-3 rounded-lg bg-gray-900 text-white font-medium">
                    Dashboard
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Products
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Inventory
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Orders
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Sales
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Payments
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Customers
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Suppliers
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Purchases
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Deliveries
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Reports
                </a>

                <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    Users
                </a>

            </nav>

        </aside>


        <!-- Main Content -->
        <main class="flex-1">

            <!-- Header -->
            <header class="bg-white border-b border-gray-200 px-8 py-5">

                <div class="flex items-center justify-between">

                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            Dashboard
                        </h2>

                        <p class="text-sm text-gray-500">
                            Overview of your bakery operations
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="font-medium">
                            System Administrator
                        </p>

                        <p class="text-sm text-gray-500">
                            Administrator
                        </p>
                    </div>

                </div>

            </header>


            <!-- Dashboard Content -->
            <section class="p-8">

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                    <!-- Products -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                        <p class="text-sm text-gray-500">
                            Total Products
                        </p>

                        <h3 class="text-3xl font-bold mt-2">
                            1
                        </h3>

                        <p class="text-sm text-gray-500 mt-2">
                            Products in system
                        </p>

                    </div>


                    <!-- Customers -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                        <p class="text-sm text-gray-500">
                            Customers
                        </p>

                        <h3 class="text-3xl font-bold mt-2">
                            0
                        </h3>

                        <p class="text-sm text-gray-500 mt-2">
                            Registered customers
                        </p>

                    </div>


                    <!-- Orders -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                        <p class="text-sm text-gray-500">
                            Total Orders
                        </p>

                        <h3 class="text-3xl font-bold mt-2">
                            1
                        </h3>

                        <p class="text-sm text-gray-500 mt-2">
                            Orders processed
                        </p>

                    </div>


                    <!-- Sales -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                        <p class="text-sm text-gray-500">
                            Total Sales
                        </p>

                        <h3 class="text-3xl font-bold mt-2">
                            UGX 17,500
                        </h3>

                        <p class="text-sm text-gray-500 mt-2">
                            Total sales value
                        </p>

                    </div>

                </div>


                <!-- Second Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">

                    <!-- Orders -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                        <h3 class="text-lg font-bold">
                            Order Status
                        </h3>

                        <div class="mt-6 space-y-4">

                            <div class="flex justify-between">
                                <span>Pending</span>
                                <span class="font-bold">0</span>
                            </div>

                            <div class="flex justify-between">
                                <span>Processing</span>
                                <span class="font-bold">0</span>
                            </div>

                            <div class="flex justify-between">
                                <span>Completed</span>
                                <span class="font-bold">1</span>
                            </div>

                        </div>

                    </div>


                    <!-- Deliveries -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                        <h3 class="text-lg font-bold">
                            Delivery Status
                        </h3>

                        <div class="mt-6 space-y-4">

                            <div class="flex justify-between">
                                <span>Pending</span>
                                <span class="font-bold">0</span>
                            </div>

                            <div class="flex justify-between">
                                <span>Assigned</span>
                                <span class="font-bold">0</span>
                            </div>

                            <div class="flex justify-between">
                                <span>Delivered</span>
                                <span class="font-bold">1</span>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- Financial Overview -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-8">

                    <h3 class="text-lg font-bold">
                        Financial Overview
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">

                        <div>
                            <p class="text-sm text-gray-500">
                                Sales
                            </p>

                            <p class="text-2xl font-bold mt-1">
                                UGX 17,500
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Payments Received
                            </p>

                            <p class="text-2xl font-bold mt-1">
                                UGX 17,500
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Purchases
                            </p>

                            <p class="text-2xl font-bold mt-1">
                                UGX 50,000
                            </p>
                        </div>

                    </div>

                </div>

            </section>

        </main>

    </div>

</body>
</html>