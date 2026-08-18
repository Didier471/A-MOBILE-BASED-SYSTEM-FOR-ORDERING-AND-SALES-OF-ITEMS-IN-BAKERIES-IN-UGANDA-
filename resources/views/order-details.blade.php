<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hot Loaf - Order Details</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800">

<div class="min-h-screen">

    <!-- Header -->
    <header class="bg-white border-b px-8 py-5">

        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Order Details
                </h1>

                <p class="text-sm text-gray-500">
                    View and manage order information
                </p>
            </div>

            <a href="/orders"
               class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700">
                ← Back to Orders
            </a>

        </div>

    </header>


    <main class="p-8 max-w-6xl mx-auto">

        <!-- Loading -->
        <div id="orderDetailsLoading"
             class="bg-white rounded-xl border p-10 text-center text-gray-500">

            Loading order details...

        </div>


        <!-- Order Content -->
        <div id="orderDetailsContent"
             class="hidden space-y-6">


            <!-- Order Summary -->

            <div class="bg-white rounded-xl border p-6">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div>

                        <p class="text-sm text-gray-500">
                            Order Number
                        </p>

                        <h2 id="detailOrderNumber"
                            class="text-2xl font-bold">
                            -
                        </h2>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Status
                        </p>

                        <span id="detailStatus"
                              class="inline-block mt-1 px-3 py-1 rounded-full text-sm">
                            -
                        </span>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Order Date
                        </p>

                        <p id="detailOrderDate"
                           class="font-medium">
                            -
                        </p>

                    </div>

                </div>

            </div>


            <!-- Customer -->

            <div class="bg-white rounded-xl border p-6">

                <h3 class="text-lg font-bold mb-4">
                    Customer Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>

                        <p class="text-sm text-gray-500">
                            Name
                        </p>

                        <p id="detailCustomerName"
                           class="font-medium">
                            -
                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-gray-500">
                            Email
                        </p>

                        <p id="detailCustomerEmail"
                           class="font-medium">
                            -
                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-gray-500">
                            Phone
                        </p>

                        <p id="detailCustomerPhone"
                           class="font-medium">
                            -
                        </p>

                    </div>

                </div>

            </div>


            <!-- Products -->

            <div class="bg-white rounded-xl border overflow-hidden">

                <div class="p-6 border-b">

                    <h3 class="text-lg font-bold">
                        Order Items
                    </h3>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="text-left px-6 py-4">
                                    Product
                                </th>

                                <th class="text-left px-6 py-4">
                                    Quantity
                                </th>

                                <th class="text-left px-6 py-4">
                                    Unit Price
                                </th>

                                <th class="text-left px-6 py-4">
                                    Subtotal
                                </th>

                            </tr>

                        </thead>

                        <tbody id="orderItemsTable">

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- Totals -->

            <div class="bg-white rounded-xl border p-6">

                <div class="max-w-md ml-auto space-y-3">

                    <div class="flex justify-between">

                        <span class="text-gray-500">
                            Total Amount
                        </span>

                        <span id="detailTotal">
                            UGX 0
                        </span>

                    </div>


                    <div class="flex justify-between">

                        <span class="text-gray-500">
                            Discount
                        </span>

                        <span id="detailDiscount">
                            UGX 0
                        </span>

                    </div>


                    <div class="flex justify-between">

                        <span class="text-gray-500">
                            Tax
                        </span>

                        <span id="detailTax">
                            UGX 0
                        </span>

                    </div>


                    <div class="border-t pt-3 flex justify-between text-lg font-bold">

                        <span>
                            Grand Total
                        </span>

                        <span id="detailGrandTotal">
                            UGX 0
                        </span>

                    </div>

                </div>

            </div>

<!-- Update Order Status -->

<div class="bg-white rounded-xl border p-6">

    <h3 class="text-lg font-bold mb-4">
        Update Order Status
    </h3>

    <div class="flex flex-col md:flex-row gap-4">

        <select
            id="orderStatusSelect"
            class="border border-gray-300 rounded-lg px-4 py-3 flex-1">

            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="processing">Processing</option>
            <option value="ready">Ready</option>
            <option value="completed">Completed</option>

        </select>

        <button
            id="updateOrderStatusButton"
            class="px-6 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-700">

            Update Status

        </button>

    </div>

    <p id="statusUpdateMessage"
       class="mt-3 text-sm hidden">
    </p>

</div>
            <!-- Notes -->

            <div class="bg-white rounded-xl border p-6">

                <h3 class="text-lg font-bold mb-3">
                    Notes
                </h3>

                <p id="detailNotes"
                   class="text-gray-600">
                    -
                </p>

            </div>


        </div>


        <!-- Error -->

        <div id="orderDetailsError"
             class="hidden bg-white rounded-xl border p-10 text-center text-red-600">

            Failed to load order details.

        </div>

    </main>

</div>

</body>
</html>