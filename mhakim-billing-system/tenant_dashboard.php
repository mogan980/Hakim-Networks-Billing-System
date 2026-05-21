<?php
require_once __DIR__ . "/access_guard.php";
require_once __DIR__ . "/config/database.php";

if (($_SESSION["tenant_role"] ?? "") === "super_admin") {
    header("Location: noc_final_clean.php");
    exit;
}

$companyId = $_SESSION["tenant_company_id"];
$name = $_SESSION["tenant_name"] ?? "Tenant";

function one($pdo,$sql,$id){
    $s=$pdo->prepare($sql);
    $s->execute([$id]);
    return $s->fetchColumn();
}

$clients = one($pdo,"SELECT COUNT(*) FROM clients WHERE company_id=?",$companyId);
$activeClients = one($pdo,"SELECT COUNT(*) FROM clients WHERE company_id=? AND status='active'",$companyId);
$expiredClients = one($pdo,"SELECT COUNT(*) FROM clients WHERE company_id=? AND status='expired'",$companyId);
$packages = one($pdo,"SELECT COUNT(*) FROM packages WHERE company_id=?",$companyId);
$vouchers = one($pdo,"SELECT COUNT(*) FROM vouchers WHERE company_id=?",$companyId);
$unusedVouchers = one($pdo,"SELECT COUNT(*) FROM vouchers WHERE company_id=? AND status='unused'",$companyId);
$usedVouchers = one($pdo,"SELECT COUNT(*) FROM vouchers WHERE company_id=? AND status='used'",$companyId);
$todayRevenue = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE company_id=? AND status='paid' AND DATE(created_at)=CURDATE()",$companyId);
$totalRevenue = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE company_id=? AND status='paid'",$companyId);
?>
<!DOCTYPE html>
<html>
<head>
<title>Tenant ISP Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#e7f8ff;color:#0f172a}
.sidebar{width:245px;height:100vh;position:fixed;left:0;top:0;background:linear-gradient(180deg,#020617,#052e2b);padding:18px 12px;color:white;overflow:auto}
.logo{width:52px;height:52px;border-radius:16px;background:#22c55e;color:#052e16;display:grid;place-items:center;font-size:26px;font-weight:900;margin-bottom:14px}
.sidebar h2{margin:0;font-size:22px}.sidebar p{color:#94a3b8;font-size:12px}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px 14px;border-radius:12px;margin:7px 0;background:rgba(15,23,42,.62);font-weight:800;font-size:14px}
.sidebar a:hover,.sidebar a.active{background:#064e3b;color:#fff}
.main{margin-left:245px;padding:20px 28px 0}
.command{background:linear-gradient(135deg,#0f172a,#166534);color:white;border-radius:20px;padding:22px;margin-bottom:16px;box-shadow:0 12px 30px rgba(15,23,42,.18)}
.command h1{margin:0;font-size:28px}.command p{margin:10px 0 0;color:#d1fae5}
.dot{display:inline-block;width:12px;height:12px;background:#22c55e;border-radius:50%;box-shadow:0 0 16px #22c55e}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px}
.card{background:white;border-radius:18px;padding:18px;box-shadow:0 12px 35px rgba(15,23,42,.08)}
.card h3{margin:0;color:#475569;font-size:13px}.card strong{display:block;margin-top:10px;font-size:25px;color:#16a34a}
.card .red{color:#dc2626}.card .blue{color:#2563eb}.card .orange{color:#f97316}
.grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px}
.grid2{display:grid;grid-template-columns:1.25fr 1fr;gap:16px;margin-bottom:16px}
.panel{background:white;border-radius:18px;padding:18px;box-shadow:0 12px 35px rgba(15,23,42,.08)}
.panel h2{margin:0 0 16px;color:#0f172a}
.row{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e5e7eb;padding:11px 0;font-size:13px}
.badge{padding:7px 10px;border-radius:999px;background:#dcfce7;color:#16a34a;font-weight:900;font-size:11px}
.badge-blue{background:#dbeafe;color:#2563eb}.badge-orange{background:#ffedd5;color:#f97316}
.progress{height:8px;background:#e5e7eb;border-radius:99px;overflow:hidden;margin-top:7px}
.progress span{display:block;height:100%;background:#16a34a;border-radius:99px}
.quick{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.quick a{background:#16a34a;color:white;text-align:center;padding:13px;border-radius:12px;text-decoration:none;font-weight:900;font-size:13px}
.footer{margin-left:245px;background:#064e3b;color:white;padding:34px 60px;display:grid;grid-template-columns:repeat(4,1fr);gap:30px}
.footer h3{margin-top:0}.footer p,.footer a{display:block;color:#d1fae5;text-decoration:none;margin:9px 0;font-size:14px}
.settings{position:fixed;right:32px;bottom:28px;background:#16a34a;color:white;border:0;border-radius:999px;padding:14px 24px;font-weight:900}
@media(max-width:1000px){.stats,.grid3,.grid2,.footer{grid-template-columns:1fr}.sidebar{position:relative;width:100%;height:auto}.main,.footer{margin-left:0}}
</style>
</head>
<body>

<div class="sidebar">
<div class="logo">H</div>
<h2>Hakim Networks</h2>
<p><?php echo htmlspecialchars($name); ?></p>

<a class="active" href="/mhakim-billing-system/tenant_noc_final_clean.php">📊 Dashboard</a>
<a href="/mhakim-billing-system/tenant/tenant_clients.php">👥 Clients</a>
<a href="/mhakim-billing-system/tenant/tenant_packages.php">📦 Packages</a>
<a href="/mhakim-billing-system/tenant/tenant_vouchers.php">🎟️ Vouchers</a>
<a href="/mhakim-billing-system/tenant/tenant_payments.php">💳 Payments</a>
<a href="/mhakim-billing-system/tenant/tenant_router.php">📡 Router Setup</a>
<a href="/mhakim-billing-system/router_wizard.php">🧙 Router Wizard</a>
<a href="/mhakim-billing-system/tenant/tenant_reports.php">📈 Reports</a>
<a href="/mhakim-billing-system/tenant/tenant_analytics.php">📊 Analytics</a>
<a href="/mhakim-billing-system/tenant/tenant_network.php">🌐 Network</a>
<a href="/mhakim-billing-system/tenant/tenant_activity.php">🧾 Activity Logs</a>
<a href="/mhakim-billing-system/logout.php">🚪 Logout</a>
</div>

<div class="main">

<div class="command">
<h1><span class="dot"></span> My ISP Command Center</h1>
<p>Auto-syncs every 10 seconds: MikroTik health, hotspot users, billing, vouchers, packages and revenue.</p>
</div>

<div class="stats">
<div class="card"><h3>Active Clients</h3><strong id="activeClients"><?php echo $activeClients; ?></strong></div>
<div class="card"><h3>Expired Clients</h3><strong class="red" id="expiredClients"><?php echo $expiredClients; ?></strong></div>
<div class="card"><h3>Today Revenue</h3><strong id="todayRevenue">Ksh <?php echo number_format($todayRevenue); ?></strong></div>
<div class="card"><h3>Total Revenue</h3><strong class="blue" id="totalRevenue">Ksh <?php echo number_format($totalRevenue); ?></strong></div>
</div>

<div class="grid3">
<div class="panel">
<h2>Network Health</h2>
<div class="row"><span>Network Strength</span><b id="networkStrength">0%</b></div>
<div class="progress"><span id="networkBar" style="width:0%"></span></div>
<div class="row"><span>Wireless Signal Quality</span><b id="signalQuality">0%</b></div>
<div class="progress"><span id="signalBar" style="width:0%"></span></div>
<div class="row"><span>Avg Signal</span><span class="badge" id="avgSignal">N/A</span></div>
<div class="row"><span>Wireless Clients</span><span class="badge" id="wirelessClients">0</span></div>
</div>

<div class="panel">
<h2>Bandwidth Monitor</h2>
<div class="row"><span>Download RX</span><span class="badge" id="downloadRx">Live</span></div>
<div class="row"><span>Upload TX</span><span class="badge-blue badge" id="uploadTx">Live</span></div>
<div class="row"><span>WAN Utilization</span><span class="badge" id="wanUsage">0%</span></div>
<div class="row"><span>Updated</span><span class="badge-blue badge" id="updatedAt">-</span></div>
<div class="row"><span>Simple Queues</span><span class="badge-blue badge" id="simpleQueues">0</span></div>
<p style="font-size:13px;color:#475569">Bandwidth control and queue monitoring sync from your own MikroTik only.</p>
</div>

<div class="panel">
<h2>MikroTik Health</h2>
<div class="row"><span>CPU Load</span><span class="badge" id="cpuLoad">0%</span></div>
<div class="row"><span>Uptime</span><span class="badge-blue badge" id="uptime">-</span></div>
<div class="row"><span>Memory</span><span class="badge" id="memory">- free</span></div>
<div class="row"><span>Storage</span><span class="badge-blue badge">- free</span></div>
<div class="row"><span>Board</span><span class="badge-orange badge" id="board">-</span></div>
</div>
</div>

<div class="grid2">
<div class="panel">
<h2>ISP Operations Center</h2>
<div class="row"><span>Hotspot Gateway</span><span class="badge" id="gateway">Checking</span></div>
<div class="row"><span>M-Pesa Gateway</span><span class="badge-orange badge">Tenant Mode</span></div>
<div class="row"><span>MikroTik API</span><span class="badge" id="apiStatus">Checking</span></div>
<div class="row"><span>Auto Expiry Engine</span><span class="badge">Ready</span></div>
<div class="row"><span>Synced Hotspot Users</span><span class="badge-blue badge" id="syncedUsers">0</span></div>
<div class="row"><span>Online Hotspot Users</span><span class="badge" id="onlineUsers">0</span></div>
<div class="row"><span>Router Identity</span><span class="badge" id="routerIdentity">-</span></div>
</div>

<div class="panel">
<h2>Quick Actions</h2>
<div class="quick">
<a href="/mhakim-billing-system/tenant/tenant_clients.php">+ Add Client</a>
<a href="/mhakim-billing-system/tenant/tenant_packages.php">Packages</a>
<a href="/mhakim-billing-system/tenant/tenant_vouchers.php">Vouchers</a>
<a href="/mhakim-billing-system/tenant/tenant_payments.php">Payments</a>
<a href="/mhakim-billing-system/tenant/tenant_router.php">MikroTik</a>
<a href="/mhakim-billing-system/tenant/tenant_router.php">Reports</a>
</div>
</div>
</div>

<div class="grid2">
<div class="panel"><h2>Revenue Analytics</h2><canvas id="revenueChart"></canvas></div>
<div class="panel"><h2>Client Status</h2><canvas id="clientChart"></canvas></div>
</div>

<div class="panel">
<h2>Business Performance</h2>
<div class="stats">
<div class="card"><h3>Total Vouchers</h3><strong class="blue" id="totalVouchers"><?php echo $vouchers; ?></strong></div>
<div class="card"><h3>Unused Vouchers</h3><strong id="unusedVouchers"><?php echo $unusedVouchers; ?></strong></div>
<div class="card"><h3>Used Vouchers</h3><strong class="orange" id="usedVouchers"><?php echo $usedVouchers; ?></strong></div>
<div class="card"><h3>System Mode</h3><strong>LIVE</strong></div>
</div>
</div>

</div>

<div class="footer">
<div><h3>Support</h3><p>WhatsApp Support</p><p>Call Support</p><p>Email Support</p></div>
<div><h3>Billing</h3><a href="/mhakim-billing-system/tenant/tenant_payments.php">Payment History</a><a href="/mhakim-billing-system/tenant/tenant_vouchers.php">Generate Vouchers</a><a href="/mhakim-billing-system/tenant/tenant_packages.php">Manage Packages</a></div>
<div><h3>Quick Links</h3><a href="/mhakim-billing-system/tenant_noc_final_clean.php">Dashboard</a><a href="/mhakim-billing-system/tenant/tenant_clients.php">Clients</a><a href="/mhakim-billing-system/tenant/tenant_router.php">Router Health</a></div>
<div><h3>Workspace</h3><p>Private Tenant System</p><p>Secure Router Sync</p><p>Live ISP Tools</p></div>
</div>

<button class="settings">⚙ Settings</button>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let clientChart, revenueChart;

function makeCharts(){
    const rc=document.getElementById("revenueChart");
    const cc=document.getElementById("clientChart");

    if(rc && !revenueChart){
        revenueChart=new Chart(rc,{type:"line",data:{labels:["Today","Total"],datasets:[{label:"Revenue Ksh",data:[0,0],borderWidth:3,fill:true,tension:.4}]},options:{responsive:true}});
    }

    if(cc && !clientChart){
        clientChart=new Chart(cc,{type:"doughnut",data:{labels:["Active","Expired"],datasets:[{data:[0,0]}]},options:{responsive:true}});
    }
}

function money(v){return "Ksh " + Number(v || 0).toLocaleString();}

async function liveTenant(){
    try{
        const d=await (await fetch("/mhakim-billing-system/tenant/tenant_live.php?t="+Date.now(),{cache:"no-store"})).json();
        activeClients.innerText=d.active_clients||0;
        expiredClients.innerText=d.expired_clients||0;
        todayRevenue.innerText=money(d.today_revenue);
        totalRevenue.innerText=money(d.total_revenue);
        totalVouchers.innerText=d.vouchers||0;
        unusedVouchers.innerText=d.unused_vouchers||0;
        usedVouchers.innerText=d.used_vouchers||0;

        makeCharts();
        revenueChart.data.datasets[0].data=[d.today_revenue||0,d.total_revenue||0];
        revenueChart.update();
        clientChart.data.datasets[0].data=[d.active_clients||0,d.expired_clients||0];
        clientChart.update();
    }catch(e){}
}

async function liveRouter(){
    try{
        const d=await (await fetch("/mhakim-billing-system/tenant/tenant_router_live.php?t="+Date.now(),{cache:"no-store"})).json();
        const online=d.status==="online";

        routerIdentity.innerText=d.router_name||"-";
        gateway.innerText=online?"Online":"Offline";
        apiStatus.innerText=online?"Connected":"Offline";
        cpuLoad.innerText=(d.cpu_load||0)+"%";
        uptime.innerText=d.uptime||"-";
        memory.innerText=d.free_memory||"-";
        board.innerText=d.board||"-";
        onlineUsers.innerText=d.active_hotspot_users||0;
        syncedUsers.innerText=d.active_hotspot_users||0;
        wirelessClients.innerText=d.active_hotspot_users||0;
        simpleQueues.innerText=d.simple_queues||0;
        updatedAt.innerText=d.updated_at||"-";

        let health=online?90:0;
        let signal=online?80:0;
        networkStrength.innerText=health+"%";
        signalQuality.innerText=signal+"%";
        networkBar.style.width=health+"%";
        signalBar.style.width=signal+"%";
        avgSignal.innerText=online?"Cached":"N/A";
        downloadRx.innerText=online?"Monitoring":"-";
        uploadTx.innerText=online?"Monitoring":"-";
        wanUsage.innerText=online?"Live":"0%";
    }catch(e){}
}

makeCharts();
liveTenant();
liveRouter();

let refreshRunning=false;

async function silentRefresh(){

    if(refreshRunning) return;
    refreshRunning=true;

    try{
        await Promise.all([
            liveTenant(),
            liveRouter()
        ]);
    }catch(e){}

    refreshRunning=false;
}

setInterval(silentRefresh,10000);

</script>

</body>
</html>
