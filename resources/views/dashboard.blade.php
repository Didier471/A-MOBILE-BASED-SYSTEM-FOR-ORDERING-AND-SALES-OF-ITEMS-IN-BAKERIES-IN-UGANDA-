@extends('layouts.app')

@section('content')

<header class="bg-white border-b border-gray-200 px-8 py-5">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Dashboard
            </h2>

            <p id="dashboardSubtitle" class="text-sm text-gray-500">
                Loading dashboard...
            </p>
        </div>

        <div class="text-right">
            <p id="userName" class="font-medium">
                Loading...
            </p>

            <p id="userRole" class="text-sm text-gray-500">
                Loading...
            </p>
        </div>
    </div>
</header>


<section class="p-8">

    <!-- Summary Cards -->
    <div id="summaryCards"
         class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    </div>


    <!-- Dashboard Details -->
    <div id="dashboardDetails"
         class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
    </div>


    <!-- Financial Overview -->
    <div id="financialOverview"
         class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-8 hidden">

        <h3 class="text-lg font-bold">
            Financial Overview
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">

            <div>
                <p class="text-sm text-gray-500">
                    Sales
                </p>

                <p id="financialSales" class="text-2xl font-bold mt-1">
                    UGX 0
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Payments Received
                </p>

                <p id="financialPayments" class="text-2xl font-bold mt-1">
                    UGX 0
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Purchases
                </p>

                <p id="financialPurchases" class="text-2xl font-bold mt-1">
                    UGX 0
                </p>
            </div>

        </div>
    </div>


    <!-- Lists -->
    <div id="listSection"
         class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-8 hidden">

        <h3 id="listTitle" class="text-lg font-bold"></h3>

        <div id="listContent" class="mt-5 space-y-3"></div>

    </div>


    <!-- Error -->
    <div id="dashboardError"
         class="hidden bg-red-50 border border-red-200 text-red-700 rounded-xl p-6 mt-8">
    </div>

</section>


<script>

document.addEventListener('DOMContentLoaded', async function () {

    const token = localStorage.getItem('auth_token');
    const storedUser = localStorage.getItem('user');

    if (!token) {
        window.location.href = '/login';
        return;
    }


    const summaryCards =
        document.getElementById('summaryCards');

    const dashboardDetails =
        document.getElementById('dashboardDetails');

    const financialOverview =
        document.getElementById('financialOverview');

    const dashboardError =
        document.getElementById('dashboardError');

    const listSection =
        document.getElementById('listSection');

    const listTitle =
        document.getElementById('listTitle');

    const listContent =
        document.getElementById('listContent');


    /*
     * Number formatting
     */

    function formatNumber(value) {

        return new Intl.NumberFormat('en-US')
            .format(Number(value || 0));

    }


    /*
     * Currency formatting
     */

    function formatCurrency(value) {

        return 'UGX ' + formatNumber(value);

    }


    /*
     * Summary card
     */

    function createCard(title, value, description) {

        return `
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                <p class="text-sm text-gray-500">
                    ${title}
                </p>

                <h3 class="text-3xl font-bold mt-2">
                    ${value}
                </h3>

                <p class="text-sm text-gray-500 mt-2">
                    ${description}
                </p>

            </div>
        `;

    }


    /*
     * Status card
     */

    function createStatusCard(title, values) {

        let rows = '';

        Object.entries(values || {}).forEach(([key, value]) => {

            rows += `
                <div class="flex justify-between">

                    <span class="capitalize">
                        ${key.replaceAll('_', ' ')}
                    </span>

                    <span class="font-bold">
                        ${formatNumber(value)}
                    </span>

                </div>
            `;

        });


        return `
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                <h3 class="text-lg font-bold">
                    ${title}
                </h3>

                <div class="mt-6 space-y-4">
                    ${rows}
                </div>

            </div>
        `;

    }


    /*
     * Display product/restock lists
     */

    function showList(title, items) {

        if (!Array.isArray(items) || items.length === 0) {
            return;
        }


        listTitle.textContent = title;


        listContent.innerHTML = items.map(item => {

            return `
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">

                    <div>

                        <p class="font-medium text-gray-900">
                            ${item.name || 'Product'}
                        </p>

                        <p class="text-sm text-gray-500">

                            ${item.sku
                                ? 'SKU: ' + item.sku
                                : ''}

                            ${item.stock_quantity !== undefined
                                ? ' • Stock: ' + formatNumber(item.stock_quantity)
                                : ''}

                        </p>

                    </div>


                    ${item.reorder_level !== undefined

                        ? `
                            <span class="text-sm font-semibold">
                                Reorder: ${formatNumber(item.reorder_level)}
                            </span>
                          `

                        : ''
                    }

                </div>
            `;

        }).join('');


        listSection.classList.remove('hidden');

    }


    try {

        /*
         * Get dashboard data
         */

        const response = await fetch('/api/dashboard', {

            headers: {

                'Accept': 'application/json',

                'Authorization': `Bearer ${token}`

            }

        });


        /*
         * Unauthorized
         */

        if (response.status === 401) {

            localStorage.removeItem('auth_token');

            localStorage.removeItem('user');

            window.location.href = '/login';

            return;

        }


        /*
         * Other API errors
         */

        if (!response.ok) {

            throw new Error(
                `HTTP error: ${response.status}`
            );

        }


        const result = await response.json();


        console.log(
            'Dashboard data:',
            result
        );


        /*
         * User information
         */

        const user =
            result.user ||
            JSON.parse(storedUser || '{}');


        const roles =
            user.roles || [];


        const role =
            result.role ||
            roles[0] ||
            'User';


        /*
         * Dashboard data
         */

        const data =
            result.data ||
            result;


        /*
         * Display user
         */

        document.getElementById('userName').textContent =
            user.name || 'User';


        document.getElementById('userRole').textContent =
            role;


        document.getElementById('dashboardSubtitle').textContent =
            `${role} dashboard`;


        /*
         =====================================================
         ADMIN DASHBOARD
         =====================================================
        */

        if (role === 'Admin') {

            const summary =
                data.summary || {};


            const sales =
                data.sales || {};


            const payments =
                data.payments || {};


            const purchases =
                data.purchases || {};


            summaryCards.innerHTML =

                createCard(
                    'Total Products',
                    formatNumber(
                        summary.total_products
                    ),
                    'Products in system'
                )

                +

                createCard(
                    'Customers',
                    formatNumber(
                        summary.total_customers
                    ),
                    'Registered customers'
                )

                +

                createCard(
                    'Total Orders',
                    formatNumber(
                        summary.total_orders
                    ),
                    'Orders processed'
                )

                +

                createCard(
                    'Total Sales',
                    formatCurrency(
                        sales.total_amount
                    ),
                    'Total sales value'
                );


            dashboardDetails.innerHTML =

                createStatusCard(
                    'Order Status',
                    data.orders
                )

                +

                createStatusCard(
                    'Delivery Status',
                    data.deliveries
                );


            financialOverview.classList.remove(
                'hidden'
            );


            document.getElementById(
                'financialSales'
            ).textContent =
                formatCurrency(
                    sales.total_amount
                );


            document.getElementById(
                'financialPayments'
            ).textContent =
                formatCurrency(
                    payments.total_received
                );


            document.getElementById(
                'financialPurchases'
            ).textContent =
                formatCurrency(
                    purchases.total_amount
                );


            showList(
                'Low Stock Products',
                data.low_stock_items
            );

        }


        /*
         =====================================================
         MANAGER DASHBOARD
         =====================================================
        */

        else if (role === 'Manager') {

            const summary =
                data.summary || {};


            const sales =
                data.sales || {};


            const payments =
                data.payments || {};


            const purchases =
                data.purchases || {};


            summaryCards.innerHTML =

                createCard(
                    'Total Orders',
                    formatNumber(
                        summary.orders
                    ),
                    'Orders in the system'
                )

                +

                createCard(
                    'Total Sales',
                    formatCurrency(
                        sales.total_amount
                    ),
                    'Sales generated'
                )

                +

                createCard(
                    'Total Purchases',
                    formatCurrency(
                        purchases.total_amount
                    ),
                    'Procurement value'
                )

                +

                createCard(
                    'Low Stock',
                    formatNumber(
                        data.inventory?.low_stock
                    ),
                    'Products requiring attention'
                );


            dashboardDetails.innerHTML =

                createStatusCard(
                    'Order Status',
                    data.orders
                )

                +

                createStatusCard(
                    'Delivery Status',
                    data.deliveries
                );


            financialOverview.classList.remove(
                'hidden'
            );


            document.getElementById(
                'financialSales'
            ).textContent =
                formatCurrency(
                    sales.total_amount
                );


            document.getElementById(
                'financialPayments'
            ).textContent =
                formatCurrency(
                    payments.total_received
                );


            document.getElementById(
                'financialPurchases'
            ).textContent =
                formatCurrency(
                    purchases.total_amount
                );


            showList(
                'Low Stock Products',
                data.low_stock_items
            );

        }


        /*
         =====================================================
         SALES STAFF DASHBOARD
         =====================================================
        */

        else if (role === 'Sales Staff') {

            const summary =
                data.summary || {};


            const today =
                data.today || {};


            summaryCards.innerHTML =

                createCard(
                    'Total Orders',
                    formatNumber(
                        summary.orders
                    ),
                    'Orders handled'
                )

                +

                createCard(
                    'Total Sales',
                    formatNumber(
                        summary.sales
                    ),
                    'Sales recorded'
                )

                +

                createCard(
                    'Customers',
                    formatNumber(
                        summary.customers
                    ),
                    'Customers served'
                )

                +

                createCard(
                    "Today's Sales",
                    formatCurrency(
                        today.sales_amount
                    ),
                    'Sales made today'
                );


            dashboardDetails.innerHTML =

                createStatusCard(
                    'Today',
                    {
                        orders: today.orders,
                        payments: today.payments
                    }
                )

                +

                createStatusCard(
                    'Order Status',
                    data.order_status
                );

        }


        /*
         =====================================================
         INVENTORY STAFF DASHBOARD
         =====================================================
        */

        else if (role === 'Inventory Staff') {

            const summary =
                data.summary || {};


            summaryCards.innerHTML =

                createCard(
                    'Total Products',
                    formatNumber(
                        summary.products
                    ),
                    'Products managed'
                )

                +

                createCard(
                    'Low Stock',
                    formatNumber(
                        summary.low_stock
                    ),
                    'Products below reorder level'
                )

                +

                createCard(
                    'Out of Stock',
                    formatNumber(
                        summary.out_of_stock
                    ),
                    'Products unavailable'
                )

                +

                createCard(
                    'Active Products',
                    formatNumber(
                        summary.active
                    ),
                    'Currently active'
                );


            dashboardDetails.innerHTML =

                createStatusCard(
                    'Inventory Overview',
                    {
                        low_stock:
                            summary.low_stock,

                        out_of_stock:
                            summary.out_of_stock,

                        active_products:
                            summary.active
                    }
                );


            showList(
                'Products Requiring Restock',
                data.low_stock_items
            );

        }


        /*
         =====================================================
         PROCUREMENT STAFF DASHBOARD
         =====================================================
        */

        else if (role === 'Procurement Staff') {

            const summary =
                data.summary || {};


            const today =
                data.today || {};


            summaryCards.innerHTML =

                createCard(
                    'Suppliers',
                    formatNumber(
                        summary.suppliers
                    ),
                    'Suppliers managed'
                )

                +

                createCard(
                    'Purchases',
                    formatCurrency(
                        summary.purchases
                    ),
                    'Total purchase value'
                )

                +

                createCard(
                    'Products',
                    formatNumber(
                        summary.products
                    ),
                    'Products supplied'
                )

                +

                createCard(
                    "Today's Purchases",
                    formatCurrency(
                        today.amount
                    ),
                    'Purchases made today'
                );


            const stockNeeds =
                Array.isArray(data.stock_needs)
                    ? data.stock_needs.length
                    : data.stock_needs || 0;


            dashboardDetails.innerHTML =

                createStatusCard(
                    'Procurement Overview',
                    {
                        purchases_today:
                            today.count,

                        low_stock_items:
                            stockNeeds
                    }
                );


            showList(
                'Products Needing Procurement',
                data.stock_needs
            );

        }


        /*
         =====================================================
         DELIVERY STAFF DASHBOARD
         =====================================================
        */

        else if (role === 'Delivery Staff') {

            const summary =
                data.summary || {};


            summaryCards.innerHTML =

                createCard(
                    'Total Deliveries',
                    formatNumber(
                        summary.deliveries
                    ),
                    'Assigned deliveries'
                )

                +

                createCard(
                    'Pending',
                    formatNumber(
                        summary.pending
                    ),
                    'Awaiting action'
                )

                +

                createCard(
                    'Assigned',
                    formatNumber(
                        summary.assigned
                    ),
                    'Currently assigned'
                )

                +

                createCard(
                    'Delivered',
                    formatNumber(
                        summary.delivered
                    ),
                    'Successfully delivered'
                );


            dashboardDetails.innerHTML =

                createStatusCard(
                    'Delivery Overview',
                    {
                        pending:
                            summary.pending,

                        assigned:
                            summary.assigned,

                        delivered:
                            summary.delivered
                    }
                );

        }


        /*
         =====================================================
         FALLBACK
         =====================================================
        */

        else {

            const summary =
                data.summary || {};


            summaryCards.innerHTML =

                createCard(
                    'Products',
                    formatNumber(
                        summary.total_products ||
                        summary.products
                    ),
                    'Products in system'
                )

                +

                createCard(
                    'Customers',
                    formatNumber(
                        summary.total_customers ||
                        summary.customers
                    ),
                    'Registered customers'
                )

                +

                createCard(
                    'Orders',
                    formatNumber(
                        summary.total_orders ||
                        summary.orders
                    ),
                    'Orders'
                )

                +

                createCard(
                    'Sales',
                    formatCurrency(
                        data.sales?.total_amount
                    ),
                    'Sales value'
                );

        }


    } catch (error) {

        console.error(
            'Dashboard error:',
            error
        );


        summaryCards.innerHTML = '';


        dashboardError.textContent =
            'Unable to load dashboard data. Please refresh the page and try again.';


        dashboardError.classList.remove(
            'hidden'
        );

    }

});

</script>

@endsection