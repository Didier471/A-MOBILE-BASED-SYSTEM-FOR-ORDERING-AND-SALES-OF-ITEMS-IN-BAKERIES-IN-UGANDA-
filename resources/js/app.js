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


    async function loadProducts() {

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

            console.log('Products data:', result);

            displayProducts(result);

        } catch (error) {

            console.error('Products error:', error);

            productsTable.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center px-6 py-10 text-red-600">
                        Failed to load products.
                    </td>
                </tr>
            `;
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
// ADD PRODUCT
// =========================

const addProductBtn = document.getElementById('addProductBtn');
const productModal = document.getElementById('productModal');
const closeProductModal = document.getElementById('closeProductModal');
const cancelProductBtn = document.getElementById('cancelProductBtn');
const productForm = document.getElementById('productForm');

if (addProductBtn) {

    addProductBtn.addEventListener('click', () => {
        productModal.classList.remove('hidden');
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

    window.editProduct = function (id) {

        alert(`Edit product ${id} — product form coming next.`);

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

        const response = await fetch('/api/inventory', {

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

    inventory.forEach(item => {

        const quantity = Number(
            item.stock_quantity ??
            item.quantity ??
            0
        );

        const reorderLevel = Number(
            item.reorder_level ?? 0
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


    inventoryTable.innerHTML = inventory.map(item => {

        const product = item.product ?? item;

        const quantity = Number(
            item.stock_quantity ??
            item.quantity ??
            product.stock_quantity ??
            0
        );

        const reorderLevel = Number(
            item.reorder_level ??
            product.reorder_level ??
            0
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
                    ${escapeHtml(
                        item.product?.name ??
                        item.name ??
                        'Unknown Product'
                    )}
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
});