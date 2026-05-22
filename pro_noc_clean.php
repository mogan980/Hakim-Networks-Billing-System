<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function mbps($v){ return round(((float)$v)/1000000, 2); }

function getLive($pdo){
    $router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $d = [
        "status"=>"offline","error"=>null,"identity"=>"-","board"=>"-","version"=>"-",
        "cpu"=>0,"uptime"=>"-","rx"=>0,"tx"=>0,"free_memory"=>0,
        "hotspot"=>[],"pppoe"=>[],"queues"=>[],"leases"=>[],"interfaces"=>[],
        "router"=>$router,"updated"=>date("H:i:s")
    ];

    try{
        if(!$router) throw new Exception("Router not configured");

        $client = new Client(new Config([
            "host"=>$router["router_ip"],
            "user"=>$router["router_user"],
            "pass"=>$router["router_pass"],
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

        $d["status"]="online";
        $d["identity"]=$identity[0]["name"] ?? "MikroTik";
        $d["board"]=$res["board-name"] ?? "-";
        $d["version"]=$res["version"] ?? "-";
        $d["cpu"]=(int)($res["cpu-load"] ?? 0);
        $d["uptime"]=$res["uptime"] ?? "-";
        $d["free_memory"]=$res["free-memory"] ?? 0;
        $d["rx"]=mbps($traffic[0]["rx-bits-per-second"] ?? 0);
        $d["tx"]=mbps($traffic[0]["tx-bits-per-second"] ?? 0);
        $d["hotspot"]=$hotspot;
        $d["pppoe"]=$pppoe;
        $d["queues"]=$queues;
        $d["leases"]=$leases;
        $d["interfaces"]=$ifaces;

    }catch(Exception $e){
        $d["error"]=$e->getMessage();
    }

    return $d;
}

if(isset($_GET["api"])){
    header("Content-Type: application/json");
    echo json_encode(getLive($pdo));
    exit;
}

$d = getLive($pdo);
?>
<!DOCTYPE html>
<html>
<head>
<title>Hakim Networks Clean NOC</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#eef7f5;color:#08111f}
.layout{display:flex;min-height:100vh}
.sidebar{width:260px;background:#020617;color:white;padding:22px;position:fixed;left:0;top:0;bottom:0;overflow:auto}
.logo{font-size:28px;font-weight:900}
.sub{font-size:13px;color:#94a3b8;margin-bottom:25px}
.nav a{display:block;color:white;text-decoration:none;background:#111827;margin:9px 0;padding:13px;border-radius:13px;font-weight:700}
.nav a.active,.nav a:hover{background:#16a34a}
.sidebox{background:#111827;margin-top:25px;padding:15px;border-radius:16px;font-size:14px}
.main{margin-left:260px;width:calc(100% - 260px);padding:24px}
.header{background:linear-gradient(135deg,#020617,#064e3b);color:white;padding:24px;border-radius:22px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center}
.header h1{margin:0}
.badge{padding:8px 14px;border-radius:30px;font-size:12px;font-weight:900}
.online{background:#dcfce7;color:#047857}
.offline{background:#fee2e2;color:#b91c1c}
.warning{background:#fef3c7;color:#92400e}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.card{background:white;border-radius:20px;padding:20px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
.card h4{margin:0;color:#64748b;font-size:13px}
.card h2{margin:10px 0 0;font-size:28px}
.grid2{display:grid;grid-template-columns:1.4fr 1fr;gap:18px;margin-bottom:20px}
.section{background:white;border-radius:20px;padding:20px;box-shadow:0 10px 30px rgba(0,0,0,.08);margin-bottom:20px;overflow:auto}
.section h2{margin-top:0}
table{width:100%;border-collapse:collapse;min-width:720px}
th{background:#020617;color:white;text-align:left;padding:12px}
td{padding:11px;border-bottom:1px solid #e5e7eb;font-size:14px}
.search{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:12px;margin-bottom:12px}
.quick{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.quick a{text-decoration:none;color:#020617;background:#f8fafc;border:1px solid #dbeafe;border-radius:14px;padding:14px;font-weight:800}
.quick a:hover{background:#dcfce7}
.footer{background:#020617;color:white;border-radius:22px;padding:24px;margin-top:20px}
.footer-grid{display:grid;grid-template-columns:1fr 2fr 1fr;gap:18px}
.footer-links{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.footer-links a{color:white;text-decoration:none;background:#111827;padding:12px;border-radius:12px;font-size:13px;font-weight:800}
.footer-links a:hover{background:#16a34a}
.footer-links a.logout:hover{background:#dc2626}
canvas{max-height:260px}
@media(max-width:1000px){.sidebar{position:relative;width:100%;height:auto}.main{margin-left:0;width:100%}.layout{display:block}.cards{grid-template-columns:repeat(2,1fr)}.grid2,.footer-grid{grid-template-columns:1fr}.footer-links,.quick{grid-template-columns:repeat(2,1fr)}}
@media(max-width:650px){.cards,.quick,.footer-links{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="layout">

<aside class="sidebar">
    <div class="logo">📡 M.Hakim</div>
    <div class="sub">Hakim Networks Clean NOC</div>

    <div class="nav">
        <a class="active" href="pro_noc_clean.php">📡 Live NOC</a>
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

    <div class="sidebox">
        <b>Router Connection</b><br><br>
        <span id="sideStatus" class="badge <?=h($d["status"])?>"><?=strtoupper(h($d["status"]))?></span>
        <p><?=h($d["router"]["router_ip"] ?? "-")?>:<?=h($d["router"]["router_port"] ?? "8728")?></p>
    </div>
</aside>

<main class="main">

<div class="header">
    <div>
        <h1>Live NOC Dashboard</h1>
        <p>Clean real-time MikroTik monitoring, clients, queues and network health.</p>
    </div>
    <div>
        <span id="status" class="badge <?=h($d["status"])?>"><?=strtoupper(h($d["status"]))?></span>
        <b id="clock"></b>
    </div>
</div>

<div class="cards">
    <div class="card"><h4>Router Identity</h4><h2 id="identity"><?=h($d["identity"])?></h2><small id="board"><?=h($d["board"])?></small></div>
    <div class="card"><h4>CPU Load</h4><h2 id="cpu"><?=h($d["cpu"])?>%</h2></div>
    <div class="card"><h4>Download RX</h4><h2 id="rx"><?=h($d["rx"])?> Mbps</h2><small>WAN ether1</small></div>
    <div class="card"><h4>Upload TX</h4><h2 id="tx"><?=h($d["tx"])?> Mbps</h2><small>WAN ether1</small></div>
    <div class="card"><h4>Hotspot Online</h4><h2 id="hotspotCount"><?=count($d["hotspot"])?></h2></div>
    <div class="card"><h4>PPPoE Online</h4><h2 id="pppoeCount"><?=count($d["pppoe"])?></h2></div>
    <div class="card"><h4>Simple Queues</h4><h2 id="queueCount"><?=count($d["queues"])?></h2></div>
    <div class="card"><h4>Router Uptime</h4><h2 id="uptime"><?=h($d["uptime"])?></h2></div>
</div>

<div class="grid2">
    <div class="section">
        <h2>Bandwidth Monitor</h2>
        <canvas id="trafficChart"></canvas>
    </div>
    <div class="section">
        <h2>Client Distribution</h2>
        <canvas id="clientPie"></canvas>
    </div>
</div>

<div class="grid2">
    <div class="section">
        <h2>ISP Operations Center</h2>
        <p>MikroTik API: <span id="apiBadge" class="badge online">ACTIVE</span></p>
        <p>Auto Expiry Engine: <span class="badge online">READY</span></p>
        <p>Router Identity: <b id="routerIdentity"><?=h($d["identity"])?></b></p>
        <p>Last Updated: <b id="updated"><?=h($d["updated"])?></b></p>
    </div>

    <div class="section">
        <h2>Quick Actions</h2>
        <div class="quick">
            <a href="clients.php">👥 Clients</a>
            <a href="packages.php">📦 Packages</a>
            <a href="vouchers.php">🎫 Vouchers</a>
            <a href="payments.php">💳 Payments</a>
            <a href="routers.php">🛰 Routers</a>
            <a href="reports.php">📈 Reports</a>
            <a href="router_health.php">🩺 Health</a>
            <a href="users.php">👤 Users</a>
        </div>
    </div>
</div>

<div class="section">
    <h2>Online Hotspot Users</h2>
    <input class="search" onkeyup="filterTable('hotspotTable',this.value)" placeholder="Search hotspot users">
    <table id="hotspotTable"><thead><tr><th>User</th><th>IP</th><th>MAC</th><th>Uptime</th><th>Download</th><th>Upload</th></tr></thead><tbody></tbody></table>
</div>

<div class="section">
    <h2>Online PPPoE Users</h2>
    <input class="search" onkeyup="filterTable('pppoeTable',this.value)" placeholder="Search PPPoE users">
    <table id="pppoeTable"><thead><tr><th>User</th><th>IP</th><th>Caller ID</th><th>Uptime</th><th>Service</th></tr></thead><tbody></tbody></table>
</div>

<div class="section">
    <h2>Simple Queues / Speed Limits</h2>
    <input class="search" onkeyup="filterTable('queueTable',this.value)" placeholder="Search queues">
    <table id="queueTable"><thead><tr><th>Name</th><th>Target</th><th>Max Limit</th><th>Bytes</th><th>Status</th></tr></thead><tbody></tbody></table>
</div>

<div class="section">
    <h2>Known DHCP Clients</h2>
    <input class="search" onkeyup="filterTable('leaseTable',this.value)" placeholder="Search DHCP clients">
    <table id="leaseTable"><thead><tr><th>IP</th><th>MAC</th><th>Host Name</th><th>Status</th></tr></thead><tbody></tbody></table>
</div>

<div class="section">
    <h2>Router Interfaces</h2>
    <table id="ifaceTable"><thead><tr><th>Name</th><th>Type</th><th>Running</th><th>Disabled</th></tr></thead><tbody></tbody></table>
</div>

<div class="footer">
    <div class="footer-grid">
        <div>
            <h2>Hakim Networks</h2>
            <p>Professional ISP NOC, MikroTik monitoring and hotspot control.</p>
        </div>
        <div class="footer-links">
            <a href="users.php">👤 Profile</a>
            <a href="change_password.php">🔐 Password</a>
            <a href="settings.php">⚙️ Settings</a>
            <a href="company_profile.php">🏢 Logo</a>
            <a href="router_wizard.php">🧙 Wizard</a>
            <a href="router_health.php">🩺 Health</a>
            <a href="reports.php">📈 Reports</a>
            <a href="analytics.php">📊 Analytics</a>
            <a href="backups.php">🛡 Backups</a>
            <a href="payments.php">💳 Payments</a>
            <a href="clients.php">👥 Clients</a>
            <a href="logout.php" class="logout">🚪 Logout</a>
        </div>
        <div>
            <h3>Support</h3>
            <p>Use Router Wizard, Health Check, Backups and Reports for admin support.</p>
        </div>
    </div>
</div>

</main>
</div>

<script>
let labels=[],rxData=[],txData=[];
const trafficChart=new Chart(document.getElementById("trafficChart"),{type:"line",data:{labels,datasets:[{label:"Download RX",data:rxData,borderWidth:3,tension:.4},{label:"Upload TX",data:txData,borderWidth:3,tension:.4}]},options:{responsive:true}});
const clientPie=new Chart(document.getElementById("clientPie"),{type:"doughnut",data:{labels:["Hotspot","PPPoE","DHCP"],datasets:[{data:[0,0,0]}]}});

function esc(v){return String(v??"-").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m]));}
function bytes(v){v=Number(v||0);if(v>=1073741824)return(v/1073741824).toFixed(2)+" GB";if(v>=1048576)return(v/1048576).toFixed(2)+" MB";if(v>=1024)return(v/1024).toFixed(2)+" KB";return v+" B";}
function rows(id,html){document.querySelector("#"+id+" tbody").innerHTML=html||`<tr><td colspan="10">No live records found</td></tr>`;}
function filterTable(id,q){q=q.toLowerCase();document.querySelectorAll("#"+id+" tbody tr").forEach(r=>r.style.display=r.innerText.toLowerCase().includes(q)?"":"none");}
setInterval(()=>document.getElementById("clock").textContent=new Date().toLocaleTimeString(),1000);

async function live(){
    const r=await fetch("pro_noc_clean.php?api=1&_="+Date.now());
    const d=await r.json();

    document.getElementById("status").textContent=d.status.toUpperCase();
    document.getElementById("status").className="badge "+d.status;
    document.getElementById("sideStatus").textContent=d.status.toUpperCase();
    document.getElementById("sideStatus").className="badge "+d.status;

    identity.textContent=d.identity; board.textContent=d.board; cpu.textContent=d.cpu+"%";
    rx.textContent=d.rx+" Mbps"; tx.textContent=d.tx+" Mbps"; uptime.textContent=d.uptime;
    hotspotCount.textContent=d.hotspot.length; pppoeCount.textContent=d.pppoe.length; queueCount.textContent=d.queues.length;
    routerIdentity.textContent=d.identity; updated.textContent=d.updated;

    labels.push(d.updated);rxData.push(d.rx);txData.push(d.tx);
    if(labels.length>12){labels.shift();rxData.shift();txData.shift();}
    trafficChart.update();

    clientPie.data.datasets[0].data=[d.hotspot.length,d.pppoe.length,d.leases.length];
    clientPie.update();

    rows("hotspotTable",d.hotspot.map(u=>`<tr><td>${esc(u.user)}</td><td>${esc(u.address)}</td><td>${esc(u["mac-address"])}</td><td>${esc(u.uptime)}</td><td>${bytes(u["bytes-out"])}</td><td>${bytes(u["bytes-in"])}</td></tr>`).join(""));
    rows("pppoeTable",d.pppoe.map(u=>`<tr><td>${esc(u.name)}</td><td>${esc(u.address)}</td><td>${esc(u["caller-id"])}</td><td>${esc(u.uptime)}</td><td>${esc(u.service)}</td></tr>`).join(""));
    rows("queueTable",d.queues.map(q=>`<tr><td>${esc(q.name)}</td><td>${esc(q.target)}</td><td>${esc(q["max-limit"])}</td><td>${esc(q.bytes)}</td><td>${q.disabled==="true"?"Disabled":"Active"}</td></tr>`).join(""));
    rows("leaseTable",d.leases.map(l=>`<tr><td>${esc(l.address)}</td><td>${esc(l["mac-address"])}</td><td>${esc(l["host-name"])}</td><td>${esc(l.status)}</td></tr>`).join(""));
    rows("ifaceTable",d.interfaces.map(i=>`<tr><td>${esc(i.name)}</td><td>${esc(i.type)}</td><td>${i.running==="true"?"Running":"Down"}</td><td>${i.disabled==="true"?"Disabled":"Enabled"}</td></tr>`).join(""));
}
live();setInterval(live,5000);
</script>
</body>
</html>
