document.addEventListener('DOMContentLoaded', () => {

    // =========================
    // LOGIN
    // =========================

    const loginForm = document.getElementById('loginForm');

    if (loginForm) {

        loginForm.addEventListener('submit', async (event) => {

            event.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('loginError');

            errorMessage.classList.add('hidden');

            try {

                const response = await fetch('/api/login', {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },

                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Login failed.');
                }

                localStorage.setItem('auth_token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));

                window.location.href = '/dashboard';

            } catch (error) {

                errorMessage.textContent = error.message;
                errorMessage.classList.remove('hidden');

                console.error('Login error:', error);
            }
        });

    }


    // =========================
    // PRODUCTS
    // =========================

    const productsTable = document.getElementById('productsTable');

    if (productsTable) {
        loadProducts();
    }


async function loadProducts(search = '') {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const url = search
            ? `/api/products?search=${encodeURIComponent(search)}&per_page=1000`
            : `/api/products?per_page=1000`;

        const response = await fetch(url, {

            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }

        });

        if (response.status === 401) {

            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Products data:', result);

        displayProducts(result);

    } catch (error) {

        console.error('Products error:', error);

    }
}


    function displayProducts(result) {

        const products = result.data?.data ?? result.data ?? [];

        if (!products.length) {

            productsTable.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center px-6 py-10 text-gray-500">
                        No products found.
                    </td>
                </tr>
            `;

            return;
        }


        productsTable.innerHTML = products.map(product => `

            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4">

                    <div class="font-medium text-gray-900">
                        ${escapeHtml(product.name)}
                    </div>

                    <div class="text-sm text-gray-500">
                        ${escapeHtml(product.barcode ?? '')}
                    </div>

                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(product.sku ?? '-')}
                </td>

                <td class="px-6 py-4 font-medium">
                    UGX ${formatMoney(product.selling_price)}
                </td>

                <td class="px-6 py-4">
                    ${product.stock_quantity ?? 0}
                </td>

                <td class="px-6 py-4">

                    ${
                        product.status
                        ? `<span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                            Active
                           </span>`
                        : `<span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                            Inactive
                           </span>`
                    }

                </td>

                <td class="px-6 py-4">

                    <button
                        class="text-sm font-medium text-gray-900 hover:underline"
                        onclick="editProduct(${product.id})">
                        Edit
                    </button>

                    <button
                        class="ml-4 text-sm font-medium text-red-600 hover:underline"
                        onclick="deleteProduct(${product.id})">
                        Delete
                    </button>

                </td>

            </tr>

        `).join('');
    }


    function formatMoney(value) {

        return Number(value || 0).toLocaleString('en-UG', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    }


    function escapeHtml(value) {

        const div = document.createElement('div');

        div.textContent = value ?? '';

        return div.innerHTML;

    }
    // =========================
// SEARCH PRODUCTS
// =========================

const productSearch = document.getElementById('productSearch');

if (productSearch) {

    productSearch.addEventListener('input', async () => {

        const searchTerm = productSearch.value.trim();

        await loadProducts(searchTerm);

    });

}
// =========================
// ADD PRODUCT
// =========================

const addProductBtn = document.getElementById('addProductBtn');
const productModal = document.getElementById('productModal');
const closeProductModal = document.getElementById('closeProductModal');
const cancelProductBtn = document.getElementById('cancelProductBtn');
const productForm = document.getElementById('productForm');
const productCategorySelect = document.getElementById('productCategoryId');

async function loadProductCategories() {
    if (!productCategorySelect) return;
    const token = localStorage.getItem('auth_token');
    try {
        const response = await fetch('/api/categories', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });
        const result = await response.json();
        const categories = result.data?.data ?? result.data ?? [];
        productCategorySelect.innerHTML = '<option value="">Select category...</option>' +
            categories.map(category => `<option value="${category.id}">${escapeHtml(category.name)}</option>`).join('');
    } catch (error) {
        productCategorySelect.innerHTML = '<option value="">Unable to load categories</option>';
    }
}

if (addProductBtn) {

    addProductBtn.addEventListener('click', () => {
        productModal.classList.remove('hidden');
        loadProductCategories();
    });

}

if (closeProductModal) {

    closeProductModal.addEventListener('click', () => {
        productModal.classList.add('hidden');
    });

}

if (cancelProductBtn) {

    cancelProductBtn.addEventListener('click', () => {
        productModal.classList.add('hidden');
    });

}

if (productForm) {

    productForm.addEventListener('submit', async (event) => {

        event.preventDefault();

        const token = localStorage.getItem('auth_token');
        const errorMessage = document.getElementById('productFormError');

        errorMessage.classList.add('hidden');

        const product = {
            category_id: Number(document.getElementById('productCategoryId').value),
            name: document.getElementById('productName').value,
            sku: document.getElementById('productSku').value,
            barcode: document.getElementById('productBarcode').value || null,
            cost_price: Number(document.getElementById('productCostPrice').value),
            selling_price: Number(document.getElementById('productSellingPrice').value),
            stock_quantity: Number(document.getElementById('productStock').value),
            reorder_level: Number(document.getElementById('productReorderLevel').value),
            description: document.getElementById('productDescription').value || null,
            status: true
        };

        try {

            const response = await fetch('/api/products', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                },

                body: JSON.stringify(product)

            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Failed to create product.'
                );
            }

            alert('Product created successfully.');

            productForm.reset();

            productModal.classList.add('hidden');

            loadProducts();

        } catch (error) {

            console.error('Create product error:', error);

            errorMessage.textContent = error.message;
            errorMessage.classList.remove('hidden');

        }

    });

}

    // =========================
    // DELETE PRODUCT
    // =========================

    window.deleteProduct = async function (id) {

        const token = localStorage.getItem('auth_token');

        if (!confirm('Are you sure you want to delete this product?')) {
            return;
        }

        try {

            const response = await fetch(`/api/products/${id}`, {

                method: 'DELETE',

                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }

            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to delete product.');
            }

            alert('Product deleted successfully.');

            loadProducts();

        } catch (error) {

            console.error('Delete product error:', error);

            alert(error.message);

        }

    };


    // =========================
    // EDIT PRODUCT
    // =========================

    window.editProduct = async function (id) {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch(`/api/products/${id}`, {

            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }

        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(
                result.message || 'Failed to load product.'
            );
        }

        const product = result.data ?? result;

        document.getElementById('productCategoryId').value =
            product.category_id ?? '';

        document.getElementById('productName').value =
            product.name ?? '';

        document.getElementById('productSku').value =
            product.sku ?? '';

        document.getElementById('productBarcode').value =
            product.barcode ?? '';

        document.getElementById('productCostPrice').value =
            product.cost_price ?? '';

        document.getElementById('productSellingPrice').value =
            product.selling_price ?? '';

        document.getElementById('productStock').value =
            product.stock_quantity ?? 0;

        document.getElementById('productReorderLevel').value =
            product.reorder_level ?? 20;

        document.getElementById('productDescription').value =
            product.description ?? '';

        productModal.classList.remove('hidden');

    } catch (error) {

        console.error('Edit product error:', error);

        alert(error.message);

    }

};
// =========================
// INVENTORY
// =========================

const inventoryTable = document.getElementById('inventoryTable');

if (inventoryTable) {
    loadInventory();
}

async function loadInventory() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch('/api/products', {

            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }

        });

        if (response.status === 401) {

            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Inventory data:', result);

        displayInventory(result);

    } catch (error) {

        console.error('Inventory error:', error);

        inventoryTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load inventory.
                </td>
            </tr>
        `;
    }
}

function displayInventory(result) {

    const inventory = result.data?.data ?? result.data ?? [];

    const totalElement = document.getElementById('inventoryTotal');
    const lowStockElement = document.getElementById('inventoryLowStock');
    const outOfStockElement = document.getElementById('inventoryOutOfStock');

    totalElement.textContent = inventory.length;

    let lowStock = 0;
    let outOfStock = 0;

    inventory.forEach(product => {

        const quantity = Number(
            product.stock_quantity ?? 0
        );

        const reorderLevel = Number(
            product.reorder_level ?? 0
        );

        if (quantity === 0) {
            outOfStock++;
        } else if (quantity <= reorderLevel) {
            lowStock++;
        }

    });

    lowStockElement.textContent = lowStock;
    outOfStockElement.textContent = outOfStock;


    if (!inventory.length) {

        inventoryTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-gray-500">
                    No inventory records found.
                </td>
            </tr>
        `;

        return;
    }


    inventoryTable.innerHTML = inventory.map(product => {

        const quantity = Number(
            product.stock_quantity ?? 0
        );

        const reorderLevel = Number(
            product.reorder_level ?? 0
        );

        let status;

        if (quantity === 0) {

            status = `
                <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700">
                    Out of Stock
                </span>
            `;

        } else if (quantity <= reorderLevel) {

            status = `
                <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                    Low Stock
                </span>
            `;

        } else {

            status = `
                <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                    In Stock
                </span>
            `;

        }


        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(product.name ?? 'Unknown Product')}
                </td>

                <td class="px-6 py-4">
                    ${quantity}
                </td>

                <td class="px-6 py-4">
                    ${reorderLevel}
                </td>

                <td class="px-6 py-4">
                    ${status}
                </td>

            </tr>
        `;

    }).join('');
}
// =========================
// ORDERS
// =========================

const ordersTable = document.getElementById('ordersTable');

if (ordersTable) {
    loadOrders();
}


async function loadOrders() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch('/api/orders', {

            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }

        });

        if (response.status === 401) {

            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Orders data:', result);

        displayOrders(result);

    } catch (error) {

        console.error('Orders error:', error);

        ordersTable.innerHTML = `
            <tr>
                <td colspan="6"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load orders.
                </td>
            </tr>
        `;
    }
}


function displayOrders(result) {

    const orders = result.data?.data ?? result.data ?? [];

    const total = document.getElementById('ordersTotal');
    const pending = document.getElementById('ordersPending');
    const processing = document.getElementById('ordersProcessing');
    const completed = document.getElementById('ordersCompleted');

    total.textContent = orders.length;

    pending.textContent = orders.filter(
        order => order.status === 'pending'
    ).length;

    processing.textContent = orders.filter(
        order => order.status === 'processing'
    ).length;

    completed.textContent = orders.filter(
        order => order.status === 'completed'
    ).length;


    if (!orders.length) {

        ordersTable.innerHTML = `
            <tr>
                <td colspan="6"
                    class="text-center px-6 py-10 text-gray-500">
                    No orders found.
                </td>
            </tr>
        `;

        return;
    }
    const orderSearch = document.getElementById('orderSearch');

if (orderSearch) {

    orderSearch.addEventListener('input', function () {

        const searchValue = this.value.toLowerCase().trim();

        const rows = ordersTable.querySelectorAll('tr');

        rows.forEach(row => {

            const orderNumber = row
                .querySelector('td')
                ?.textContent
                .toLowerCase() ?? '';

            row.style.display =
                orderNumber.includes(searchValue)
                    ? ''
                    : 'none';

        });

    });

}


    ordersTable.innerHTML = orders.map(order => {

        const items = order.items ?? [];

        let statusClass = 'bg-gray-100 text-gray-700';

        if (order.status === 'pending') {
            statusClass = 'bg-yellow-100 text-yellow-700';
        }

        if (order.status === 'processing') {
            statusClass = 'bg-blue-100 text-blue-700';
        }

        if (order.status === 'completed') {
            statusClass = 'bg-green-100 text-green-700';
        }

        if (order.status === 'confirmed') {
            statusClass = 'bg-purple-100 text-purple-700';
        }

        if (order.status === 'ready') {
            statusClass = 'bg-indigo-100 text-indigo-700';
        }


        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(order.order_number ?? 'N/A')}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(
                        order.customer?.name ?? 'Walk-in Customer'
                    )}
                </td>

                <td class="px-6 py-4">
                    ${items.length}
                </td>

                <td class="px-6 py-4 font-medium">
                    UGX ${Number(order.grand_total ?? 0).toLocaleString()}
                </td>

                <td class="px-6 py-4">

                    <span class="px-3 py-1 text-xs rounded-full ${statusClass}">
                        ${escapeHtml(order.status ?? 'unknown')}
                    </span>

                </td>

                <td class="px-6 py-4">

                    <button
                        onclick="viewOrder(${order.id})"
                        class="text-blue-600 hover:text-blue-800 font-medium">
                        View
                    </button>

                </td>

            </tr>
        `;

    }).join('');
}


window.viewOrder = function (id) {

    window.location.href = `/orders/${id}`;

};
// =========================
// ORDER DETAILS
// =========================

const orderDetailsContent = document.getElementById('orderDetailsContent');

if (orderDetailsContent) {
    loadOrderDetails();
}


async function loadOrderDetails() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    const parts = window.location.pathname.split('/');
    const orderId = parts[parts.length - 1];

    try {

        const response = await fetch(`/api/orders/${orderId}`, {

            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }

        });

        if (response.status === 401) {

            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Order details:', result);

        displayOrderDetails(result);

    } catch (error) {

        console.error('Order details error:', error);

        document
            .getElementById('orderDetailsLoading')
            .classList.add('hidden');

        document
            .getElementById('orderDetailsError')
            .classList.remove('hidden');
    }
}


function displayOrderDetails(result) {

    const order = result.data ?? result;

    document
        .getElementById('orderDetailsLoading')
        .classList.add('hidden');

    document
        .getElementById('orderDetailsContent')
        .classList.remove('hidden');


    document.getElementById('detailOrderNumber').textContent =
        order.order_number ?? '-';


    document.getElementById('detailOrderDate').textContent =
        order.created_at
            ? new Date(order.created_at).toLocaleString()
            : '-';


    const statusElement =
        document.getElementById('detailStatus');

    statusElement.textContent =
        order.status ?? '-';

    statusElement.className =
        'inline-block mt-1 px-3 py-1 rounded-full text-sm ' +
        getOrderStatusClass(order.status);
        // =========================
// UPDATE ORDER STATUS
// =========================

const orderStatusSelect =
    document.getElementById('orderStatusSelect');

const updateOrderStatusButton =
    document.getElementById('updateOrderStatusButton');

const statusUpdateMessage =
    document.getElementById('statusUpdateMessage');


if (updateOrderStatusButton) {

    updateOrderStatusButton.addEventListener('click', updateOrderStatus);

}


async function updateOrderStatus() {

    const token = localStorage.getItem('auth_token');

    const parts = window.location.pathname.split('/');
    const orderId = parts[parts.length - 1];

    const newStatus = orderStatusSelect.value;


    updateOrderStatusButton.disabled = true;

    updateOrderStatusButton.textContent = 'Updating...';


    try {

        const response = await fetch(`/api/orders/${orderId}`, {

            method: 'PUT',

            headers: {

                'Accept': 'application/json',

                'Content-Type': 'application/json',

                'Authorization': `Bearer ${token}`

            },

            body: JSON.stringify({

                status: newStatus

            })

        });


        const result = await response.json();

        console.log('Status update:', result);


        if (!response.ok) {

            throw new Error(
                result.message ?? `HTTP error: ${response.status}`
            );

        }


        statusUpdateMessage.textContent =
            'Order status updated successfully.';

        statusUpdateMessage.className =
            'mt-3 text-sm text-green-600';


        document.getElementById('detailStatus').textContent =
            newStatus;

        document.getElementById('detailStatus').className =
            'inline-block mt-1 px-3 py-1 rounded-full text-sm ' +
            getOrderStatusClass(newStatus);


    } catch (error) {

        console.error('Status update error:', error);

        statusUpdateMessage.textContent =
            error.message;

        statusUpdateMessage.className =
            'mt-3 text-sm text-red-600';


    } finally {

        updateOrderStatusButton.disabled = false;

        updateOrderStatusButton.textContent =
            'Update Status';

    }

}


    const customer = order.customer;

    document.getElementById('detailCustomerName').textContent =
        customer?.name ?? 'Walk-in Customer';

    document.getElementById('detailCustomerEmail').textContent =
        customer?.email ?? '-';

    document.getElementById('detailCustomerPhone').textContent =
        customer?.phone ?? '-';


    document.getElementById('detailTotal').textContent =
        formatCurrency(order.total_amount);

    document.getElementById('detailDiscount').textContent =
        formatCurrency(order.discount);

    document.getElementById('detailTax').textContent =
        formatCurrency(order.tax);

    document.getElementById('detailGrandTotal').textContent =
        formatCurrency(order.grand_total);


    document.getElementById('detailNotes').textContent =
        order.notes ?? 'No notes';


    const items = order.items ?? [];

    const table =
        document.getElementById('orderItemsTable');


    if (!items.length) {

        table.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-8 text-gray-500">
                    No items found.
                </td>
            </tr>
        `;

        return;
    }


    table.innerHTML = items.map(item => {

        return `
            <tr class="border-b">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(
                        item.product?.name ?? 'Unknown Product'
                    )}
                </td>

                <td class="px-6 py-4">
                    ${item.quantity ?? 0}
                </td>

                <td class="px-6 py-4">
                    ${formatCurrency(item.unit_price)}
                </td>

                <td class="px-6 py-4 font-medium">
                    ${formatCurrency(item.subtotal)}
                </td>

            </tr>
        `;

    }).join('');
}


function formatCurrency(value) {

    return `UGX ${Number(value ?? 0).toLocaleString()}`;

}


function getOrderStatusClass(status) {

    switch (status) {

        case 'pending':
            return 'bg-yellow-100 text-yellow-700';

        case 'confirmed':
            return 'bg-purple-100 text-purple-700';

        case 'processing':
            return 'bg-blue-100 text-blue-700';

        case 'ready':
            return 'bg-indigo-100 text-indigo-700';

        case 'completed':
            return 'bg-green-100 text-green-700';

        default:
            return 'bg-gray-100 text-gray-700';
    }

}
// =========================
// SALES
// =========================

const salesTable = document.getElementById('salesTable');

if (salesTable) {
    loadSales();
}


async function loadSales() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch('/api/sales', {

            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }

        });


        if (response.status === 401) {

            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            window.location.href = '/login';
            return;
        }


        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }


        const result = await response.json();

        console.log('Sales data:', result);

        displaySales(result);


    } catch (error) {

        console.error('Sales error:', error);

        salesTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load sales.
                </td>
            </tr>
        `;
    }
}


function displaySales(result) {

    const sales = result.data?.data ?? result.data ?? [];

    const totalElement =
        document.getElementById('salesTotal');

    const countElement =
        document.getElementById('salesCount');

    const averageElement =
        document.getElementById('salesAverage');


    if (!Array.isArray(sales)) {

        console.error('Unexpected sales response:', result);

        return;
    }


    const total = sales.reduce((sum, sale) => {

        return sum + Number(
            sale.grand_total ??
            sale.total_amount ??
            0
        );

    }, 0);


    const count = sales.length;

    const average = count > 0
        ? total / count
        : 0;


    totalElement.textContent =
        `UGX ${formatNumber(total)}`;


    countElement.textContent =
        count;


    averageElement.textContent =
        `UGX ${formatNumber(average)}`;


    if (!sales.length) {

        salesTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-gray-500">
                    No sales records found.
                </td>
            </tr>
        `;

        return;
    }


    salesTable.innerHTML = sales.map(sale => {

        const customer =
            sale.customer?.name ??
            'Walk-in Customer';


        const amount =
            Number(
                sale.grand_total ??
                sale.total_amount ??
                0
            );


        const date =
            sale.created_at
                ? new Date(sale.created_at).toLocaleDateString()
                : '-';


        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(
                        sale.sale_number ??
                        '-'
                    )}
                </td>


                <td class="px-6 py-4">
                    ${escapeHtml(customer)}
                </td>


                <td class="px-6 py-4">
                    UGX ${formatNumber(amount)}
                </td>


                <td class="px-6 py-4">
                    ${date}
                </td>

            </tr>
        `;

    }).join('');


    setupSalesSearch(sales);

}


function setupSalesSearch(sales) {

    const search =
        document.getElementById('salesSearch');


    if (!search) {
        return;
    }


    search.addEventListener('input', function () {

        const value =
            this.value.toLowerCase().trim();


        const rows =
            document.querySelectorAll('#salesTable tr');


        rows.forEach(row => {

            row.style.display =
                row.textContent
                    .toLowerCase()
                    .includes(value)
                    ? ''
                    : 'none';

        });

    });

}


function formatNumber(number) {

    return Number(number).toLocaleString(
        'en-UG',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }
    );

}
// =========================
// PAYMENTS
// =========================

const paymentsTable = document.getElementById('paymentsTable');

if (paymentsTable) {
    loadPayments();
}

async function loadPayments() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch('/api/payments', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Payments data:', result);

        displayPayments(result);

    } catch (error) {

        console.error('Payments error:', error);

        paymentsTable.innerHTML = `
            <tr>
                <td colspan="5"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load payments.
                </td>
            </tr>
        `;
    }
}


function displayPayments(result) {

    const payments = result.data?.data ?? result.data ?? [];

    const totalElement =
        document.getElementById('paymentsTotal');

    const countElement =
        document.getElementById('paymentsCount');

    const completedElement =
        document.getElementById('paymentsCompleted');

    if (!Array.isArray(payments)) {
        console.error('Unexpected payments response:', result);
        return;
    }

    const total = payments.reduce((sum, payment) => {
        return sum + Number(payment.amount ?? 0);
    }, 0);

    const count = payments.length;

    const completed = payments.filter(payment =>
        payment.status === 'completed'
    ).length;

    totalElement.textContent =
        `UGX ${formatNumber(total)}`;

    countElement.textContent = count;

    completedElement.textContent = completed;


    if (!payments.length) {

        paymentsTable.innerHTML = `
            <tr>
                <td colspan="5"
                    class="text-center px-6 py-10 text-gray-500">
                    No payment records found.
                </td>
            </tr>
        `;

        return;
    }


    paymentsTable.innerHTML = payments.map(payment => {

        const saleNumber =
            payment.sale?.sale_number ??
            'N/A';

        const amount =
            Number(payment.amount ?? 0);

        const method =
            payment.payment_method ?? 'N/A';

        const status =
            payment.status ?? 'N/A';

        const date =
            payment.paid_at
                ? new Date(payment.paid_at).toLocaleDateString()
                : '-';


        let statusBadge;

        if (status === 'completed') {

            statusBadge = `
                <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                    Completed
                </span>
            `;

        } else {

            statusBadge = `
                <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                    ${escapeHtml(status)}
                </span>
            `;
        }


        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(saleNumber)}
                </td>

                <td class="px-6 py-4">
                    UGX ${formatNumber(amount)}
                </td>

                <td class="px-6 py-4 capitalize">
                    ${escapeHtml(method)}
                </td>

                <td class="px-6 py-4">
                    ${statusBadge}
                </td>

                <td class="px-6 py-4">
                    ${date}
                </td>

            </tr>
        `;

    }).join('');


    setupPaymentsSearch();

}


function setupPaymentsSearch() {

    const search =
        document.getElementById('paymentsSearch');

    if (!search) {
        return;
    }

    search.addEventListener('input', function () {

        const value =
            this.value.toLowerCase().trim();

        const rows =
            document.querySelectorAll('#paymentsTable tr');

        rows.forEach(row => {

            row.style.display =
                row.textContent
                    .toLowerCase()
                    .includes(value)
                    ? ''
                    : 'none';

        });

    });
}
// =========================
// PURCHASES
// =========================

const purchasesTable = document.getElementById('purchasesTable');

if (purchasesTable) {
    loadPurchases();
}

async function loadPurchases() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch('/api/purchases', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Purchases data:', result);

        displayPurchases(result);

    } catch (error) {

        console.error('Purchases error:', error);

        purchasesTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load purchases.
                </td>
            </tr>
        `;
    }
}


function displayPurchases(result) {

    const purchases =
        result.data?.data ??
        result.data ??
        [];

    const totalElement =
        document.getElementById('purchasesTotal');

    const countElement =
        document.getElementById('purchasesCount');

    const averageElement =
        document.getElementById('purchasesAverage');

    if (!Array.isArray(purchases)) {
        console.error('Unexpected purchases response:', result);
        return;
    }

    const total = purchases.reduce((sum, purchase) => {

        return sum + Number(
            purchase.grand_total ??
            purchase.total_amount ??
            purchase.total ??
            0
        );

    }, 0);

    const count = purchases.length;

    const average = count > 0
        ? total / count
        : 0;


    totalElement.textContent =
        `UGX ${formatNumber(total)}`;

    countElement.textContent =
        count;

    averageElement.textContent =
        `UGX ${formatNumber(average)}`;


    if (!purchases.length) {

        purchasesTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-gray-500">
                    No purchase records found.
                </td>
            </tr>
        `;

        return;
    }


    purchasesTable.innerHTML = purchases.map(purchase => {

        const purchaseNumber =
            purchase.purchase_number ??
            purchase.reference_number ??
            `PUR-${purchase.id ?? '-'}`;

        const supplier =
            purchase.supplier?.name ??
            purchase.supplier_name ??
            'Unknown Supplier';

        const amount =
            Number(
                purchase.grand_total ??
                purchase.total_amount ??
                purchase.total ??
                0
            );

        const date =
            purchase.created_at
                ? new Date(
                    purchase.created_at
                  ).toLocaleDateString()
                : '-';


        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(purchaseNumber)}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(supplier)}
                </td>

                <td class="px-6 py-4">
                    UGX ${formatNumber(amount)}
                </td>

                <td class="px-6 py-4">
                    ${date}
                </td>

            </tr>
        `;

    }).join('');


    setupPurchasesSearch();
}


function setupPurchasesSearch() {

    const search =
        document.getElementById('purchasesSearch');

    if (!search) {
        return;
    }

    search.addEventListener('input', function () {

        const value =
            this.value.toLowerCase().trim();

        const rows =
            document.querySelectorAll('#purchasesTable tr');

        rows.forEach(row => {

            row.style.display =
                row.textContent
                    .toLowerCase()
                    .includes(value)
                    ? ''
                    : 'none';

        });

    });
}
// =========================
// DELIVERIES
// =========================

const deliveriesTable = document.getElementById('deliveriesTable');

if (deliveriesTable) {
    loadDeliveries();
}

async function loadDeliveries() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch('/api/deliveries', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Deliveries data:', result);

        displayDeliveries(result);

    } catch (error) {

        console.error('Deliveries error:', error);

        deliveriesTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load deliveries.
                </td>
            </tr>
        `;
    }
}


function displayDeliveries(result) {

    const deliveries =
        result.data?.data ??
        result.data ??
        [];

    const totalElement =
        document.getElementById('deliveriesTotal');

    const pendingElement =
        document.getElementById('deliveriesPending');

    const deliveredElement =
        document.getElementById('deliveriesDelivered');

    if (!Array.isArray(deliveries)) {
        console.error('Unexpected deliveries response:', result);
        return;
    }

    const pending = deliveries.filter(delivery =>
        delivery.status === 'pending'
    ).length;

    const delivered = deliveries.filter(delivery =>
        delivery.status === 'delivered'
    ).length;

    totalElement.textContent = deliveries.length;
    pendingElement.textContent = pending;
    deliveredElement.textContent = delivered;


    if (!deliveries.length) {

        deliveriesTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-gray-500">
                    No delivery records found.
                </td>
            </tr>
        `;

        return;
    }


    deliveriesTable.innerHTML = deliveries.map(delivery => {

        const orderNumber =
            delivery.order?.order_number ??
            delivery.order_number ??
            'N/A';

        const address =
            delivery.delivery_address ??
            delivery.address ??
            'N/A';

        const status =
            delivery.status ??
            'N/A';

        const deliveryDate =
            delivery.delivery_date ??
            delivery.delivered_at ??
            delivery.created_at ??
            null;

        const formattedDate = deliveryDate
            ? new Date(deliveryDate).toLocaleDateString()
            : '-';


        let statusBadge;

        if (status === 'delivered') {

            statusBadge = `
                <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                    Delivered
                </span>
            `;

        } else if (status === 'pending') {

            statusBadge = `
                <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                    Pending
                </span>
            `;

        } else {

            statusBadge = `
                <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                    ${escapeHtml(status)}
                </span>
            `;
        }


        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(orderNumber)}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(address)}
                </td>

                <td class="px-6 py-4">
                    ${statusBadge}
                </td>

                <td class="px-6 py-4">
                    ${formattedDate}
                </td>

            </tr>
        `;

    }).join('');


    setupDeliveriesSearch();
}


function setupDeliveriesSearch() {

    const search =
        document.getElementById('deliveriesSearch');

    if (!search) {
        return;
    }

    search.addEventListener('input', function () {

        const value =
            this.value.toLowerCase().trim();

        const rows =
            document.querySelectorAll('#deliveriesTable tr');

        rows.forEach(row => {

            row.style.display =
                row.textContent
                    .toLowerCase()
                    .includes(value)
                    ? ''
                    : 'none';

        });

    });
}
// =========================
// CUSTOMERS
// =========================

const customersTable = document.getElementById('customersTable');

if (customersTable) {
    loadCustomers();
}

async function loadCustomers() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch('/api/customers', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Customers data:', result);

        displayCustomers(result);

    } catch (error) {

        console.error('Customers error:', error);

        customersTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load customers.
                </td>
            </tr>
        `;
    }
}


function displayCustomers(result) {

    const customers =
        result.data?.data ??
        result.data ??
        [];

    const totalElement =
        document.getElementById('customersTotal');

    const activeElement =
        document.getElementById('customersActive');

    const inactiveElement =
        document.getElementById('customersInactive');

    if (!Array.isArray(customers)) {
        console.error('Unexpected customers response:', result);
        return;
    }

    const active = customers.filter(customer =>
        customer.status === true ||
        customer.status === 1 ||
        customer.status === 'active'
    ).length;

    const inactive = customers.length - active;

    totalElement.textContent = customers.length;
    activeElement.textContent = active;
    inactiveElement.textContent = inactive;


    if (!customers.length) {

        customersTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-gray-500">
                    No customer records found.
                </td>
            </tr>
        `;

        return;
    }


    customersTable.innerHTML = customers.map(customer => {

        const name =
            customer.name ??
            'Unknown Customer';

        const phone =
            customer.phone ??
            customer.phone_number ??
            'N/A';

        const email =
            customer.email ??
            'N/A';

        const isActive =
            customer.status === true ||
            customer.status === 1 ||
            customer.status === 'active';

        const statusBadge = isActive
            ? `
                <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                    Active
                </span>
            `
            : `
                <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                    Inactive
                </span>
            `;


        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(name)}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(phone)}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(email)}
                </td>

                <td class="px-6 py-4">
                    ${statusBadge}
                </td>

            </tr>
        `;

    }).join('');


    setupCustomersSearch();
}


function setupCustomersSearch() {

    const search =
        document.getElementById('customersSearch');

    if (!search) {
        return;
    }

    search.addEventListener('input', function () {

        const value =
            this.value.toLowerCase().trim();

        const rows =
            document.querySelectorAll('#customersTable tr');

        rows.forEach(row => {

            row.style.display =
                row.textContent
                    .toLowerCase()
                    .includes(value)
                    ? ''
                    : 'none';

        });

    });
}
// =========================
// SUPPLIERS
// =========================

const suppliersTable = document.getElementById('suppliersTable');

if (suppliersTable) {
    loadSuppliers();
}

async function loadSuppliers() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch('/api/suppliers', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Suppliers data:', result);

        displaySuppliers(result);

    } catch (error) {

        console.error('Suppliers error:', error);

        suppliersTable.innerHTML = `
            <tr>
                <td colspan="5"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load suppliers.
                </td>
            </tr>
        `;
    }
}


function displaySuppliers(result) {

    const suppliers =
        result.data?.data ??
        result.data ??
        [];

    const totalElement =
        document.getElementById('suppliersTotal');

    const activeElement =
        document.getElementById('suppliersActive');

    const inactiveElement =
        document.getElementById('suppliersInactive');

    if (!Array.isArray(suppliers)) {
        console.error('Unexpected suppliers response:', result);
        return;
    }

    const active = suppliers.filter(supplier =>
        supplier.status === true ||
        supplier.status === 1 ||
        supplier.status === 'active'
    ).length;

    const inactive = suppliers.length - active;

    totalElement.textContent = suppliers.length;
    activeElement.textContent = active;
    inactiveElement.textContent = inactive;


    if (!suppliers.length) {

        suppliersTable.innerHTML = `
            <tr>
                <td colspan="5"
                    class="text-center px-6 py-10 text-gray-500">
                    No supplier records found.
                </td>
            </tr>
        `;

        return;
    }


    suppliersTable.innerHTML = suppliers.map(supplier => {

        const name =
            supplier.name ??
            'Unknown Supplier';

        const phone =
            supplier.phone ??
            supplier.phone_number ??
            'N/A';

        const email =
            supplier.email ??
            'N/A';

        const address =
            supplier.address ??
            'N/A';

        const isActive =
            supplier.status === true ||
            supplier.status === 1 ||
            supplier.status === 'active';

        const statusBadge = isActive
            ? `
                <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                    Active
                </span>
            `
            : `
                <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                    Inactive
                </span>
            `;


        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(name)}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(phone)}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(email)}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(address)}
                </td>

                <td class="px-6 py-4">
                    ${statusBadge}
                </td>

            </tr>
        `;

    }).join('');


    setupSuppliersSearch();
}


function setupSuppliersSearch() {

    const search =
        document.getElementById('suppliersSearch');

    if (!search) {
        return;
    }

    search.addEventListener('input', function () {

        const value =
            this.value.toLowerCase().trim();

        const rows =
            document.querySelectorAll('#suppliersTable tr');

        rows.forEach(row => {

            row.style.display =
                row.textContent
                    .toLowerCase()
                    .includes(value)
                    ? ''
                    : 'none';

        });

    });
}
// =========================
// REPORTS
// =========================

const reportsTable = document.getElementById('reportsTable');

if (reportsTable) {
    loadReports();

    const generateButton =
        document.getElementById('generateReport');

    if (generateButton) {
        generateButton.addEventListener('click', loadReports);
    }
}


async function loadReports() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    const from =
        document.getElementById('reportFrom')?.value;

    const to =
        document.getElementById('reportTo')?.value;

    try {

        let url = '/api/reports';

        const params = new URLSearchParams();

        if (from) {
            params.append('from', from);
        }

        if (to) {
            params.append('to', to);
        }

        if (params.toString()) {
            url += `?${params.toString()}`;
        }


        const response = await fetch(url, {

            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }

        });


        if (response.status === 401) {

            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            window.location.href = '/login';

            return;
        }


        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }


        const result = await response.json();

        console.log('Reports data:', result);

        displayReports(result);
        renderMonthlyPerformanceChart(result.monthly_performance || []);
        renderPaymentMethodsChart(result.payment_methods || []);
        renderMonthlyPerformance(result.monthly_performance || []);

    } catch (error) {

        console.error('Reports error:', error);

        reportsTable.innerHTML = `
            <tr>
                <td colspan="2"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load reports.
                </td>
            </tr>
        `;
    }
}


function drawBarChart(canvasId, labels, series) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    const width = Math.max(320, rect.width);
    const height = Math.max(260, rect.height);
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, width, height);

    const padding = { top: 20, right: 20, bottom: 55, left: 75 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;
    const maxValue = Math.max(1, ...series.flatMap(s => s.values.map(Number)));
    const tickCount = 5;

    ctx.font = '12px Instrument Sans, Arial, sans-serif';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#020000';
    ctx.strokeStyle = '#6d1616';

    for (let i = 0; i <= tickCount; i++) {
        const value = (maxValue / tickCount) * i;
        const y = padding.top + chartH - (value / maxValue) * chartH;
        ctx.beginPath(); ctx.moveTo(padding.left, y); ctx.lineTo(width - padding.right, y); ctx.stroke();
        ctx.fillText(`UGX ${formatNumber(value)}`, padding.left - 10, y);
    }

    const groupW = chartW / Math.max(1, labels.length);
    const barW = Math.min(28, (groupW * 0.65) / Math.max(1, series.length));
    series.forEach((s, si) => {
        s.values.forEach((raw, i) => {
            const value = Number(raw || 0);
            const barH = (value / maxValue) * chartH;
            const x = padding.left + i * groupW + groupW / 2 - ((series.length * barW) / 2) + si * barW;
            const y = padding.top + chartH - barH;
            ctx.fillStyle = s.fill;
            ctx.fillRect(x, y, Math.max(2, barW - 4), barH);
        });
    });

    ctx.fillStyle = '#0341a5';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    labels.forEach((label, i) => {
        const x = padding.left + i * groupW + groupW / 2;
        ctx.fillText(label, x, padding.top + chartH + 12);
    });

    ctx.textAlign = 'left';
    series.forEach((s, i) => {
        const x = padding.left + i * 145;
        const y = height - 18;
        ctx.fillStyle = s.fill; ctx.fillRect(x, y - 9, 10, 10);
        ctx.fillStyle = '#d12047'; ctx.fillText(s.label, x + 16, y);
    });
}

function drawPieChart(canvasId, data) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    const width = Math.max(240, rect.width);
    const height = Math.max(240, rect.height);
    canvas.width = width * dpr; canvas.height = height * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, width, height);

    const total = data.reduce((sum, item) => sum + Number(item.total || 0), 0);
    if (!total) {
        ctx.fillStyle = '#0050da'; ctx.font = '14px Instrument Sans, Arial, sans-serif'; ctx.textAlign = 'center';
        ctx.fillText('No completed payments in this period', width / 2, height / 2);
        return;
    }

    const fills = ['#e00e31', '#7a4c58', '#184394', '#bd9e15', '#6b7280'];
    const cx = width / 2, cy = height / 2, radius = Math.min(width, height) * 0.36;
    let start = -Math.PI / 2;
    data.forEach((item, i) => {
        const slice = (Number(item.total || 0) / total) * Math.PI * 2;
        ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, radius, start, start + slice); ctx.closePath();
        ctx.fillStyle = fills[i % fills.length]; ctx.fill();
        start += slice;
    });
    ctx.beginPath(); ctx.arc(cx, cy, radius * 0.55, 0, Math.PI * 2); ctx.fillStyle = '#fff'; ctx.fill();
    ctx.fillStyle = '#3768d1'; ctx.font = 'bold 14px Instrument Sans, Arial, sans-serif'; ctx.textAlign = 'center';
    ctx.fillText('Total', cx, cy - 7);
    ctx.font = '12px Instrument Sans, Arial, sans-serif'; ctx.fillText(`UGX ${formatNumber(total)}`, cx, cy + 12);
}

function renderMonthlyPerformanceChart(rows) {
    const labels = rows.map(r => { const d = new Date(`${r.month}-01T00:00:00`); return d.toLocaleDateString('en-US', { month: 'short', year: '2-digit' }); });
    drawBarChart('monthlyPerformanceChart', labels, [
        { label: 'Sales', values: rows.map(r => r.sales), fill: '#d40202' },
        { label: 'Purchases', values: rows.map(r => r.purchases), fill: '#311468' }
    ]);
}

function renderPaymentMethodsChart(rows) {
    drawPieChart('paymentMethodsChart', rows);
    const legend = document.getElementById('paymentLegend');
    if (!legend) return;
    const total = rows.reduce((sum, r) => sum + Number(r.total || 0), 0);
    legend.innerHTML = rows.length ? rows.map((r, i) => {
        const pct = total ? ((Number(r.total || 0) / total) * 100).toFixed(1) : '0.0';
        const name = String(r.payment_method || 'Unknown').replace(/_/g, ' ');
        return `<div class="flex items-center justify-between text-sm"><span class="capitalize">${name}</span><strong>${pct}%</strong></div>`;
    }).join('') : '<p class="text-sm text-gray-500">No completed payments found.</p>';
}

function renderMonthlyPerformance(rows) {
    const container = document.getElementById('monthlyPerformance');
    if (!container) return;
    if (!rows.length) {
        container.innerHTML = '<div class="px-6 py-8 text-gray-500">No monthly data is available for the selected period.</div>';
        return;
    }
    container.innerHTML = rows.map(row => {
        const date = new Date(`${row.month}-01T00:00:00`);
        const monthName = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        const change = row.sales_change_percent;
        const changeText = change === null || change === undefined ? 'No previous month comparison' : `${change >= 0 ? '+' : ''}${change}% vs previous month`;
        const badge = row.performance === 'Improving' ? 'bg-green-100 text-green-700' : row.performance === 'Needs attention' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700';
        return `<div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1"><div class="flex items-center gap-3 mb-1"><h4 class="font-semibold text-gray-900">${monthName}</h4><span class="text-xs font-semibold px-2 py-1 rounded-full ${badge}">${row.performance}</span></div><p class="text-sm text-gray-600">${row.description}</p></div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm min-w-[280px]"><div><span class="text-gray-500 block">Sales</span><strong>UGX ${formatNumber(row.sales)}</strong></div><div><span class="text-gray-500 block">Orders</span><strong>${Number(row.orders || 0)}</strong></div><div><span class="text-gray-500 block">Change</span><strong>${changeText}</strong></div></div>
        </div>`;
    }).join('');
}


function displayReports(result) {

    const summary =
        result.summary ?? {};

    const totalSales =
        Number(summary.total_sales ?? 0);

    const totalPayments =
        Number(summary.total_payments ?? 0);

    const totalPurchases =
        Number(summary.total_purchases ?? 0);

    const totalOrders =
        Number(summary.total_orders ?? 0);


    document.getElementById('reportSales').textContent =
        `UGX ${formatNumber(totalSales)}`;

    document.getElementById('reportPayments').textContent =
        `UGX ${formatNumber(totalPayments)}`;

    document.getElementById('reportPurchases').textContent =
        `UGX ${formatNumber(totalPurchases)}`;

    document.getElementById('reportOrders').textContent =
        totalOrders;


    reportsTable.innerHTML = `

        <tr class="border-b border-gray-100">

            <td class="px-6 py-4 font-medium">
                Total Sales
            </td>

            <td class="px-6 py-4">
                UGX ${formatNumber(totalSales)}
            </td>

        </tr>


        <tr class="border-b border-gray-100">

            <td class="px-6 py-4 font-medium">
                Total Payments
            </td>

            <td class="px-6 py-4">
                UGX ${formatNumber(totalPayments)}
            </td>

        </tr>


        <tr class="border-b border-gray-100">

            <td class="px-6 py-4 font-medium">
                Total Purchases
            </td>

            <td class="px-6 py-4">
                UGX ${formatNumber(totalPurchases)}
            </td>

        </tr>


        <tr class="border-b border-gray-100">

            <td class="px-6 py-4 font-medium">
                Total Orders
            </td>

            <td class="px-6 py-4">
                ${totalOrders}
            </td>

        </tr>

    `;
}
// =========================
// USERS
// =========================

const usersTable = document.getElementById('usersTable');

if (usersTable) {
    loadUsers();
}

async function loadUsers() {

    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {

        const response = await fetch('/api/users', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        console.log('Users data:', result);

        displayUsers(result);

    } catch (error) {

        console.error('Users error:', error);

        usersTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-red-600">
                    Failed to load users.
                </td>
            </tr>
        `;
    }
}


function displayUsers(result) {

    const users =
        result.data?.data ??
        result.data ??
        [];

    const totalElement =
        document.getElementById('usersTotal');

    const adminsElement =
        document.getElementById('usersAdmins');

    const othersElement =
        document.getElementById('usersOthers');

    if (!Array.isArray(users)) {
        console.error('Unexpected users response:', result);
        return;
    }

    const admins = users.filter(user => {

        const roles = user.roles ?? [];

        if (Array.isArray(roles)) {
            return roles.some(role =>
                (role.name ?? role)
                    .toString()
                    .toLowerCase() === 'admin'
            );
        }

        return false;

    }).length;

    const others = users.length - admins;

    totalElement.textContent = users.length;
    adminsElement.textContent = admins;
    othersElement.textContent = others;


    if (!users.length) {

        usersTable.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center px-6 py-10 text-gray-500">
                    No users found.
                </td>
            </tr>
        `;

        return;
    }


    usersTable.innerHTML = users.map(user => {

        const name =
            user.name ??
            'Unknown User';

        const email =
            user.email ??
            'N/A';

        const roles = user.roles ?? [];

        let roleText = 'User';

        if (Array.isArray(roles) && roles.length) {

            roleText = roles.map(role =>
                role.name ?? role
            ).join(', ');

        }

        const created =
            user.created_at
                ? new Date(
                    user.created_at
                  ).toLocaleDateString()
                : '-';


        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">

                <td class="px-6 py-4 font-medium">
                    ${escapeHtml(name)}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(email)}
                </td>

                <td class="px-6 py-4">
                    ${escapeHtml(roleText)}
                </td>

                <td class="px-6 py-4">
                    ${created}
                </td>

            </tr>
        `;

    }).join('');


    setupUsersSearch();
}


function setupUsersSearch() {

    const search =
        document.getElementById('usersSearch');

    if (!search) {
        return;
    }

    search.addEventListener('input', function () {

        const value =
            this.value.toLowerCase().trim();

        const rows =
            document.querySelectorAll('#usersTable tr');

        rows.forEach(row => {

            row.style.display =
                row.textContent
                    .toLowerCase()
                    .includes(value)
                    ? ''
                    : 'none';

        });

    });
}
});
// ============================================================================
// GLOBAL CREATE ACTIONS
// ============================================================================
document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('auth_token');
    if (!token) return;
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const role = Array.isArray(user.roles) ? user.roles[0] : user.role;
    const path = window.location.pathname;
    const actionMap = {
        '/orders': { label: '+ Add Order', type: 'order', color: 'blue', roles: ['Admin','Manager','Sales Staff'] },
        '/sales': { label: '+ Record Sale', type: 'sale', color: 'green', roles: ['Admin','Manager','Sales Staff'] },
        '/payments': { label: '+ Add Payment', type: 'payment', color: 'purple', roles: ['Admin','Manager','Sales Staff'] },
        '/purchases': { label: '+ New Purchase', type: 'purchase', color: 'orange', roles: ['Admin','Manager','Procurement Staff'] },
        '/customers': { label: '+ Add Customer', type: 'customer', color: 'blue', roles: ['Admin','Manager','Sales Staff'] },
        '/suppliers': { label: '+ Add Supplier', type: 'supplier', color: 'orange', roles: ['Admin','Manager','Procurement Staff'] },
        '/deliveries': { label: '+ New Delivery', type: 'delivery', color: 'red', roles: ['Admin','Manager'] },
        '/inventory': { label: '+ Update Stock', type: 'inventory', color: 'green', roles: ['Admin','Manager','Inventory Staff'] },
        '/users': { label: '+ Add User', type: 'user', color: 'purple', roles: ['Admin'] }
    };
    const action = actionMap[path];
    if (!action || !action.roles.includes(role)) return;
    const header = document.querySelector('main > header');
    if (!header) return;
    let inner = header.firstElementChild;
    if (!inner || !inner.classList.contains('flex')) {
        const nodes = Array.from(header.childNodes);
        inner = document.createElement('div');
        inner.className = 'flex flex-col md:flex-row md:items-center md:justify-between gap-3';
        nodes.forEach(n => inner.appendChild(n));
        header.appendChild(inner);
    }
    if (header.querySelector('.hl-page-action')) return;
    const button = document.createElement('button');
    button.type='button'; button.className=`hl-page-action ${action.color}`; button.textContent=action.label;
    button.addEventListener('click',()=>openGlobalCreateModal(action.type));
    inner.appendChild(button);

    const configs={
        order:['Create New Order','Capture a customer order and its line items.','Create Order'],
        sale:['Record New Sale','Complete a sale and update stock.','Record Sale'],
        payment:['Add Payment','Record a payment against an existing sale.','Save Payment'],
        purchase:['Create New Purchase','Record supplier stock received into the bakery.','Create Purchase'],
        customer:['Add Customer','Create a new bakery customer.','Save Customer'],
        supplier:['Add Supplier','Create a new supplier record.','Save Supplier'],
        delivery:['Create Delivery','Schedule and assign a customer delivery.','Create Delivery'],
        inventory:['Update Inventory','Record stock in, stock out or an adjustment.','Update Stock'],
        user:['Add System User','Create a staff account and assign a role.','Create User']
    };
    const esc=v=>{const d=document.createElement('div');d.textContent=v??'';return d.innerHTML;};
    const headers=(json=true)=>{const h={Accept:'application/json',Authorization:`Bearer ${token}`};if(json)h['Content-Type']='application/json';return h;};
    async function get(url){const r=await fetch(url,{headers:headers(false)});const j=await r.json().catch(()=>({}));if(r.status===401){localStorage.removeItem('auth_token');localStorage.removeItem('user');location.href='/login';throw new Error('Session expired.');}if(!r.ok)throw new Error(j.message||'Unable to load data.');return j;}
    async function list(url){const j=await get(url);return Array.isArray(j)?j:(Array.isArray(j.data?.data)?j.data.data:Array.isArray(j.data)?j.data:[]);}
    const opts=(items,label=x=>x.name)=>`<option value="">Select...</option>`+items.map(x=>`<option value="${x.id}">${esc(label(x))}</option>`).join('');

    async function openGlobalCreateModal(type){
        const cfg=configs[type];let form='';
        try{
            if(['order','sale','purchase'].includes(type)){
                const [products,customers]=await Promise.all([list('/api/products'),list('/api/customers')]);
                const customer=type==='purchase'?`<div><label>Supplier</label><select name="supplier_id" required data-supplier-select><option value="">Loading suppliers...</option></select></div><div><label>Purchase Date</label><input name="purchase_date" type="date" value="${new Date().toISOString().slice(0,10)}" required></div>`:`<div><label>Customer</label><select name="customer_id">${opts(customers)}</select><p class="text-xs text-gray-500 mt-1">Leave blank for a walk-in customer.</p></div>`;
                const po=opts(products,x=>`${x.name} — UGX ${Number(x.selling_price||0).toLocaleString('en-UG')}`);
                form=`<div class="space-y-4">${customer}<div><label>Items</label><div id="globalItemRows"><div class="hl-item-row grid grid-cols-1 md:grid-cols-3 gap-3"><div><label>Product</label><select name="product_id" required>${po}</select></div><div><label>Quantity</label><input name="quantity" type="number" min="1" value="1" required></div>${type==='purchase'?'<div><label>Unit Cost</label><input name="unit_cost" type="number" min="0" step="0.01" required></div>':''}</div></div><button type="button" id="addItemRow" class="text-sm font-bold text-blue-600 hover:underline">+ Add another item</button></div>${type!=='purchase'?`<div class="grid grid-cols-2 gap-3"><div><label>Discount</label><input name="discount" type="number" min="0" step="0.01" value="0"></div><div><label>Tax</label><input name="tax" type="number" min="0" step="0.01" value="0"></div></div><div><label>Notes</label><textarea name="notes" rows="3"></textarea></div>`:'<div><label>Remarks</label><textarea name="remarks" rows="3"></textarea></div>'}</div>`;
            } else if(type==='payment'){
                const sales=await list('/api/sales');form=`<div class="space-y-4"><div><label>Sale</label><select name="sale_id" required>${opts(sales,x=>`${x.sale_number} — UGX ${Number(x.grand_total||0).toLocaleString('en-UG')}`)}</select></div><div class="grid grid-cols-2 gap-3"><div><label>Amount</label><input name="amount" type="number" min="0.01" step="0.01" required></div><div><label>Payment Method</label><select name="payment_method" required><option value="cash">Cash</option><option value="mobile_money">Mobile Money</option><option value="card">Card</option><option value="bank_transfer">Bank Transfer</option></select></div></div><div><label>Transaction Reference</label><input name="transaction_reference"></div><div><label>Status</label><select name="status"><option value="completed">Completed</option><option value="pending">Pending</option><option value="failed">Failed</option></select></div><div><label>Remarks</label><textarea name="remarks" rows="3"></textarea></div></div>`;
            } else if(type==='delivery'){
                const [sales,customers,staff]=await Promise.all([list('/api/sales'),list('/api/customers'),list('/api/deliveries/staff')]);form=`<div class="space-y-4"><div class="grid grid-cols-2 gap-3"><div><label>Sale</label><select name="sale_id" required>${opts(sales,x=>x.sale_number)}</select></div><div><label>Customer</label><select name="customer_id">${opts(customers)}</select></div></div><div class="grid grid-cols-2 gap-3"><div><label>Recipient Name</label><input name="recipient_name" required></div><div><label>Recipient Phone</label><input name="recipient_phone" required></div></div><div><label>Delivery Address</label><textarea name="delivery_address" rows="2" required></textarea></div><div class="grid grid-cols-2 gap-3"><div><label>Delivery Fee</label><input name="delivery_fee" type="number" min="0" step="0.01" value="0"></div><div><label>Assign Delivery Staff</label><select name="assigned_to">${opts(staff)}</select></div></div><div><label>Scheduled At</label><input name="scheduled_at" type="datetime-local"></div><div><label>Notes</label><textarea name="notes" rows="3"></textarea></div></div>`;
            } else if(type==='inventory'){const products=await list('/api/products');form=`<div class="space-y-4"><div><label>Product</label><select name="product_id" required>${opts(products,x=>`${x.name} — Stock ${x.stock_quantity}`)}</select></div><div class="grid grid-cols-2 gap-3"><div><label>Action</label><select name="type"><option value="stock_in">Stock In</option><option value="stock_out">Stock Out</option><option value="adjustment">Adjustment</option></select></div><div><label>Quantity</label><input name="quantity" type="number" min="1" required></div></div><div><label>Remarks</label><textarea name="remarks" rows="3"></textarea></div></div>`;
            } else if(type==='user') form=`<div class="space-y-4"><div><label>Full Name</label><input name="name" required></div><div><label>Email</label><input name="email" type="email" required></div><div><label>Password</label><input name="password" type="password" minlength="8" required></div><div><label>Role</label><select name="role" required><option value="">Select role...</option><option>Admin</option><option>Manager</option><option>Sales Staff</option><option>Inventory Staff</option><option>Procurement Staff</option><option>Delivery Staff</option></select></div></div>`;
            else if(type==='customer') form=`<div class="space-y-4"><div><label>Full Name</label><input name="name" required></div><div class="grid grid-cols-2 gap-3"><div><label>Phone</label><input name="phone" required></div><div><label>Email</label><input name="email" type="email"></div></div><div><label>Address</label><textarea name="address" rows="3"></textarea></div><div><label>Status</label><select name="status"><option value="1">Active</option><option value="0">Inactive</option></select></div></div>`;
            else if(type==='supplier') form=`<div class="space-y-4"><div><label>Supplier Name</label><input name="name" required></div><div class="grid grid-cols-2 gap-3"><div><label>Contact Person</label><input name="contact_person"></div><div><label>Phone</label><input name="phone" required></div></div><div><label>Email</label><input name="email" type="email"></div><div><label>Address</label><textarea name="address" rows="3"></textarea></div><div><label>Status</label><select name="status"><option value="1">Active</option><option value="0">Inactive</option></select></div></div>`;
        }catch(e){alert(e.message);return;}
        const modal=document.createElement('div');modal.id='globalCreateModal';modal.className='hl-modal-backdrop';modal.innerHTML=`<div class="hl-modal" role="dialog" aria-modal="true"><div class="hl-modal-header"><div class="flex items-center justify-between gap-4"><div><h3 class="text-xl font-bold">${cfg[0]}</h3><p class="text-white/70 text-sm mt-1">${cfg[1]}</p></div><button type="button" data-close class="text-white/70 hover:text-white text-2xl">×</button></div></div><form id="globalCreateForm" class="hl-form">${form}<div id="globalCreateError" class="hl-form-error"></div><div class="hl-form-footer"><button type="button" data-close class="hl-secondary">Cancel</button><button type="submit" class="hl-primary">${cfg[2]}</button></div></form></div>`;document.body.appendChild(modal);
        modal.querySelectorAll('[data-close]').forEach(b=>b.addEventListener('click',()=>modal.remove()));modal.addEventListener('click',e=>{if(e.target===modal)modal.remove()});
        const supplier=modal.querySelector('[data-supplier-select]');if(supplier)list('/api/suppliers').then(x=>supplier.innerHTML=opts(x)).catch(e=>supplier.innerHTML='<option value="">Unable to load suppliers</option>');
        const add=modal.querySelector('#addItemRow');if(add)add.addEventListener('click',()=>{const c=modal.querySelector('#globalItemRows'),first=c.querySelector('.hl-item-row'),clone=first.cloneNode(true);clone.querySelectorAll('input').forEach(i=>i.value=i.name==='quantity'?'1':'');clone.querySelectorAll('select').forEach(s=>s.selectedIndex=0);c.appendChild(clone)});
        modal.querySelector('#globalCreateForm').addEventListener('submit',e=>submitGlobalForm(e,type,modal,cfg[2]));
    }

    async function submitGlobalForm(event,type,modal,submitLabel){
        event.preventDefault();
        const form=event.currentTarget, error=modal.querySelector('#globalCreateError'), submit=form.querySelector('button[type="submit"]');
        error.style.display='none'; submit.disabled=true; submit.textContent='Saving...';
        const fd=new FormData(form), get=n=>fd.get(n), number=v=>(v===''||v===null?null:Number(v));
        let payload, endpoint;
        try{
            if(type==='customer'){endpoint='customers';payload={name:get('name'),phone:get('phone'),email:get('email')||null,address:get('address')||null,status:Boolean(Number(get('status')))};}
            else if(type==='supplier'){endpoint='suppliers';payload={name:get('name'),contact_person:get('contact_person')||null,phone:get('phone'),email:get('email')||null,address:get('address')||null,status:Boolean(Number(get('status')))};}
            else if(type==='user'){endpoint='users';payload={name:get('name'),email:get('email'),password:get('password'),role:get('role')};}
            else if(type==='inventory'){endpoint='inventory';payload={product_id:number(get('product_id')),type:get('type'),quantity:number(get('quantity')),remarks:get('remarks')||null};}
            else if(type==='payment'){endpoint='payments';payload={sale_id:number(get('sale_id')),amount:number(get('amount')),payment_method:get('payment_method'),status:get('status'),transaction_reference:get('transaction_reference')||null,remarks:get('remarks')||null};}
            else if(type==='delivery'){endpoint='deliveries';payload={sale_id:number(get('sale_id')),customer_id:number(get('customer_id')),delivery_address:get('delivery_address'),recipient_name:get('recipient_name'),recipient_phone:get('recipient_phone'),delivery_fee:number(get('delivery_fee')||0),status:get('assigned_to')?'assigned':'pending',assigned_to:number(get('assigned_to')),scheduled_at:get('scheduled_at')||null,notes:get('notes')||null};}
            else {const items=[...form.querySelectorAll('.hl-item-row')].map(row=>({product_id:Number(row.querySelector('[name="product_id"]').value),quantity:Number(row.querySelector('[name="quantity"]').value),...(type==='purchase'?{unit_cost:Number(row.querySelector('[name="unit_cost"]').value)}:{})}));endpoint=type==='purchase'?'purchases':type==='sale'?'sales':'orders';if(type==='purchase')payload={supplier_id:number(get('supplier_id')),purchase_date:get('purchase_date'),remarks:get('remarks')||null,items};else payload={customer_id:number(get('customer_id')),discount:number(get('discount')||0),tax:number(get('tax')||0),items,...(type==='order'?{status:'pending',notes:get('notes')||null}:{})};}
            const response=await fetch(`/api/${endpoint}`,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json',Authorization:`Bearer ${token}`},body:JSON.stringify(payload)});
            const result=await response.json().catch(()=>({}));
            if(!response.ok){const validation=result.errors?Object.values(result.errors).flat().join(' '):'';throw new Error(validation||result.message||'Could not save this record.');}
            modal.remove();alert(result.message||'Record saved successfully.');refreshCurrentPage();
        }catch(e){error.textContent=e.message;error.style.display='block';submit.disabled=false;submit.textContent=submitLabel;}
    }

    function refreshCurrentPage(){
        const map={'/orders':'loadOrders','/sales':'loadSales','/payments':'loadPayments','/purchases':'loadPurchases','/customers':'loadCustomers','/suppliers':'loadSuppliers','/deliveries':'loadDeliveries','/inventory':'loadInventory','/users':'loadUsers'};
        const fn=window[map[path]]; if(typeof fn==='function')fn(); else location.reload();
    }
});
