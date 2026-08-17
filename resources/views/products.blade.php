<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hot Loaf - Products</title>

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
               class="block px-4 py-3 rounded-lg bg-gray-900 text-white font-medium">
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

        <header class="bg-white border-b border-gray-200 px-8 py-5">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        Products
                    </h2>

                    <p class="text-sm text-gray-500">
                        Manage bakery products
                    </p>
                </div>

                <button
    id="addProductBtn"
    class="bg-gray-900 text-white px-5 py-3 rounded-lg font-medium hover:bg-gray-800">
    + Add Product
</button>

<!-- Add Product Modal -->
<div
    id="productModal"
    class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">

        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-bold">
                Add Product
            </h3>

            <button
                id="closeProductModal"
                class="text-gray-500 hover:text-gray-900 text-2xl">
                &times;
            </button>
        </div>

        <form id="productForm" class="p-6 space-y-4">

            <div>
                <label class="block text-sm font-medium mb-1">
                    Category ID
                </label>

                <input
                    type="number"
                    id="productCategoryId"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Product Name
                </label>

                <input
                    type="text"
                    id="productName"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    SKU
                </label>

                <input
                    type="text"
                    id="productSku"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Barcode
                </label>

                <input
                    type="text"
                    id="productBarcode"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Cost Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        id="productCostPrice"
                        required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Selling Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        id="productSellingPrice"
                        required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>

            </div>

            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Stock Quantity
                    </label>

                    <input
                        type="number"
                        id="productStock"
                        value="0"
                        required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Reorder Level
                    </label>

                    <input
                        type="number"
                        id="productReorderLevel"
                        value="20"
                        required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>

            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Description
                </label>

                <textarea
                    id="productDescription"
                    rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"></textarea>
            </div>

            <p id="productFormError" class="hidden text-sm text-red-600"></p>

            <div class="flex justify-end gap-3 pt-3">

                <button
                    type="button"
                    id="cancelProductBtn"
                    class="px-5 py-3 rounded-lg border border-gray-300">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="px-5 py-3 rounded-lg bg-gray-900 text-white">
                    Save Product
                </button>

            </div>

        </form>

    </div>

</div>

            </div>

        </header>


        <section class="p-8">

            <!-- Search -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">

                <input
                    id="productSearch"
                    type="text"
                    placeholder="Search products..."
                    class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-900"
                >

            </div>


            <!-- Products Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50 border-b border-gray-200">

                            <tr>

                                <th class="text-left px-6 py-4 text-sm font-semibold">
                                    Product
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold">
                                    SKU
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold">
                                    Selling Price
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold">
                                    Stock
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold">
                                    Status
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody id="productsTable">

                            <tr>
                                <td colspan="6" class="text-center px-6 py-10 text-gray-500">
                                    Loading products...
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