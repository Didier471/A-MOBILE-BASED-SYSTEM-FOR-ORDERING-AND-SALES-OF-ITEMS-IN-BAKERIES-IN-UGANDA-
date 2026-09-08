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