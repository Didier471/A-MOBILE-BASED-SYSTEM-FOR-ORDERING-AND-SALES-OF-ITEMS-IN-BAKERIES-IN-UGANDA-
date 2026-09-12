# Dashboard and UI Enhancement Update

## Dashboard
- Reworked the dashboard presentation for all six roles: Admin, Manager, Sales Staff, Inventory Staff, Procurement Staff and Delivery Staff.
- Admin now has KPI cards, estimated profit, sales trend, payment mix, top products, staff overview, top customers, alerts, delivery performance, recent orders and recent payments.
- Manager has business-performance KPIs, sales trend, order/delivery status, top products, low-stock items and alerts.
- Sales Staff has today's sales, personal sales performance, order queue, recent orders and top products.
- Inventory Staff has product count, stock units, estimated stock value, low-stock and out-of-stock lists.
- Procurement Staff has supplier/purchase KPIs, top suppliers, procurement needs and recent purchases.
- Delivery Staff has delivery status KPIs, personal assignments, personal performance and delivery queues.
- Dashboard date filters are available for range-based dashboard metrics.

## Create actions
Role-aware create buttons were added to operational pages for:
- Orders
- Sales
- Payments
- Purchases
- Customers
- Suppliers
- Deliveries (Admin/Manager)
- Inventory stock updates
- Users (Admin)

Orders, sales and purchases support multiple line items in the create form.

## UI refresh
- Added a bakery-themed dashboard/page background.
- Added a dark branded sidebar with highlighted active navigation.
- Added gradient/tinted summary cards and stronger table styling.
- Added reusable modal styling for create forms.
- Product creation now uses a category dropdown instead of requiring a category ID.

## Cache freshness
Dashboard cache invalidation now covers operational model changes so new records are reflected sooner on dashboard views.

## Validation
- PHP syntax checks passed for changed PHP/Blade files.
- JavaScript syntax check passed.
- Dashboard and delivery routes were verified with `php artisan route:list`.
- Local Vite build could not be completed in the Linux validation environment because the uploaded Windows `node_modules` lacks the Linux Rolldown native binding. Running `npm install` on the development machine will restore the correct native dependency.
