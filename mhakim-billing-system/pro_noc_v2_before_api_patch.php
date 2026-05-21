<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function mbps($bits){ return round(((float)$bits) / 1000000, 2); }

function formatBytes($bytes){
    $bytes = (float)($bytes ?? 0);
    if($bytes >= 1073741824) return round($bytes/1073741824,2)." GB";
    if($bytes >= 1048576) return round($bytes/1048576,2)." MB";
    if($bytes >= 1024) return round($bytes/1024,2)." KB";
    return round($bytes,0)." B";
}

function getData($pdo){
    $router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $d = [
        "ok"=>false,
        "error"=>null,
        "router"=>$router,
        "status"=>"offline",
        "identity"=>"MikroTik",
        "ip"=>$router["router_ip"] ?? "-",
        "port"=>$router["router_port"] ?? "8728",
        "cpu"=>0,
        "uptime"=>"-",
        "free_memory"=>0,
        "total_memory"=>0,
        "board"=>"-",
        "version"=>"-",
        "rx"=>0,
        "tx"=>0,
        "hotspot"=>[],
        "pppoe"=>[],
        "queues"=>[],
        "leases"=>[],
        "interfaces"=>[],
        "updated"=>date("H:i:s")
    ];

    try{
        if(!$router) throw new Exception("No router configured");

        $client = new Client(new Config([
            "host"=>$router["router_ip"],
            "user"=>$router["router_username"],
            "pass"=>$router["router_password"],
            "port"=>(int)$router["router_port"],
            "timeout"=>5,
            "attempts"=>1
        ]));

        $identity = $client->query(new Query("/system/identity/print"))->read();
        $resource = $client->query(new Query("/system/resource/print"))->read();
        $hotspot  = $client->query(new Query("/ip/hotspot/active/print"))->read();
        $pppoe    = $client->query(new Query("/ppp/active/print"))->read();
        $queues   = $client->query(new Query("/queue/simple/print"))->read();
        $leases   = $client->query(new Query("/ip/dhcp-server/lease/print"))->read();
        $ifaces   = $client->query(new Query("/interface/print"))->read();

        $traffic = $client->query(
            (new Query("/interface/monitor-traffic"))
                ->equal("interface","ether1")
                ->equal("once","")
        )->read();

        $res = $resource[0] ?? [];

        $d["ok"] = true;
        $d["status"] = "online";
        $d["identity"] = $identity[0]["name"] ?? "MikroTik";
        $d["cpu"] = (int)($res["cpu-load"] ?? 0);
        $d["uptime"] = $res["uptime"] ?? "-";
        $d["free_memory"] = (float)($res["free-memory"] ?? 0);
        $d["total_memory"] = (float)($res["total-memory"] ?? 0);
        $d["board"] = $res["board-name"] ?? "-";
        $d["version"] = $res["version"] ?? "-";
        $d["rx"] = mbps($traffic[0]["rx-bits-per-second"] ?? 0);
        $d["tx"] = mbps($traffic[0]["tx-bits-per-second"] ?? 0);
        $d["hotspot"] = $hotspot;
        $d["pppoe"] = $pppoe;
        $d["queues"] = $queues;
        $d["leases"] = $leases;
        $d["interfaces"] = $ifaces;

    }catch(Exception $e){
        $d["error"] = $e->getMessage();
    }

    return $d;
}

if(isset($_GET["api"])){
    header("Content-Type: application/json");
    echo json_encode(getData($pdo));
    exit;
}

$data = getData($pdo);
?>
<!DOCTYPE html>
<html>
<head>
<title>Hakim Networks Pro NOC V2</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Inter,Arial,sans-serif;
    background:linear-gradient(140deg,#020617,#064e3b,#e0f2fe);
    color:#020617;
}
.app{display:flex;min-height:100vh}
.sidebar{
    width:270px;
    height:100vh;
    position:fixed;
    left:0;top:0;
    background:rgba(2,6,23,.96);
    color:white;
    padding:24px 16px;
    overflow:auto;
}
.logo{font-size:28px;font-weight:900}
.brand{font-size:13px;color:#94a3b8;margin-top:5px}
.nav{margin-top:28px}
.nav a{
    display:flex;
    align-items:center;
    gap:10px;
    color:white;
    text-decoration:none;
    padding:13px 14px;
    border-radius:14px;
    margin-bottom:8px;
    font-weight:700;
    background:rgba(255,255,255,.04);
}
.nav a:hover,.nav a.active{background:#16a34a}
.connection{
    background:rgba(255,255,255,.07);
    margin-top:25px;
    padding:16px;
    border-radius:18px;
}
.main{
    margin-left:270px;
    padding:26px;
    width:calc(100% - 270px);
}
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    color:white;
    margin-bottom:20px;
}
.title h1{margin:0;font-size:31px}
.title p{margin:6px 0 0;color:#dbeafe}
.badge{
    display:inline-block;
    padding:7px 13px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.online{background:#dcfce7;color:#047857}
.offline{background:#fee2e2;color:#b91c1c}
.warning{background:#fef3c7;color:#92400e}
.panel{
    background:rgba(255,255,255,.94);
    border-radius:22px;
    box-shadow:0 18px 45px rgba(0,0,0,.16);
    overflow:hidden;
}
.kpis{
    display:grid;
    grid-template-columns:repeat(4,1fr);
}
.kpi{
    padding:24px;
    border-right:1px solid #e5e7eb;
    border-bottom:1px solid #e5e7eb;
    min-height:130px;
}
.kpi h4{margin:0;color:#64748b;font-size:14px}
.kpi h2{font-size:28px;margin:12px 0 0}
.kpi small{color:#64748b}
.grid{
    display:grid;
    grid-template-columns:1.3fr .8fr .8fr;
    gap:18px;
    margin-top:18px;
}
.two{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
    margin-top:18px;
}
.card{
    background:rgba(255,255,255,.95);
    border-radius:22px;
    padding:20px;
    box-shadow:0 16px 40px rgba(0,0,0,.14);
}
.card h2{margin:0 0 15px;font-size:20px}
.row{
    display:flex;
    justify-content:space-between;
    border-bottom:1px solid #e5e7eb;
    padding:11px 0;
}
.progress{
    width:100%;
    height:9px;
    background:#e5e7eb;
    border-radius:999px;
    overflow:hidden;
}
.bar{
    height:100%;
    background:#16a34a;
    width:0%;
}
.quick{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
}
.quick a{
    text-decoration:none;
    color:#020617;
    border:1px solid #dbeafe;
    border-radius:16px;
    padding:16px;
    font-weight:900;
    background:#fff;
}
.quick a:hover{background:#dcfce7}
.search{
    width:100%;
    padding:13px;
    border:1px solid #cbd5e1;
    border-radius:13px;
    margin-bottom:13px;
}
table{
    width:100%;
    border-collapse:collapse;
    overflow:hidden;
    border-radius:15px;
}
th{
    background:#020617;
    color:white;
    text-align:left;
    padding:12px;
}
td{
    padding:11px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
}
tr:hover{background:#f0fdf4}
.pill{
    padding:5px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:900;
    display:inline-block;
    background:#e0f2fe;
    color:#0369a1;
}
.good{background:#dcfce7;color:#166534}
.bad{background:#fee2e2;color:#991b1b}
.chartbox{height:280px}
.footer{
    color:white;
    text-align:center;
    padding:25px;
}
@media(max-width:1100px){
    .sidebar{position:relative;width:100%;height:auto}
    .main{margin-left:0;width:100%}
    .app{display:block}
    .kpis{grid-template-columns:repeat(2,1fr)}
    .grid,.two{grid-template-columns:1fr}
}
@media(max-width:650px){
    .kpis{grid-template-columns:1fr}
}
.hero-stats{display:grid;grid-template-columns:1.1fr 1fr 1fr 1fr;gap:16px;margin-bottom:18px}
.hero-card{background:linear-gradient(135deg,#ffffff,#f8fafc);border-radius:24px;padding:22px;box-shadow:0 18px 45px rgba(0,0,0,.16);position:relative;overflow:hidden;min-height:145px}
.hero-card:after{content:"";position:absolute;right:-35px;top:-35px;width:115px;height:115px;border-radius:999px;background:rgba(22,163,74,.12)}
.hero-card h4{margin:0;color:#64748b;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.hero-card h2{margin:14px 0 6px;font-size:30px}
.hero-card small{color:#64748b;font-weight:700}
.hero-card canvas{max-height:82px!important;max-width:82px!important;position:absolute;right:24px;bottom:22px}
.pro-footer{margin-top:28px;padding:28px;border-radius:26px;background:linear-gradient(135deg,#020617,#064e3b);color:white;box-shadow:0 18px 50px rgba(0,0,0,.25)}
.footer-top{display:grid;grid-template-columns:1.1fr 2fr 1fr;gap:20px;align-items:start}
.footer-brand h2{margin:0 0 8px;font-size:26px}
.footer-brand p,.support-box p{margin:0;color:#cbd5e1;font-size:14px}
.footer-links{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.footer-links a{color:white;text-decoration:none;background:rgba(255,255,255,.08);padding:13px;border-radius:15px;font-weight:800;font-size:13px;transition:.25s}
.footer-links a:hover{background:#16a34a;transform:translateY(-2px)}
.footer-links a.logout:hover{background:#dc2626}
.support-box{background:rgba(255,255,255,.08);padding:16px;border-radius:18px}
.footer-bottom{margin-top:20px;padding-top:16px;border-top:1px solid rgba(255,255,255,.12);display:flex;justify-content:space-between;color:#cbd5e1;font-size:13px}
@media(max-width:1100px){.hero-stats{grid-template-columns:1fr 1fr}.footer-top{grid-template-columns:1fr}.footer-links{grid-template-columns:1fr 1fr}}
@media(max-width:650px){.hero-stats{grid-template-columns:1fr}.footer-bottom{display:block}.footer-links{grid-template-columns:1fr}}

/* ===== PROFESSIONAL LAYOUT FIX ===== */
body{overflow-x:hidden;}
.sidebar{width:250px!important;z-index:9999!important;}
.main{margin-left:250px!important;width:calc(100% - 250px)!important;padding:26px!important;}
.topbar{position:sticky;top:0;z-index:50;background:linear-gradient(135deg,#020617,#064e3b);padding:18px 0;margin-bottom:18px;}
.hero-stats{grid-template-columns:repeat(4,minmax(180px,1fr))!important;gap:16px!important;}
.hero-card{min-height:130px!important;padding:20px!important;}
.hero-card canvas{width:72px!important;height:72px!important;max-width:72px!important;max-height:72px!important;}
.two{grid-template-columns:1fr!important;}
.card{overflow-x:auto!important;}
table{table-layout:auto!important;width:100%!important;min-width:760px!important;}
th,td{white-space:nowrap!important;font-size:13px!important;}
.search{max-width:100%;}
.pro-footer{margin-left:0!important;}
@media(max-width:1100px){.sidebar{position:relative!important;width:100%!important;height:auto!important}.main{margin-left:0!important;width:100%!important}.hero-stats{grid-template-columns:repeat(2,1fr)!important}}
@media(max-width:650px){.hero-stats{grid-template-columns:1fr!important}table{min-width:650px!important}}

</style>
</head>

<body>
<div class="app">

<aside class="sidebar">
    <div class="logo">📶 M.Hakim</div>
    <div class="brand">Hakim Networks Pro NOC</div>

    <div class="nav">
        <a class="active" href="pro_noc_v2.php">📡 Live NOC</a>
        <a href="noc_final_clean.php">📊 Main Dashboard</a>
        <a href="noc.php">🖥 NOC Center</a>
        <a href="routers.php">🛰 Routers</a>
        <a href="clients.php">👥 Clients</a>
        <a href="packages.php">📦 Packages</a>
        <a href="vouchers.php">🎫 Vouchers</a>
        <a href="payments.php">💳 Payments</a>
        <a href="pppoe.php">🌐 PPPoE</a>
        <a href="router_wizard.php">🧙 Router Wizard</a>
        <a href="router_health.php">🩺 Health Check</a>
        <a href="reports.php">📈 Reports</a>
        <a href="analytics.php">📊 Analytics</a>
        <a href="backups.php">🛡 Backups</a>
        <a href="users.php">👤 Users</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="connection">
        <b>Router Connection</b><br><br>
        <span id="sideStatus" class="badge <?= $data["status"] ?>"><?= strtoupper($data["status"]) ?></span>
        <p id="sideRouter"><?= h($data["ip"]) ?>:<?= h($data["port"]) ?></p>
        <small>Auto refresh every 5 seconds</small>
    </div>
</aside>

<main class="main">

<div class="topbar">
    <div class="title">
        <h1>Live NOC Dashboard</h1>
        <p>Real-time MikroTik monitoring, live clients, queues, health and bandwidth.</p>
    </div>
    <div>
        <span id="statusBadge" class="badge <?= $data["status"] ?>"><?= strtoupper($data["status"]) ?></span>
        <b id="clock"></b>
    </div>
</div>

<div class="hero-stats">
    <div class="hero-card"><h4>Router Identity</h4><h2 id="identity"><?= h($data["identity"]) ?></h2><small id="board"><?= h($data["board"]) ?></small></div>
    <div class="hero-card"><h4>CPU Load</h4><h2 id="cpu"><?= h($data["cpu"]) ?>%</h2><small>Live system usage</small><canvas id="cpuPie"></canvas></div>
    <div class="hero-card"><h4>Download RX</h4><h2 id="rx"><?= h($data["rx"]) ?> Mbps</h2><small>WAN ether1</small></div>
    <div class="hero-card"><h4>Upload TX</h4><h2 id="tx"><?= h($data["tx"]) ?> Mbps</h2><small>WAN ether1</small></div>
    <div class="hero-card"><h4>Hotspot Online</h4><h2 id="hotspotCount"><?= count($data["hotspot"]) ?></h2><small>Active hotspot sessions</small></div>
    <div class="hero-card"><h4>PPPoE Online</h4><h2 id="pppoeCount"><?= count($data["pppoe"]) ?></h2><small>Active PPP sessions</small></div>
    <div class="hero-card"><h4>Simple Queues</h4><h2 id="queueCount"><?= count($data["queues"]) ?></h2><small>Bandwidth rules</small></div>
    <div class="hero-card"><h4>Router Uptime</h4><h2 id="uptime"><?= h($data["uptime"]) ?></h2><small id="updated">Updated: <?= h($data["updated"]) ?></small></div>
</div>
</div>

<div class="grid">
    <div class="card">
        <h2>Bandwidth Monitor</h2>
        <div class="chartbox"><canvas id="trafficChart"></canvas></div>
    </div>

    <div class="card">
        <h2>Client Distribution</h2>
        <canvas id="clientPie"></canvas>
    </div>

    <div class="card">
        <h2>MikroTik Health</h2>
        <div class="row"><span>CPU</span><b id="healthCpu">0%</b></div>
        <div class="row"><span>Memory Free</span><b id="memory">-</b></div>
        <div class="row"><span>Board</span><b id="healthBoard">-</b></div>
        <div class="row"><span>Version</span><b id="version">-</b></div>
        <div class="row"><span>Status</span><b id="healthStatus">Online</b></div>
    </div>
</div>

<div class="two">
    <div class="card">
        <h2>ISP Operations Center</h2>
        <div class="row"><span>Hotspot Gateway</span><span id="hotspotGateway" class="badge online">ONLINE</span></div>
        <div class="row"><span>M-Pesa Gateway</span><span class="badge warning">SANDBOX</span></div>
        <div class="row"><span>MikroTik API</span><span id="apiStatus" class="badge online">ACTIVE</span></div>
        <div class="row"><span>Auto Expiry Engine</span><span class="badge online">READY</span></div>
        <div class="row"><span>Synced Hotspot Users</span><b id="syncedUsers">0</b></div>
        <div class="row"><span>Online Hotspot Users</span><b id="onlineUsers">0</b></div>
        <div class="row"><span>Router Identity</span><b id="routerIdentity">MikroTik</b></div>
    </div>

    <div class="card">
        <h2>Quick Actions</h2>
        <div class="quick">
            <a href="clients.php">👥 Add / View Clients</a>
            <a href="packages.php">📦 Packages</a>
            <a href="vouchers.php">🎫 Vouchers</a>
            <a href="payments.php">💳 Payments</a>
            <a href="routers.php">🛰 Routers</a>
            <a href="reports.php">📈 Reports</a>
            <a href="pppoe.php">🌐 PPPoE Users</a>
            <a href="router_health.php">🩺 Health</a>
        </div>
    </div>
</div>

<div class="two">
    <div class="card">
        <h2>Online Hotspot Users</h2>
        <input class="search" onkeyup="filterTable('hotspotTable',this.value)" placeholder="Search hotspot users...">
        <table id="hotspotTable">
            <thead><tr><th>User</th><th>IP</th><th>MAC</th><th>Uptime</th><th>Download</th><th>Upload</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card">
        <h2>Online PPPoE Users</h2>
        <input class="search" onkeyup="filterTable('pppoeTable',this.value)" placeholder="Search PPPoE users...">
        <table id="pppoeTable">
            <thead><tr><th>User</th><th>IP</th><th>Caller ID</th><th>Uptime</th><th>Service</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="two">
    <div class="card">
        <h2>Active Simple Queues</h2>
        <input class="search" onkeyup="filterTable('queueTable',this.value)" placeholder="Search queues...">
        <table id="queueTable">
            <thead><tr><th>Name</th><th>Target</th><th>Max Limit</th><th>Bytes</th><th>Status</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card">
        <h2>Known DHCP Clients</h2>
        <input class="search" onkeyup="filterTable('leaseTable',this.value)" placeholder="Search DHCP clients...">
        <table id="leaseTable">
            <thead><tr><th>IP</th><th>MAC</th><th>Host Name</th><th>Status</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="two">
    <div class="card">
        <h2>Router Interfaces</h2>
        <table id="ifaceTable">
            <thead><tr><th>Name</th><th>Type</th><th>Running</th><th>Disabled</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card">
        <h2>System Information</h2>
        <div class="row"><span>Router IP</span><b id="sysIp"><?= h($data["ip"]) ?></b></div>
        <div class="row"><span>Router Port</span><b><?= h($data["port"]) ?></b></div>
        <div class="row"><span>Board</span><b id="sysBoard">-</b></div>
        <div class="row"><span>RouterOS Version</span><b id="sysVersion">-</b></div>
        <div class="row"><span>Last Update</span><b id="sysUpdated">-</b></div>
    </div>
</div>

<div class="pro-footer">
    <div class="footer-top">
        <div class="footer-brand">
            <h2>Hakim Networks</h2>
            <p>Professional ISP Billing, MikroTik Monitoring, Hotspot Control and NOC Operations.</p>
        </div>
        <div class="footer-links">
            <a href="users.php">👤 Profile / Users</a>
            <a href="change_password.php">🔐 Change Password</a>
            <a href="settings.php">⚙️ Settings</a>
            <a href="company_profile.php">🏢 Company Logo</a>
            <a href="router_wizard.php">🧙 Router Wizard</a>
            <a href="router_health.php">🩺 Health Check</a>
            <a href="reports.php">📈 Reports</a>
            <a href="analytics.php">📊 Analytics</a>
            <a href="clients.php">👥 Clients</a>
            <a href="packages.php">📦 Packages</a>
            <a href="vouchers.php">🎫 Vouchers</a>
            <a href="payments.php">💳 Payments</a>
            <a href="backups.php">🛡 Backups</a>
            <a href="routers.php">🛰 Routers</a>
            <a href="pppoe.php">🌐 PPPoE</a>
            <a class="logout" href="logout.php">🚪 Logout</a>
        </div>
        <div class="support-box">
            <h3>Support</h3>
            <p>Use Router Wizard for setup, Health Check for diagnosis, and Reports for business tracking.</p>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© 2026 Hakim Networks. All rights reserved.</span>
        <span>Live NOC • Secure Admin • MikroTik API Connected</span>
    </div>
</div>

</main>
</div>

<script>
let rxData=[], txData=[], labels=[];

const trafficChart = new Chart(document.getElementById("trafficChart"),{
    type:"line",
    data:{
        labels:labels,
        datasets:[
            {label:"Download RX",data:rxData,borderWidth:3,tension:.4},
            {label:"Upload TX",data:txData,borderWidth:3,tension:.4}
        ]
    },
    options:{responsive:true,maintainAspectRatio:false}
});

const cpuPie = new Chart(document.getElementById("cpuPie"),{
    type:"doughnut",
    data:{labels:["Used","Free"],datasets:[{data:[0,100]}]},
    options:{plugins:{legend:{display:false}},cutout:"70%"}
});

const clientPie = new Chart(document.getElementById("clientPie"),{
    type:"pie",
    data:{
        labels:["Hotspot","PPPoE","DHCP"],
        datasets:[{data:[0,0,0]}]
    }
});

function esc(v){return String(v ?? "-").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m]));}
function bytes(v){
    v=Number(v||0);
    if(v>=1073741824)return(v/1073741824).toFixed(2)+" GB";
    if(v>=1048576)return(v/1048576).toFixed(2)+" MB";
    if(v>=1024)return(v/1024).toFixed(2)+" KB";
    return v+" B";
}
function filterTable(id,q){
    q=q.toLowerCase();
    document.querySelectorAll("#"+id+" tbody tr").forEach(r=>{
        r.style.display=r.innerText.toLowerCase().includes(q)?"":"none";
    });
}
function rows(id,html){
    document.querySelector("#"+id+" tbody").innerHTML=html || `<tr><td colspan="10">No live records found</td></tr>`;
}
function clock(){
    document.getElementById("clock").textContent=new Date().toLocaleTimeString();
}
setInterval(clock,1000); clock();

async function loadLive(){
    const res = await fetch("pro_noc_v2.php?api=1&_="+Date.now());
    const d = await res.json();

    document.getElementById("statusBadge").textContent=d.status.toUpperCase();
    document.getElementById("statusBadge").className="badge "+d.status;
    document.getElementById("sideStatus").textContent=d.status.toUpperCase();
    document.getElementById("sideStatus").className="badge "+d.status;

    document.getElementById("identity").textContent=d.identity;
    document.getElementById("cpu").textContent=d.cpu+"%";
    document.getElementById("rx").textContent=d.rx+" Mbps";
    document.getElementById("tx").textContent=d.tx+" Mbps";
    document.getElementById("hotspotCount").textContent=d.hotspot.length;
    document.getElementById("pppoeCount").textContent=d.pppoe.length;
    document.getElementById("queueCount").textContent=d.queues.length;
    document.getElementById("uptime").textContent=d.uptime;
    document.getElementById("updated").textContent="Updated: "+d.updated;

    document.getElementById("healthCpu").textContent=d.cpu+"%";
    document.getElementById("memory").textContent=bytes(d.free_memory);
    document.getElementById("healthBoard").textContent=d.board;
    document.getElementById("version").textContent=d.version;
    document.getElementById("healthStatus").textContent=d.status;
    document.getElementById("routerIdentity").textContent=d.identity;
    document.getElementById("syncedUsers").textContent=d.hotspot.length;
    document.getElementById("onlineUsers").textContent=d.hotspot.length;
    document.getElementById("sysBoard").textContent=d.board;
    document.getElementById("sysVersion").textContent=d.version;
    document.getElementById("sysUpdated").textContent=d.updated;

    labels.push(d.updated); rxData.push(d.rx); txData.push(d.tx);
    if(labels.length>12){labels.shift();rxData.shift();txData.shift();}
    trafficChart.update();

    cpuPie.data.datasets[0].data=[Number(d.cpu),100-Number(d.cpu)];
    cpuPie.update();

    clientPie.data.datasets[0].data=[d.hotspot.length,d.pppoe.length,d.leases.length];
    clientPie.update();

    rows("hotspotTable",d.hotspot.map(u=>`
        <tr>
            <td><span class="pill good">${esc(u.user)}</span></td>
            <td>${esc(u.address)}</td>
            <td>${esc(u["mac-address"])}</td>
            <td>${esc(u.uptime)}</td>
            <td>${bytes(u["bytes-out"])}</td>
            <td>${bytes(u["bytes-in"])}</td>
        </tr>`).join(""));

    rows("pppoeTable",d.pppoe.map(u=>`
        <tr>
            <td><span class="pill good">${esc(u.name)}</span></td>
            <td>${esc(u.address)}</td>
            <td>${esc(u["caller-id"])}</td>
            <td>${esc(u.uptime)}</td>
            <td>${esc(u.service)}</td>
        </tr>`).join(""));

    rows("queueTable",d.queues.map(q=>`
        <tr>
            <td>${esc(q.name)}</td>
            <td>${esc(q.target)}</td>
            <td><span class="pill">${esc(q["max-limit"])}</span></td>
            <td>${esc(q.bytes)}</td>
            <td>${q.disabled==="true" ? `<span class="pill bad">Disabled</span>` : `<span class="pill good">Active</span>`}</td>
        </tr>`).join(""));

    rows("leaseTable",d.leases.map(l=>`
        <tr>
            <td>${esc(l.address)}</td>
            <td>${esc(l["mac-address"])}</td>
            <td>${esc(l["host-name"])}</td>
            <td><span class="pill">${esc(l.status)}</span></td>
        </tr>`).join(""));

    rows("ifaceTable",d.interfaces.map(i=>`
        <tr>
            <td>${esc(i.name)}</td>
            <td>${esc(i.type)}</td>
            <td>${i.running==="true" ? `<span class="pill good">Running</span>` : `<span class="pill bad">Down</span>`}</td>
            <td>${i.disabled==="true" ? `<span class="pill bad">Disabled</span>` : `<span class="pill good">Enabled</span>`}</td>
        </tr>`).join(""));
}

loadLive();
setInterval(loadLive,5000);
</script>

</body>
</html>
