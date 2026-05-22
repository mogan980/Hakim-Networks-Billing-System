<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function clean($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function mbps($bits){ return round(((float)$bits) / 1000000, 2); }
function fmtBytes($bytes){
    $bytes = (float)$bytes;
    if ($bytes >= 1073741824) return round($bytes/1073741824,2)." GB";
    if ($bytes >= 1048576) return round($bytes/1048576,2)." MB";
    if ($bytes >= 1024) return round($bytes/1024,2)." KB";
    return $bytes." B";
}

function getLiveData($pdo){
    $router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $out = [
        "ok"=>false,
        "error"=>null,
        "router"=>$router,
        "identity"=>"-",
        "status"=>"offline",
        "cpu"=>0,
        "uptime"=>"-",
        "free_memory"=>0,
        "board"=>"-",
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
        if(!$router) throw new Exception("No router configured.");

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

        $out["ok"] = true;
        $out["status"] = "online";
        $out["identity"] = $identity[0]["name"] ?? "MikroTik";
        $out["cpu"] = $resource[0]["cpu-load"] ?? 0;
        $out["uptime"] = $resource[0]["uptime"] ?? "-";
        $out["free_memory"] = $resource[0]["free-memory"] ?? 0;
        $out["board"] = $resource[0]["board-name"] ?? "-";
        $out["rx"] = mbps($traffic[0]["rx-bits-per-second"] ?? 0);
        $out["tx"] = mbps($traffic[0]["tx-bits-per-second"] ?? 0);
        $out["hotspot"] = $hotspot;
        $out["pppoe"] = $pppoe;
        $out["queues"] = $queues;
        $out["leases"] = $leases;
        $out["interfaces"] = $ifaces;

    }catch(Exception $e){
        $out["error"] = $e->getMessage();
    }

    return $out;
}

if(isset($_GET["api"])){
    header("Content-Type: application/json");
    echo json_encode(getLiveData($pdo));
    exit;
}

$data = getLiveData($pdo);
?>
<!DOCTYPE html>
<html>
<head>
<title>Hakim Networks Pro NOC</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Inter,Arial,sans-serif;
    background:linear-gradient(135deg,#061126,#0f766e,#e8f7ff);
    color:#071427;
}
.app{display:flex;min-height:100vh}
.sidebar{
    width:260px;
    background:rgba(2,8,23,.95);
    color:white;
    padding:24px;
    position:fixed;
    height:100vh;
}
.logo{
    font-size:26px;
    font-weight:900;
    margin-bottom:4px;
}
.sub{color:#94a3b8;font-size:13px}
.nav{margin-top:35px}
.nav a{
    display:block;
    color:white;
    text-decoration:none;
    padding:13px 14px;
    margin:8px 0;
    border-radius:14px;
    font-weight:700;
    background:rgba(255,255,255,.04);
}
.nav a.active,.nav a:hover{background:#16a34a}
.main{
    margin-left:260px;
    padding:28px;
    width:calc(100% - 260px);
}
.hero{
    background:rgba(255,255,255,.92);
    border-radius:28px;
    padding:24px;
    box-shadow:0 20px 60px rgba(0,0,0,.18);
    display:flex;
    justify-content:space-between;
    align-items:center;
    animation:fade .5s ease;
}
h1{margin:0;font-size:30px}
.badge{
    padding:9px 15px;
    border-radius:30px;
    font-weight:900;
    font-size:12px;
}
.online{background:#dcfce7;color:#047857}
.offline{background:#fee2e2;color:#b91c1c}
.grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-top:22px;
}
.card{
    background:rgba(255,255,255,.94);
    border-radius:24px;
    padding:22px;
    box-shadow:0 12px 35px rgba(0,0,0,.12);
    animation:up .45s ease;
}
.card h3{margin:0;color:#64748b;font-size:14px}
.card h2{margin:12px 0 0;font-size:30px}
.section{
    background:rgba(255,255,255,.95);
    border-radius:24px;
    padding:22px;
    margin-top:22px;
    box-shadow:0 12px 35px rgba(0,0,0,.12);
}
table{width:100%;border-collapse:collapse;overflow:hidden;border-radius:16px}
th{
    background:#020617;
    color:white;
    text-align:left;
    padding:13px;
}
td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
}
tr:hover{background:#f0fdf4}
.search{
    width:100%;
    padding:14px;
    border:1px solid #cbd5e1;
    border-radius:14px;
    margin-bottom:14px;
    font-size:15px;
}
.pill{
    display:inline-block;
    padding:5px 10px;
    border-radius:999px;
    background:#e0f2fe;
    color:#0369a1;
    font-weight:800;
    font-size:12px;
}
.warn{background:#fef3c7;color:#92400e}
.good{background:#dcfce7;color:#166534}
.footer{color:white;text-align:center;margin-top:30px}
@keyframes fade{from{opacity:0}to{opacity:1}}
@keyframes up{from{transform:translateY(12px);opacity:0}to{transform:translateY(0);opacity:1}}
@media(max-width:1000px){
    .sidebar{position:relative;width:100%;height:auto}
    .main{margin-left:0;width:100%}
    .app{display:block}
    .grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:650px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="logo">M.Hakim</div>
        <div class="sub">Hakim Networks Pro NOC</div>
        <div class="nav">
            <a class="active" href="#">📡 Live NOC</a>
            <a href="noc_final_clean.php">📊 Old Dashboard</a>
            <a href="routers.php">🛰 Routers</a>
            <a href="clients.php">👥 Clients</a>
            <a href="vouchers.php">🎫 Vouchers</a>
            <a href="payments.php">💳 Payments</a>
        </div>
    </aside>

    <main class="main">
        <div class="hero">
            <div>
                <h1>Professional Live MikroTik NOC</h1>
                <p>Real-time router health, online users, queues, leases, and traffic.</p>
            </div>
            <div>
                <span id="statusBadge" class="badge <?= $data["status"] ?>"><?= strtoupper($data["status"]) ?></span>
            </div>
        </div>

        <div class="grid">
            <div class="card"><h3>Router Identity</h3><h2 id="identity"><?= clean($data["identity"]) ?></h2></div>
            <div class="card"><h3>CPU Load</h3><h2 id="cpu"><?= clean($data["cpu"]) ?>%</h2></div>
            <div class="card"><h3>Download RX</h3><h2 id="rx"><?= clean($data["rx"]) ?> Mbps</h2></div>
            <div class="card"><h3>Upload TX</h3><h2 id="tx"><?= clean($data["tx"]) ?> Mbps</h2></div>
            <div class="card"><h3>Hotspot Online</h3><h2 id="hotspotCount"><?= count($data["hotspot"]) ?></h2></div>
            <div class="card"><h3>PPPoE Online</h3><h2 id="pppoeCount"><?= count($data["pppoe"]) ?></h2></div>
            <div class="card"><h3>Simple Queues</h3><h2 id="queueCount"><?= count($data["queues"]) ?></h2></div>
            <div class="card"><h3>Router Uptime</h3><h2 id="uptime"><?= clean($data["uptime"]) ?></h2></div>
        </div>

        <div class="section">
            <h2>Online Hotspot Users</h2>
            <input class="search" onkeyup="filterTable('hotspotTable',this.value)" placeholder="Search online hotspot users...">
            <table id="hotspotTable">
                <thead><tr><th>User</th><th>IP</th><th>MAC</th><th>Uptime</th><th>Download</th><th>Upload</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="section">
            <h2>Online PPPoE Users</h2>
            <table id="pppoeTable">
                <thead><tr><th>User</th><th>IP</th><th>Caller ID</th><th>Uptime</th><th>Service</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="section">
            <h2>Bandwidth Queues / Speed Limits</h2>
            <input class="search" onkeyup="filterTable('queueTable',this.value)" placeholder="Search queues, clients, IPs...">
            <table id="queueTable">
                <thead><tr><th>Name</th><th>Target</th><th>Max Limit</th><th>Bytes</th><th>Status</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="section">
            <h2>Known DHCP Clients</h2>
            <input class="search" onkeyup="filterTable('leaseTable',this.value)" placeholder="Search known clients...">
            <table id="leaseTable">
                <thead><tr><th>IP</th><th>MAC</th><th>Host Name</th><th>Status</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="section">
            <h2>Router Interfaces</h2>
            <table id="ifaceTable">
                <thead><tr><th>Name</th><th>Type</th><th>Running</th><th>Disabled</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="footer">Hakim Networks NOC • Auto-refreshing every 5 seconds</div>
    </main>
</div>

<script>
function esc(v){return String(v ?? "-").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m]));}
function bytes(v){
    v = Number(v || 0);
    if(v >= 1073741824) return (v/1073741824).toFixed(2)+" GB";
    if(v >= 1048576) return (v/1048576).toFixed(2)+" MB";
    if(v >= 1024) return (v/1024).toFixed(2)+" KB";
    return v+" B";
}
function filterTable(id,q){
    q=q.toLowerCase();
    document.querySelectorAll("#"+id+" tbody tr").forEach(r=>{
        r.style.display = r.innerText.toLowerCase().includes(q) ? "" : "none";
    });
}
function rows(id, html){
    document.querySelector("#"+id+" tbody").innerHTML = html || `<tr><td colspan="10">No live records found</td></tr>`;
}
async function loadLive(){
    try{
        const res = await fetch("pro_live_noc.php?api=1&_="+Date.now());
        const d = await res.json();

        const badge = document.getElementById("statusBadge");
        badge.textContent = d.status.toUpperCase();
        badge.className = "badge " + d.status;

        document.getElementById("identity").textContent = d.identity;
        document.getElementById("cpu").textContent = d.cpu + "%";
        document.getElementById("rx").textContent = d.rx + " Mbps";
        document.getElementById("tx").textContent = d.tx + " Mbps";
        document.getElementById("hotspotCount").textContent = d.hotspot.length;
        document.getElementById("pppoeCount").textContent = d.pppoe.length;
        document.getElementById("queueCount").textContent = d.queues.length;
        document.getElementById("uptime").textContent = d.uptime;

        rows("hotspotTable", d.hotspot.map(u=>`
            <tr>
                <td><span class="pill good">${esc(u.user)}</span></td>
                <td>${esc(u.address)}</td>
                <td>${esc(u["mac-address"])}</td>
                <td>${esc(u.uptime)}</td>
                <td>${bytes(u["bytes-out"])}</td>
                <td>${bytes(u["bytes-in"])}</td>
            </tr>
        `).join(""));

        rows("pppoeTable", d.pppoe.map(u=>`
            <tr>
                <td><span class="pill good">${esc(u.name)}</span></td>
                <td>${esc(u.address)}</td>
                <td>${esc(u["caller-id"])}</td>
                <td>${esc(u.uptime)}</td>
                <td>${esc(u.service)}</td>
            </tr>
        `).join(""));

        rows("queueTable", d.queues.map(q=>`
            <tr>
                <td>${esc(q.name)}</td>
                <td>${esc(q.target)}</td>
                <td><span class="pill">${esc(q["max-limit"])}</span></td>
                <td>${esc(q.bytes)}</td>
                <td>${q.disabled === "true" ? `<span class="pill warn">Disabled</span>` : `<span class="pill good">Active</span>`}</td>
            </tr>
        `).join(""));

        rows("leaseTable", d.leases.map(l=>`
            <tr>
                <td>${esc(l.address)}</td>
                <td>${esc(l["mac-address"])}</td>
                <td>${esc(l["host-name"])}</td>
                <td>${esc(l.status)}</td>
            </tr>
        `).join(""));

        rows("ifaceTable", d.interfaces.map(i=>`
            <tr>
                <td>${esc(i.name)}</td>
                <td>${esc(i.type)}</td>
                <td>${i.running === "true" ? `<span class="pill good">Running</span>` : `<span class="pill warn">Down</span>`}</td>
                <td>${i.disabled === "true" ? `<span class="pill warn">Disabled</span>` : `<span class="pill good">Enabled</span>`}</td>
            </tr>
        `).join(""));

    }catch(e){
        console.log(e);
    }
}
loadLive();
setInterval(loadLive, 5000);
</script>
<script src="mikrotik_live_sync.js"></script>
</body>
</html>
