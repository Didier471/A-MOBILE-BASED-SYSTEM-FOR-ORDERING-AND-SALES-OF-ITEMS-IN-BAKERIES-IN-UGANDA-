@extends('layouts.app')

@section('content')
<header class="px-8 py-5">
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center font-black">HL</div>
                <div>
                    <h2 class="text-2xl font-bold">Dashboard</h2>
                    <p id="dashboardSubtitle" class="text-sm">Loading your bakery overview...</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">From</label>
                <input id="dashboardFrom" type="date" class="border border-gray-300 rounded-lg px-3 py-2 bg-white" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">To</label>
                <input id="dashboardTo" type="date" class="border border-gray-300 rounded-lg px-3 py-2 bg-white" />
            </div>
            <button id="dashboardRefresh" class="hl-page-action red">Refresh</button>
        </div>
    </div>
</header>

<section class="p-8 space-y-6">
    <div id="dashboardWelcome" class="rounded-2xl p-6 text-white bg-gradient-to-r from-slate-900 via-slate-800 to-red-700 shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-red-200 text-sm font-semibold uppercase tracking-wider">Hot Loaf Control Center</p>
                <h1 id="welcomeTitle" class="text-2xl font-bold mt-1">Loading...</h1>
                <p id="welcomeText" class="text-white/75 mt-1">Preparing your bakery performance overview.</p>
            </div>
            <div class="text-right">
                <p id="userName" class="font-bold text-lg">Loading...</p>
                <p id="userRole" class="text-white/70 text-sm">Loading...</p>
            </div>
        </div>
    </div>

    <div id="summaryCards" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4"></div>

    <div id="dashboardMain" class="space-y-6"></div>

    <div id="dashboardError" class="hidden rounded-xl border border-red-200 bg-red-50 text-red-700 p-5"></div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const token = localStorage.getItem('auth_token');
    if (!token) { window.location.href = '/login'; return; }

    const summaryCards = document.getElementById('summaryCards');
    const main = document.getElementById('dashboardMain');
    const errorBox = document.getElementById('dashboardError');
    const fromInput = document.getElementById('dashboardFrom');
    const toInput = document.getElementById('dashboardTo');

    const today = new Date();
    const iso = today.toISOString().slice(0,10);
    fromInput.value = iso;
    toInput.value = iso;

    const money = value => 'UGX ' + Number(value || 0).toLocaleString('en-UG', { maximumFractionDigits: 0 });
    const num = value => Number(value || 0).toLocaleString('en-UG', { maximumFractionDigits: 1 });
    const esc = value => { const d = document.createElement('div'); d.textContent = value ?? ''; return d.innerHTML; };

    function card(title, value, detail, accent='blue', icon='●') {
        const accents = {
            blue: 'from-blue-50 to-white text-blue-700',
            green: 'from-green-50 to-white text-green-700',
            orange: 'from-orange-50 to-white text-orange-700',
            red: 'from-red-50 to-white text-red-700',
            purple: 'from-purple-50 to-white text-purple-700',
            yellow: 'from-yellow-50 to-white text-yellow-700'
        };
        return `<div class="rounded-2xl border border-gray-200 bg-gradient-to-br ${accents[accent] || accents.blue} p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3"><div><p class="text-xs uppercase tracking-wide text-gray-500 font-bold">${esc(title)}</p><p class="text-2xl font-black text-gray-900 mt-2">${value}</p><p class="text-xs text-gray-500 mt-1">${esc(detail)}</p></div><div class="w-10 h-10 rounded-xl bg-white/80 flex items-center justify-center font-black">${icon}</div></div>
        </div>`;
    }

    function panel(title, subtitle, body, extra='') {
        return `<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden ${extra}"><div class="px-5 py-4 border-b border-gray-100"><h3 class="font-bold text-gray-900">${esc(title)}</h3>${subtitle ? `<p class="text-xs text-gray-500 mt-1">${esc(subtitle)}</p>` : ''}</div><div class="p-5">${body}</div></div>`;
    }

    function rows(items, mapper, empty='Nothing to show yet.') {
        if (!Array.isArray(items) || !items.length) return `<div class="text-sm text-gray-500 py-5 text-center">${esc(empty)}</div>`;
        return `<div class="space-y-2">${items.map(mapper).join('')}</div>`;
    }

    function statusPill(status) {
        const s = String(status || '').replaceAll('_',' ');
        const cls = {pending:'bg-yellow-100 text-yellow-700', processing:'bg-blue-100 text-blue-700', confirmed:'bg-indigo-100 text-indigo-700', ready:'bg-purple-100 text-purple-700', assigned:'bg-blue-100 text-blue-700', out_for_delivery:'bg-orange-100 text-orange-700', delivered:'bg-green-100 text-green-700', completed:'bg-green-100 text-green-700', cancelled:'bg-red-100 text-red-700'}[status] || 'bg-gray-100 text-gray-700';
        return `<span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize ${cls}">${esc(s)}</span>`;
    }

    function drawBar(canvasId, labels, datasets) {
        const canvas = document.getElementById(canvasId); if (!canvas) return;
        const rect = canvas.getBoundingClientRect(); const dpr = window.devicePixelRatio || 1;
        const width = Math.max(320, rect.width || 600), height = 260;
        canvas.width = width*dpr; canvas.height = height*dpr; canvas.style.height = height+'px';
        const ctx = canvas.getContext('2d'); ctx.scale(dpr,dpr); ctx.clearRect(0,0,width,height);
        const pad={top:18,right:20,bottom:48,left:65}; const w=width-pad.left-pad.right, h=height-pad.top-pad.bottom;
        const max=Math.max(1,...datasets.flatMap(d=>d.values.map(v=>Number(v)||0)))*1.15;
        ctx.strokeStyle='#e5e7eb'; ctx.fillStyle='#6b7280'; ctx.font='11px Instrument Sans, Arial'; ctx.textAlign='right'; ctx.textBaseline='middle';
        for(let i=0;i<=5;i++){const y=pad.top+h-(h*i/5), val=max*i/5; ctx.beginPath();ctx.moveTo(pad.left,y);ctx.lineTo(width-pad.right,y);ctx.stroke();ctx.fillText(money(val),pad.left-8,y);}
        const groupW=w/Math.max(1,labels.length), barW=Math.min(42,groupW/(datasets.length+1));
        labels.forEach((label,i)=>{datasets.forEach((ds,j)=>{const v=Number(ds.values[i]||0), bh=(v/max)*h, x=pad.left+i*groupW+groupW/2-(datasets.length*barW)/2+j*barW, y=pad.top+h-bh;ctx.fillStyle=ds.fill;ctx.fillRect(x,y,Math.max(3,barW-5),bh);});ctx.fillStyle='#475467';ctx.textAlign='center';ctx.textBaseline='top';ctx.fillText(label,pad.left+i*groupW+groupW/2,pad.top+h+10);});
        datasets.forEach((ds,i)=>{const x=pad.left+i*145;const y=height-16;ctx.fillStyle=ds.fill;ctx.fillRect(x,y-8,10,10);ctx.fillStyle='#475467';ctx.textAlign='left';ctx.fillText(ds.label,x+16,y);});
    }

    function drawDonut(canvasId, data) {
        const canvas=document.getElementById(canvasId); if(!canvas)return; const rect=canvas.getBoundingClientRect(); const dpr=window.devicePixelRatio||1; const width=Math.max(280,rect.width||350),height=270;canvas.width=width*dpr;canvas.height=height*dpr;canvas.style.height=height+'px';const ctx=canvas.getContext('2d');ctx.scale(dpr,dpr);ctx.clearRect(0,0,width,height);
        const total=data.reduce((a,x)=>a+Number(x.total||0),0); if(!total){ctx.fillStyle='#667085';ctx.font='14px Instrument Sans, Arial';ctx.textAlign='center';ctx.fillText('No completed payments',width/2,height/2);return;}
        const colors=['#16a34a','#2563eb','#7c3aed','#f97316','#dc2626']; const cx=width/2,cy=height/2,r=Math.min(width,height)*.34;let start=-Math.PI/2;
        data.forEach((item,i)=>{const slice=(Number(item.total||0)/total)*Math.PI*2;ctx.beginPath();ctx.moveTo(cx,cy);ctx.arc(cx,cy,r,start,start+slice);ctx.closePath();ctx.fillStyle=colors[i%colors.length];ctx.fill();start+=slice;});ctx.beginPath();ctx.arc(cx,cy,r*.56,0,Math.PI*2);ctx.fillStyle='#fff';ctx.fill();ctx.fillStyle='#172033';ctx.font='bold 14px Instrument Sans, Arial';ctx.textAlign='center';ctx.fillText('Total',cx,cy-6);ctx.font='12px Instrument Sans, Arial';ctx.fillText(money(total),cx,cy+14);
    }

    function renderAdmin(data) {
        const s=data.summary||{}, sales=data.sales||{}, pay=data.payments||{}, inv=data.inventory||{}, ord=data.orders||{}, del=data.deliveries||{}, profit=data.profit||{};
        summaryCards.innerHTML = [
            card('Sales', money(sales.period_amount), `${num(sales.period_count)} sales in selected period`, 'blue','S'),
            card('Estimated Profit', money(profit.estimated_profit), `${num(profit.margin_pct)}% estimated margin`, 'green','P'),
            card('Orders', num(s.total_orders), `${num(ord.pending)} pending`, 'orange','O'),
            card('Completed Payments', money(pay.period_received), 'Payments received in period', 'purple','₵'),
            card('Products', num(s.total_products), 'Products in catalogue', 'blue','P'),
            card('Customers', num(s.total_customers), 'Registered customers', 'green','C'),
            card('Low Stock', num(inv.low_stock), `${num(inv.out_of_stock)} out of stock`, 'yellow','!'),
            card('Deliveries', num(s.total_deliveries), `${num(del.pending)} pending`, 'red','D')
        ].join('');

        const trend=data.sales_trend||[]; const methods=data.sales_by_payment_method||[];
        const trendLabels=trend.map(x=>new Date(x.date+'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric'}));
        const trendValues=trend.map(x=>Number(x.total||0));
        const top=data.top_products||[]; const staff=data.staff_performance||[]; const alerts=data.alerts?.items||[];
        main.innerHTML = `
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                ${panel('Sales Trend','Revenue by day in the selected period','<canvas id="adminSalesTrend" class="w-full"></canvas>','xl:col-span-2')}
                ${panel('Payment Mix','Completed payments by method','<canvas id="adminPaymentMix" class="w-full"></canvas><div id="adminPaymentLegend" class="space-y-2 mt-2"></div>')}
            </div>
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                ${panel('Top Selling Products','Products generating the most revenue',rows(top,(x,i)=>`<div class="flex items-center justify-between rounded-xl bg-gray-50 p-3"><div class="flex items-center gap-3"><span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold">${i+1}</span><div><p class="font-semibold text-sm">${esc(x.name)}</p><p class="text-xs text-gray-500">${num(x.units_sold)} units</p></div></div><b class="text-sm">${money(x.revenue)}</b></div>`),'No sales in this period.','xl:col-span-1')}
                ${panel('Staff Performance','Sales generated by staff',rows(staff,(x)=>`<div class="flex items-center justify-between border-b border-gray-100 pb-2"><div><p class="font-semibold text-sm">${esc(x.name)}</p><p class="text-xs text-gray-500">${num(x.sales_count)} sales</p></div><b>${money(x.sales_amount)}</b></div>`),'No staff sales recorded.','xl:col-span-1')}
                ${panel('Alerts & Attention','Items that need action',rows(alerts,(x)=>`<div class="rounded-xl p-3 ${x.severity==='critical'?'bg-red-50 text-red-700':x.severity==='warning'?'bg-yellow-50 text-yellow-700':'bg-blue-50 text-blue-700'}"><p class="text-sm font-medium">${esc(x.message)}</p></div>`),'No active alerts.','xl:col-span-1')}
            </div>
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                ${panel('Staff Overview','Users grouped by role',rows(Object.entries(data.users_by_role||{}),(entry)=>`<div class="flex justify-between p-3 bg-gray-50 rounded-xl"><span>${esc(entry[0])}</span><b>${num(entry[1])}</b></div>`),'No staff records.') }
                ${panel('Top Customers','Highest customer spend in this period',rows(data.top_customers||[],x=>`<div class="flex justify-between p-3 bg-gray-50 rounded-xl"><div><b>${esc(x.name)}</b><p class="text-xs text-gray-500">${num(x.orders_count)} sales</p></div><b>${money(x.total_spent)}</b></div>`),'No customer sales in this period.') }
                ${panel('Delivery Performance','Completed delivery performance',`<div class="grid grid-cols-2 gap-3"><div class="bg-green-50 rounded-xl p-4"><p class="text-xs text-gray-500">Delivered</p><p class="text-2xl font-black">${num(data.delivery_performance?.delivered_count)}</p></div><div class="bg-blue-50 rounded-xl p-4"><p class="text-xs text-gray-500">On-time</p><p class="text-2xl font-black">${data.delivery_performance?.on_time_rate_pct===null?'—':num(data.delivery_performance?.on_time_rate_pct)+'%'}</p></div></div>`) }
            </div>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                ${panel('Recent Orders','Latest customer orders',rows(data.recent_orders||[],x=>`<div class="flex items-center justify-between border-b border-gray-100 pb-3"><div><p class="font-semibold">${esc(x.order_number)}</p><p class="text-xs text-gray-500">${esc(x.customer?.name||'Walk-in Customer')}</p></div><div class="text-right">${statusPill(x.status)}<p class="text-xs font-bold mt-1">${money(x.grand_total)}</p></div></div>`))}
                ${panel('Recent Payments','Latest payment activity',rows(data.recent_payments||[],x=>`<div class="flex items-center justify-between border-b border-gray-100 pb-3"><div><p class="font-semibold">${esc(x.sale?.sale_number||'Payment')}</p><p class="text-xs text-gray-500 capitalize">${esc(x.payment_method||'')}</p></div><b>${money(x.amount)}</b></div>`))}
            </div>`;
        requestAnimationFrame(()=>{drawBar('adminSalesTrend',trendLabels,[{label:'Sales',values:trendValues,fill:'#2563eb'}]);drawDonut('adminPaymentMix',methods);const legend=document.getElementById('adminPaymentLegend');if(legend)legend.innerHTML=methods.map((x,i)=>`<div class="flex justify-between text-sm"><span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:${['#16a34a','#2563eb','#7c3aed','#f97316','#dc2626'][i%5]}"></span><span class="capitalize">${esc(x.payment_method)}</span></span><b>${money(x.total)}</b></div>`).join('');});
    }

    function renderManager(data) {
        const s=data.summary||{}, sales=data.sales||{}, pay=data.payments||{}, inv=data.inventory||{}, ord=data.orders||{}, del=data.deliveries||{}, profit=data.profit||{};
        summaryCards.innerHTML=[card('Sales',money(sales.period_amount),'Selected period','blue','S'),card('Profit',money(profit.estimated_profit),`${num(profit.margin_pct)}% estimated margin`,'green','P'),card('Orders',num(s.total_orders),`${num(ord.pending)} pending`,'orange','O'),card('Payments',money(pay.period_received),'Completed in period','purple','₵'),card('Low Stock',num(inv.low_stock),'Needs attention','yellow','!'),card('Deliveries',num(s.total_deliveries),`${num(del.pending)} pending`,'red','D')].join('');
        const trend=data.sales_trend||[], top=data.top_products||[], alerts=data.alerts?.items||[];
        main.innerHTML=`<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">${panel('Sales Trend','Revenue during selected period','<canvas id="managerTrend" class="w-full"></canvas>')}${panel('Order & Delivery Status','Operational workload',`<div class="grid grid-cols-2 gap-3">${Object.entries(ord).map(([k,v])=>`<div class="rounded-xl bg-gray-50 p-4"><p class="text-xs text-gray-500 capitalize">${esc(k)}</p><p class="text-2xl font-black">${num(v)}</p></div>`).join('')}${Object.entries(del).map(([k,v])=>`<div class="rounded-xl bg-blue-50 p-4"><p class="text-xs text-gray-500 capitalize">${esc(k)}</p><p class="text-2xl font-black text-blue-700">${num(v)}</p></div>`).join('')}</div>`)}</div><div class="grid grid-cols-1 xl:grid-cols-3 gap-6">${panel('Top Products','Best revenue performers',rows(top,(x,i)=>`<div class="flex justify-between p-3 rounded-xl bg-gray-50"><span><b>${i+1}. ${esc(x.name)}</b><small class="block text-gray-500">${num(x.units_sold)} units</small></span><b>${money(x.revenue)}</b></div>`))}${panel('Low Stock','Products to restock',rows(data.low_stock_items||[],x=>`<div class="flex justify-between p-3 rounded-xl bg-yellow-50"><span><b>${esc(x.name)}</b><small class="block text-gray-500">Reorder at ${num(x.reorder_level)}</small></span><b class="text-yellow-700">${num(x.stock_quantity)}</b></div>`))}${panel('Alerts','Operational warnings',rows(alerts,x=>`<div class="p-3 rounded-xl bg-red-50 text-red-700 text-sm">${esc(x.message)}</div>`))}</div>`;
        requestAnimationFrame(()=>drawBar('managerTrend',trend.map(x=>new Date(x.date+'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric'})),[{label:'Sales',values:trend.map(x=>x.total),fill:'#2563eb'}]));
    }

    function renderSales(data) {
        const s=data.summary||{}, today=data.today||{}, mine=data.my_performance||{}, sales=data.sales||{}, ord=data.orders||{};
        summaryCards.innerHTML=[card('Today\'s Sales',money(today.sales_amount),'Revenue today','blue','S'),card('My Sales',money(mine.my_sales_amount),`${num(mine.my_sales_count)} sales in period`,'green','M'),card('Orders Today',num(today.orders),'Orders received today','orange','O'),card('Payments Today',money(today.payments),'Completed payments','purple','₵'),card('Customers',num(s.total_customers),'Customers served','red','C'),card('Products',num(s.total_products),'Available catalogue','blue','P')].join('');
        main.innerHTML=`<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">${panel('My Performance','Your sales in the selected period',`<div class="grid grid-cols-2 gap-4"><div class="rounded-xl bg-green-50 p-4"><p class="text-xs text-gray-500">Sales</p><p class="text-2xl font-black text-green-700">${money(mine.my_sales_amount)}</p></div><div class="rounded-xl bg-blue-50 p-4"><p class="text-xs text-gray-500">Transactions</p><p class="text-2xl font-black text-blue-700">${num(mine.my_sales_count)}</p></div></div>`)}${panel('Order Queue','Current order status',`<div class="space-y-3">${Object.entries(ord).map(([k,v])=>`<div class="flex justify-between"><span class="capitalize">${esc(k)}</span><b>${num(v)}</b></div>`).join('')}</div>`)}${panel('Quick Focus','What to do next','<p class="text-sm text-gray-600">Use <b>Record Sale</b> for completed transactions and <b>Add Payment</b> when a customer settles an outstanding sale.</p>')}</div><div class="grid grid-cols-1 xl:grid-cols-2 gap-6">${panel('Recent Orders','Latest orders',rows(data.recent_orders||[],x=>`<div class="flex justify-between border-b border-gray-100 pb-3"><div><b>${esc(x.order_number)}</b><p class="text-xs text-gray-500">${esc(x.customer?.name||'Walk-in Customer')}</p></div>${statusPill(x.status)}</div>`))}${panel('Top Products','Best products in this period',rows(data.top_products||[],x=>`<div class="flex justify-between"><span>${esc(x.name)}</span><b>${money(x.revenue)}</b></div>`))}</div>`;
    }

    function renderInventory(data) {
        const s=data.summary||{}, st=data.stock||{};
        summaryCards.innerHTML=[card('Total Products',num(s.total_products),'Catalogue items','blue','P'),card('Total Units',num(st.total_units),'Units currently in stock','green','U'),card('Stock Value',money(st.stock_value),'Estimated cost value','purple','₵'),card('Low Stock',num(s.low_stock),'At or below reorder level','yellow','!'),card('Out of Stock',num(s.out_of_stock),'Unavailable products','red','0'),card('Active Products',num(s.active_products),'Currently active','blue','✓')].join('');
        main.innerHTML=`<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">${panel('Products Requiring Restock','Prioritised by lowest stock',rows(st.low_stock_items||[],x=>`<div class="flex items-center justify-between rounded-xl ${Number(x.stock_quantity)<=0?'bg-red-50':'bg-yellow-50'} p-3"><div><b>${esc(x.name)}</b><p class="text-xs text-gray-500">SKU: ${esc(x.sku||'-')} • Reorder: ${num(x.reorder_level)}</p></div><b class="${Number(x.stock_quantity)<=0?'text-red-700':'text-yellow-700'}">${num(x.stock_quantity)}</b></div>`))}${panel('Out of Stock','Products currently unavailable',rows(st.out_of_stock_items||[],x=>`<div class="flex justify-between p-3 bg-red-50 rounded-xl"><b>${esc(x.name)}</b><span class="text-red-700 font-bold">Out of stock</span></div>`))}</div><div class="rounded-2xl p-5 bg-blue-50 border border-blue-100"><b>Inventory tip:</b><span class="text-sm text-gray-600 ml-1">Use <b>Update Stock</b> to record stock-in, stock-out or an adjustment so inventory history stays traceable.</span></div>`;
    }

    function renderProcurement(data) {
        const s=data.summary||{}, p=data.purchases||{}, needs=data.stock_needs||{};
        summaryCards.innerHTML=[card('Suppliers',num(s.total_suppliers),'Suppliers available','blue','S'),card('Purchase Spend',money(p.total_amount),'Selected period','orange','₵'),card('Purchases Today',num(p.today_count),money(p.today_amount),'green','P'),card('Products',num(s.total_products),'Products to source','purple','P'),card('Low Stock',num(needs.low_stock),'Needs procurement','yellow','!'),card('Out of Stock',num(needs.out_of_stock),'Urgent sourcing','red','0')].join('');
        main.innerHTML=`<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">${panel('Top Suppliers','Suppliers by recorded purchase spend',rows(data.top_suppliers||[],x=>`<div class="flex justify-between p-3 bg-gray-50 rounded-xl"><div><b>${esc(x.name)}</b><p class="text-xs text-gray-500">${num(x.purchases_count)} purchases</p></div><b>${money(x.total_spend)}</b></div>`))}${panel('Products Needing Procurement','Low-stock products',rows(data.low_stock_items||[],x=>`<div class="flex justify-between p-3 bg-yellow-50 rounded-xl"><b>${esc(x.name)}</b><b class="text-yellow-700">${num(x.stock_quantity)} left</b></div>`))}${panel('Recent Purchases','Latest procurement records',rows(data.recent_purchases||[],x=>`<div class="flex justify-between border-b border-gray-100 pb-2"><b>${esc(x.purchase_number)}</b><b>${money(x.total_amount)}</b></div>`))}</div>`;
    }

    function renderDelivery(data) {
        const s=data.summary||{}, mine=data.my_performance||{};
        summaryCards.innerHTML=[card('Pending',num(s.pending),'Awaiting assignment','yellow','!'),card('Assigned',num(s.assigned),'Ready for pickup','blue','A'),card('Out for Delivery',num(s.out_for_delivery),'Currently on route','orange','→'),card('Delivered',num(s.delivered),'Completed deliveries','green','✓'),card('My Deliveries',num((data.my_assignments||[]).length),'Current assignments','purple','M'),card('On-time Rate',mine.on_time_rate_pct===null?'—':`${num(mine.on_time_rate_pct)}%`,'Your completed deliveries','green','T')].join('');
        main.innerHTML=`<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">${panel('My Current Assignments','Deliveries assigned to you',rows(data.my_assignments||[],x=>`<div class="rounded-xl bg-blue-50 p-4"><div class="flex justify-between gap-3"><div><b>${esc(x.recipient_name)}</b><p class="text-xs text-gray-500 mt-1">${esc(x.delivery_address)}</p></div>${statusPill(x.status)}</div><p class="text-xs text-gray-500 mt-2">Phone: ${esc(x.recipient_phone||'-')}</p></div>`,'No active deliveries assigned to you.'))}${panel('My Performance','Delivery performance for selected period',`<div class="grid grid-cols-2 gap-4"><div class="bg-green-50 rounded-xl p-4"><p class="text-xs text-gray-500">Delivered</p><p class="text-2xl font-black">${num(mine.delivered_count)}</p></div><div class="bg-blue-50 rounded-xl p-4"><p class="text-xs text-gray-500">Avg. Hours</p><p class="text-2xl font-black">${mine.avg_delivery_hours===null?'—':num(mine.avg_delivery_hours)}</p></div></div>` )}</div><div class="grid grid-cols-1 xl:grid-cols-3 gap-6">${panel('Pending', 'Waiting to be assigned', rows(data.deliveries?.pending||[],x=>`<div class="flex justify-between"><b>${esc(x.recipient_name)}</b>${statusPill(x.status)}</div>`))}${panel('Assigned','Awaiting delivery',rows(data.deliveries?.assigned||[],x=>`<div class="flex justify-between"><b>${esc(x.recipient_name)}</b>${statusPill(x.status)}</div>`))}${panel('Recently Delivered','Completed jobs',rows(data.deliveries?.completed||[],x=>`<div class="flex justify-between"><b>${esc(x.recipient_name)}</b>${statusPill(x.status)}</div>`))}</div>`;
    }

    async function loadDashboard() {
        errorBox.classList.add('hidden');
        const from=fromInput.value, to=toInput.value;
        if(from && to && from>to){errorBox.textContent='The From date cannot be after the To date.';errorBox.classList.remove('hidden');return;}
        try{
            const query=from&&to?`?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`:'';
            const response=await fetch('/api/dashboard'+query,{headers:{Accept:'application/json',Authorization:`Bearer ${token}`}});
            if(response.status===401){localStorage.removeItem('auth_token');localStorage.removeItem('user');window.location.href='/login';return;}
            if(!response.ok) throw new Error(`HTTP ${response.status}`);
            const result=await response.json(); const data=result.data||result; const role=result.role||data.role||JSON.parse(localStorage.getItem('user')||'{}').roles?.[0]||'User';
            const user=result.user||JSON.parse(localStorage.getItem('user')||'{}');
            document.getElementById('userName').textContent=user.name||'User';document.getElementById('userRole').textContent=role;document.getElementById('dashboardSubtitle').textContent=`${role} dashboard • ${from||'today'} to ${to||'today'}`;document.getElementById('welcomeTitle').textContent=`Welcome back, ${user.name||'User'}`;document.getElementById('welcomeText').textContent=`Here is your ${role.toLowerCase()} view of the bakery.`;
            if(role==='Admin')renderAdmin(data); else if(role==='Manager')renderManager(data); else if(role==='Sales Staff')renderSales(data); else if(role==='Inventory Staff')renderInventory(data); else if(role==='Procurement Staff')renderProcurement(data); else if(role==='Delivery Staff')renderDelivery(data); else {summaryCards.innerHTML=card('Dashboard','Ready','Your dashboard is available','blue','✓');main.innerHTML='';}
        }catch(e){console.error(e);errorBox.textContent='Unable to load dashboard data. Please check that Laravel and the database are running, then refresh.';errorBox.classList.remove('hidden');}
    }

    document.getElementById('dashboardRefresh').addEventListener('click',loadDashboard);
    loadDashboard();
});
</script>
@endsection
