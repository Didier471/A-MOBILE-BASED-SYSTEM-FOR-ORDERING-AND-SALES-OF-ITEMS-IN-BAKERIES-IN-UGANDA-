<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Hot Loaf - Bakery Management' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200">

        <!-- Logo -->
        <div class="p-6 border-b border-gray-200">

            <h1 class="text-2xl font-bold text-gray-900">
                Hot Loaf
            </h1>

            <p class="text-sm text-gray-500">
                Bakery Management
            </p>

        </div>

        <!-- Navigation -->
        <nav class="p-4 space-y-2">

            <a href="/dashboard"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('dashboard') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Dashboard
            </a>

            <a href="/products"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('products') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Products
            </a>

            <a href="/inventory"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('inventory') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Inventory
            </a>

            <a href="/orders"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('orders') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Orders
            </a>

            <a href="/sales"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('sales') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Sales
            </a>

            <a href="/payments"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('payments') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Payments
            </a>

            <a href="/customers"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('customers') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Customers
            </a>

            <a href="/suppliers"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('suppliers') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Suppliers
            </a>

            <a href="/purchases"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('purchases') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Purchases
            </a>

            <a href="/deliveries"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('deliveries') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Deliveries
            </a>

            <a href="/reports"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('reports') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Reports
            </a>

            <a href="/users"
               class="block px-4 py-3 rounded-lg
               {{ request()->is('users') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Users
            </a>

        </nav>

    </aside>

    <!-- Main Content -->
    <main class="flex-1">

        @yield('content')

    </main>

</div>

</body>
</html>