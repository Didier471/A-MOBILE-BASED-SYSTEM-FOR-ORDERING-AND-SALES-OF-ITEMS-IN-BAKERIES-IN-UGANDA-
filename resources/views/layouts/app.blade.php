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
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">

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
        <nav class="p-4 space-y-2 flex-1">

            <!-- Dashboard - Everyone -->
            <a href="/dashboard"
               data-roles="Admin,Manager,Sales Staff,Inventory Staff,Procurement Staff,Delivery Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('dashboard') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Dashboard
            </a>


            <!-- Products -->
            <a href="/products"
               data-roles="Admin,Manager,Inventory Staff,Procurement Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('products') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Products
            </a>


            <!-- Inventory -->
            <a href="/inventory"
               data-roles="Admin,Manager,Inventory Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('inventory') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Inventory
            </a>


            <!-- Orders -->
            <a href="/orders"
               data-roles="Admin,Manager,Sales Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('orders') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Orders
            </a>


            <!-- Sales -->
            <a href="/sales"
               data-roles="Admin,Manager,Sales Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('sales') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Sales
            </a>


            <!-- Payments -->
            <a href="/payments"
               data-roles="Admin,Manager,Sales Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('payments') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Payments
            </a>


            <!-- Customers -->
            <a href="/customers"
               data-roles="Admin,Manager,Sales Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('customers') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Customers
            </a>


            <!-- Suppliers -->
            <a href="/suppliers"
               data-roles="Admin,Manager,Procurement Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('suppliers') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Suppliers
            </a>


            <!-- Purchases -->
            <a href="/purchases"
               data-roles="Admin,Manager,Procurement Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('purchases') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Purchases
            </a>


            <!-- Deliveries -->
            <a href="/deliveries"
               data-roles="Admin,Manager,Delivery Staff"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('deliveries') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Deliveries
            </a>


            <!-- Reports -->
            <a href="/reports"
               data-roles="Admin,Manager"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('reports') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Reports
            </a>


            <!-- Users - Admin ONLY -->
            <a href="/users"
               data-roles="Admin"
               class="role-menu block px-4 py-3 rounded-lg
               {{ request()->is('users') ? 'bg-gray-900 text-white font-medium' : 'hover:bg-gray-100' }}">
                Users
            </a>

        </nav>


        <!-- Logout -->
        <div class="p-4 border-t border-gray-200">

            <button
                id="logoutButton"
                type="button"
                class="w-full px-4 py-3 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition">
                Logout
            </button>

        </div>

    </aside>


    <!-- Main Content -->
    <main class="flex-1">

        @yield('content')

    </main>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ROLE-BASED NAVIGATION
    |--------------------------------------------------------------------------
    */

    const userData = localStorage.getItem('user');

    if (!userData) {
        window.location.href = '/login';
        return;
    }

    let user;

    try {
        user = JSON.parse(userData);
    } catch (error) {

        console.error('Invalid user data:', error);

        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');

        window.location.href = '/login';

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get user's role
    |--------------------------------------------------------------------------
    */

    let userRole = '';

    if (Array.isArray(user.roles) && user.roles.length > 0) {

        userRole = user.roles[0];

    } else if (typeof user.role === 'string') {

        userRole = user.role;

    }


    console.log('Logged-in user:', user);
    console.log('User role:', userRole);


    /*
    |--------------------------------------------------------------------------
    | Show only allowed navigation items
    |--------------------------------------------------------------------------
    */

    const menuItems = document.querySelectorAll('.role-menu');

    menuItems.forEach(function (item) {

        const allowedRoles = item.dataset.roles
            .split(',')
            .map(role => role.trim());

        if (!allowedRoles.includes(userRole)) {

            item.style.display = 'none';

        }

    });


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    const logoutButton = document.getElementById('logoutButton');

    if (logoutButton) {

        logoutButton.addEventListener('click', async function () {

            const token = localStorage.getItem('auth_token');

            logoutButton.disabled = true;
            logoutButton.textContent = 'Logging out...';

            try {

                if (token) {

                    await fetch('/api/logout', {
                        method: 'POST',

                        headers: {
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    });

                }

            } catch (error) {

                console.error('Logout request failed:', error);

            } finally {

                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');

                window.location.href = '/login';

            }

        });

    }

});

</script>

</body>
</html>