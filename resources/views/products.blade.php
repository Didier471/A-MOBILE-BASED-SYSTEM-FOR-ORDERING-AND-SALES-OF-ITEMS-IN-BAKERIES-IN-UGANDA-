@extends('layouts.app')

@section('content')

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
<button class="bg-gray-900 text-white px-5 py-3 rounded-lg font-medium hover:bg-gray-800" id="addProductBtn">
    + Add Product
</button>
<!-- Add Product Modal -->
<div class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" id="productModal">
<div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
<div class="flex items-center justify-between p-6 border-b">
<h3 class="text-xl font-bold">
                Add Product
            </h3>
<button class="text-gray-500 hover:text-gray-900 text-2xl" id="closeProductModal">
                ×
            </button>
</div>
<form class="p-6 space-y-4" id="productForm">
<div>
<label class="block text-sm font-medium mb-1">
                    Category ID
                </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="productCategoryId" required="" type="number"/>
</div>
<div>
<label class="block text-sm font-medium mb-1">
                    Product Name
                </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="productName" required="" type="text"/>
</div>
<div>
<label class="block text-sm font-medium mb-1">
                    SKU
                </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="productSku" required="" type="text"/>
</div>
<div>
<label class="block text-sm font-medium mb-1">
                    Barcode
                </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="productBarcode" type="text"/>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium mb-1">
                        Cost Price
                    </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="productCostPrice" required="" step="0.01" type="number"/>
</div>
<div>
<label class="block text-sm font-medium mb-1">
                        Selling Price
                    </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="productSellingPrice" required="" step="0.01" type="number"/>
</div>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium mb-1">
                        Stock Quantity
                    </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="productStock" required="" type="number" value="0"/>
</div>
<div>
<label class="block text-sm font-medium mb-1">
                        Reorder Level
                    </label>
<input class="w-full border border-gray-300 rounded-lg px-4 py-3" id="productReorderLevel" required="" type="number" value="20"/>
</div>
</div>
<div>
<label class="block text-sm font-medium mb-1">
                    Description
                </label>
<textarea class="w-full border border-gray-300 rounded-lg px-4 py-3" id="productDescription" rows="3"></textarea>
</div>
<p class="hidden text-sm text-red-600" id="productFormError"></p>
<div class="flex justify-end gap-3 pt-3">
<button class="px-5 py-3 rounded-lg border border-gray-300" id="cancelProductBtn" type="button">
                    Cancel
                </button>
<button class="px-5 py-3 rounded-lg bg-gray-900 text-white" type="submit">
                    Save Product
                </button>
</div>
</form>
</div>
</div>
</div>
</header><section class="p-8">
<!-- Search -->
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
<input class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-900" id="productSearch" placeholder="Search products..." type="text"/>
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
<td class="text-center px-6 py-10 text-gray-500" colspan="6">
                                    Loading products...
                                </td>
</tr>
</tbody>
</table>
</div>
</div>
</section>

@endsection
