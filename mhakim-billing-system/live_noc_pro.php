<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }
function mbps($v){ return round(((float)$v)/1000000, 2); }
function qone($pdo,$sql,$fallback=0){ try{return $pdo->query($sql)->fetchColumn() ?: $fallback;}catch(Exception $e){return $fallback;} }

function liveData($pdo){
    $router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $d = [
        "status"=>"offline","error"=>null,"router"=>$router,
        "identity"=>"-","board"=>"-","version"=>"-","cpu"=>0,"uptime"=>"-",
        "free_memory"=>0,"rx"=>0,"tx"=>0,
        "hotspot"=>[],"pppoe"=>[],"queues"=>[],"leases"=>[],"interfaces"=>[],
        "updated"=>date("H:i:s")
    ];

    try{
        if(!$router) throw new Exception("Router not configured");

        $client = new Client(new Config([
            "host"=>$router["router_ip"],
            "user"=>$router["username"],
            "pass"=>$router["password"],
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

        $r = $resource[0] ?? [];

        $d["status"] = "online";
        $d["identity"] = $identity[0]["name"] ?? "MikroTik";
        $d["board"] = $r["board-name"] ?? "-";
        $d["version"] = $r["version"] ?? "-";
        $d["cpu"] = (int)($r["cpu-load"] ?? 0);
        $d["uptime"] = $r["uptime"] ?? "-";
        $d["free_memory"] = $r["free-memory"] ?? 0;
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

    $d["business"] = [
        "revenue" => qone($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments",0),
        "clients" => qone($pdo,"SELECT COUNT(*) FROM clients",0),
        "packages" => qone($pdo,"SELECT COUNT(*) FROM packages",0),
        "vouchers" => qone($pdo,"SELECT COUNT(*) FROM vouchers",0),
        "used_vouchers" => qone($pdo,"SELECT COUNT(*) FROM vouchers WHERE status='used'",0),
        "unused_vouchers" => qone($pdo,"SELECT COUNT(*) FROM vouchers WHERE status='unused'",0),
        "tenants" => qone($pdo,"SELECT COUNT(*) FROM companies",0)
    ];

    return $d;
}

if(isset($_GET["api"])){
    header("Content-Type: application/json");
    echo json_encode(liveData($pdo));
    exit;
}

$d = liveData($pdo);
?>
<!DOCTYPE html>
<html>
<head>
<title>Hakim Networks Live NOC Pro</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:linear-gradient(135deg,#031827,#065f46);color:#0f172a}
.app{display:flex;min-height:100vh}
.sidebar{width:250px;background:#020617;color:white;position:fixed;top:0;bottom:0;left:0;padding:20px;overflow:auto}
.logo{font-size:25px;font-weight:900}.sub{color:#94a3b8;font-size:13px;margin-bottom:25px}
.nav a{display:block;color:white;text-decoration:none;background:#111827;padding:12px 14px;margin:8px 0;border-radius:13px;font-weight:700}
.nav a:hover,.nav a.active{background:#16a34a}
.sidebox{background:#111827;padding:15px;border-radius:16px;margin-top:20px}
.main{margin-left:250px;width:calc(100% - 250px);padding:24px}
.top{display:flex;justify-content:space-between;align-items:center;color:white;margin-bottom:20px}
.top h1{margin:0}.top p{margin:5px 0 0;color:#cbd5e1}
.badge{padding:7px 12px;border-radius:999px;font-size:12px;font-weight:900}.online{background:#dcfce7;color:#047857}.offline{background:#fee2e2;color:#b91c1c}.warn{background:#fef3c7;color:#92400e}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
.card,.section{background:white;border-radius:20px;padding:20px;box-shadow:0 15px 40px rgba(0,0,0,.15)}
.card h4{margin:0;color:#64748b}.card h2{margin:9px 0 0;font-size:28px}
.grid{display:grid;grid-template-columns:1.4fr 1fr;gap:18px;margin-bottom:18px}
.grid3{display:grid;grid-template-columns:1.2fr .9fr .9fr;gap:18px;margin-bottom:18px}
.section{margin-bottom:18px;overflow:auto}
.section h2{margin-top:0}
.row{display:flex;justify-content:space-between;border-bottom:1px solid #e5e7eb;padding:10px 0}
.quick{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.quick a{text-decoration:none;color:#0f172a;background:#f8fafc;border:1px solid #dbeafe;border-radius:14px;padding:15px;font-weight:900}
.quick a:hover{background:#dcfce7}
.search{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:12px;margin-bottom:12px}
table{width:100%;border-collapse:collapse;min-width:720px}
th{background:#020617;color:white;text-align:left;padding:11px}
td{border-bottom:1px solid #e5e7eb;padding:10px;font-size:13px}
.pill{padding:5px 10px;border-radius:999px;font-size:12px;font-weight:900;background:#e0f2fe;color:#0369a1}
.good{background:#dcfce7;color:#166534}.bad{background:#fee2e2;color:#991b1b}
.footer{background:#020617;color:white;border-radius:22px;padding:24px;margin-top:20px}
.footer-grid{display:grid;grid-template-columns:1fr 2fr 1fr;gap:18px}
.footer-links{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.footer-links a{color:white;text-decoration:none;background:#111827;padding:12px;border-radius:12px;font-weight:800;font-size:13px}
.footer-links a:hover{background:#16a34a}.footer-links a.logout:hover{background:#dc2626}
canvas{max-height:260px}
@media(max-width:1000px){.sidebar{position:relative;width:100%;height:auto}.main{margin-left:0;width:100%}.app{display:block}.kpis{grid-template-columns:repeat(2,1fr)}.grid,.grid3,.footer-grid{grid-template-columns:1fr}.footer-links{grid-template-columns:repeat(2,1fr)}}
@media(max-width:650px){.kpis,.footer-links{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="app">
<aside class="sidebar">
    <div class="logo">📶 M.Hakim</div>
    <div class="sub">Hakim Networks Pro NOC</div>
    <div class="nav">
        <a class="active" href="live_noc_pro.php">📡 Live NOC</a>
        <a href="noc_final_clean.php">📊 Dashboard</a>
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
        <a href="settings.php">⚙️ Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
    <div class="sidebox">
        <b>Router Connection</b><br><br>
        <span id="sideStatus" class="badge <?=h($d["status"])?>"><?=strtoupper(h($d["status"]))?></span>
        <p><?=h($d["router"]["router_ip"] ?? "-")?>:<?=h($d["router"]["router_port"] ?? "8728")?></p>
        <small>Auto refresh every 5 seconds</small>
    </div>
</aside>

<main class="main">
<div class="top">
    <div><h1>Live NOC Dashboard</h1><p>Real-time MikroTik, clients, queues, bandwidth and business performance.</p></div>
    <div><span id="status" class="badge <?=h($d["status"])?>"><?=strtoupper(h($d["status"]))?></span> <b id="clock"></b></div>
</div>

<div class="kpis">
    <div class="card"><h4>Router Identity</h4><h2 id="identity"><?=h($d["identity"])?></h2><small id="board"><?=h($d["board"])?></small></div>
    <div class="card"><h4>CPU Load</h4><h2 id="cpu"><?=h($d["cpu"])?>%</h2></div>
    <div class="card"><h4>Download RX</h4><h2 id="rx"><?=h($d["rx"])?> Mbps</h2><small>WAN ether1</small></div>
    <div class="card"><h4>Upload TX</h4><h2 id="tx"><?=h($d["tx"])?> Mbps</h2><small>WAN ether1</small></div>
    <div class="card"><h4>Hotspot Online</h4><h2 id="hotspotCount"><?=count($d["hotspot"])?></h2></div>
    <div class="card"><h4>PPPoE Online</h4><h2 id="pppoeCount"><?=count($d["pppoe"])?></h2></div>
    <div class="card"><h4>Simple Queues</h4><h2 id="queueCount"><?=count($d["queues"])?></h2></div>
    <div class="card"><h4>Router Uptime</h4><h2 id="uptime"><?=h($d["uptime"])?></h2></div>
</div>

<div class="grid3">
    <div class="section"><h2>Bandwidth Monitor</h2><canvas id="trafficChart"></canvas></div>
    <div class="section"><h2>Client Distribution</h2><canvas id="clientPie"></canvas></div>
    <div class="section"><h2>MikroTik Health</h2>
        <div class="row"><span>CPU Load</span><b id="healthCpu">0%</b></div>
        <div class="row"><span>Memory Free</span><b id="memory">-</b></div>
        <div class="row"><span>Board</span><b id="healthBoard">-</b></div>
        <div class="row"><span>RouterOS</span><b id="version">-</b></div>
        <div class="row"><span>Status</span><span id="healthStatus" class="badge online">ONLINE</span></div>
    </div>
</div>

<div class="grid">
    <div class="section"><h2>ISP Operations Center</h2>
        <div class="row"><span>Hotspot Gateway</span><span class="badge online">ONLINE</span></div>
        <div class="row"><span>M-Pesa Gateway</span><span class="badge warn">SANDBOX</span></div>
        <div class="row"><span>MikroTik API</span><span id="apiStatus" class="badge online">ACTIVE</span></div>
        <div class="row"><span>Auto Expiry Engine</span><span class="badge online">READY</span></div>
        <div class="row"><span>Online Hotspot Users</span><b id="onlineUsers">0</b></div>
        <div class="row"><span>Router Identity</span><b id="routerIdentity"><?=h($d["identity"])?></b></div>
    </div>
    <div class="section"><h2>Quick Actions</h2>
        <div class="quick">
            <a href="clients.php">👥 Clients</a><a href="packages.php">📦 Packages</a>
            <a href="vouchers.php">🎫 Vouchers</a><a href="payments.php">💳 Payments</a>
            <a href="routers.php">🛰 Routers</a><a href="reports.php">📈 Reports</a>
            <a href="router_health.php">🩺 Health</a><a href="users.php">👤 Users</a>
        </div>
    </div>
</div>

<div class="grid">
    <div class="section"><h2>Revenue Analytics</h2><canvas id="revenueChart"></canvas></div>
    <div class="section"><h2>Voucher Status</h2><canvas id="voucherPie"></canvas></div>
</div>

<div class="section"><h2>Business Performance</h2>
    <div class="kpis">
        <div class="card"><h4>Total Revenue</h4><h2 id="bizRevenue">Ksh <?=h($d["business"]["revenue"])?></h2></div>
        <div class="card"><h4>Total Clients</h4><h2 id="bizClients"><?=h($d["business"]["clients"])?></h2></div>
        <div class="card"><h4>Packages</h4><h2 id="bizPackages"><?=h($d["business"]["packages"])?></h2></div>
        <div class="card"><h4>Vouchers</h4><h2 id="bizVouchers"><?=h($d["business"]["vouchers"])?></h2></div>
    </div>
</div>

<div class="section"><h2>Online Hotspot Users</h2><input class="search" onkeyup="filterTable('hotspotTable',this.value)" placeholder="Search hotspot users"><table id="hotspotTable"><thead><tr><th>User</th><th>IP</th><th>MAC</th><th>Uptime</th><th>Download</th><th>Upload</th></tr></thead><tbody></tbody></table></div>

<div class="section"><h2>Online PPPoE Users</h2><input class="search" onkeyup="filterTable('pppoeTable',this.value)" placeholder="Search PPPoE users"><table id="pppoeTable"><thead><tr><th>User</th><th>IP</th><th>Caller ID</th><th>Uptime</th><th>Service</th></tr></thead><tbody></tbody></table></div>

<div class="section"><h2>Active Simple Queues</h2><input class="search" onkeyup="filterTable('queueTable',this.value)" placeholder="Search queues"><table id="queueTable"><thead><tr><th>Name</th><th>Target</th><th>Max Limit</th><th>Bytes</th><th>Status</th></tr></thead><tbody></tbody></table></div>

<div class="section"><h2>Known DHCP Clients</h2><input class="search" onkeyup="filterTable('leaseTable',this.value)" placeholder="Search DHCP clients"><table id="leaseTable"><thead><tr><th>IP</th><th>MAC</th><th>Host Name</th><th>Status</th></tr></thead><tbody></tbody></table></div>

<div class="section"><h2>Router Interfaces</h2><table id="ifaceTable"><thead><tr><th>Name</th><th>Type</th><th>Running</th><th>Disabled</th></tr></thead><tbody></tbody></table></div>

<div class="footer">
    <div class="footer-grid">
        <div><h2>Hakim Networks</h2><p>Professional ISP NOC and MikroTik monitoring dashboard.</p></div>
        <div class="footer-links">
            <a href="users.php">👤 Profile</a><a href="change_password.php">🔐 Password</a>
            <a href="settings.php">⚙️ Settings</a><a href="company_profile.php">🏢 Logo</a>
            <a href="router_wizard.php">🧙 Wizard</a><a href="router_health.php">🩺 Health</a>
            <a href="reports.php">📈 Reports</a><a href="analytics.php">📊 Analytics</a>
            <a href="backups.php">🛡 Backups</a><a href="payments.php">💳 Payments</a>
            <a href="clients.php">👥 Clients</a><a href="logout.php" class="logout">🚪 Logout</a>
        </div>
        <div><h3>Support</h3><p>Use Router Wizard, Health Check, Reports and Backups for support shortcuts.</p></div>
    </div>
</div>

</main>
</div>

<script>
let labels=[],rxData=[],txData=[];
const trafficChart=new Chart(document.getElementById("trafficChart"),{type:"line",data:{labels,datasets:[{label:"Download RX",data:rxData,borderWidth:3,tension:.4},{label:"Upload TX",data:txData,borderWidth:3,tension:.4}]},options:{responsive:true}});
const clientPie=new Chart(document.getElementById("clientPie"),{type:"doughnut",data:{labels:["Hotspot","PPPoE","DHCP"],datasets:[{data:[0,0,0]}]}});
const voucherPie=new Chart(document.getElementById("voucherPie"),{type:"doughnut",data:{labels:["Unused","Used"],datasets:[{data:[0,0]}]}});
const revenueChart=new Chart(document.getElementById("revenueChart"),{type:"line",data:{labels:["Revenue"],datasets:[{label:"Revenue Ksh",data:[0],fill:true,borderWidth:3,tension:.4}]}});
function esc(v){return String(v??"-").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m]));}
function bytes(v){v=Number(v||0);if(v>=1073741824)return(v/1073741824).toFixed(2)+" GB";if(v>=1048576)return(v/1048576).toFixed(2)+" MB";if(v>=1024)return(v/1024).toFixed(2)+" KB";return v+" B";}
function rows(id,html){document.querySelector("#"+id+" tbody").innerHTML=html||`<tr><td colspan="10">No live records found</td></tr>`;}
function filterTable(id,q){q=q.toLowerCase();document.querySelectorAll("#"+id+" tbody tr").forEach(r=>r.style.display=r.innerText.toLowerCase().includes(q)?"":"none");}
setInterval(()=>clock.textContent=new Date().toLocaleTimeString(),1000);
async function live(){
    const r=await fetch("live_noc_pro.php?api=1&_="+Date.now()); const d=await r.json();
    status.textContent=d.status.toUpperCase(); status.className="badge "+d.status;
    sideStatus.textContent=d.status.toUpperCase(); sideStatus.className="badge "+d.status;
    identity.textContent=d.identity; board.textContent=d.board; cpu.textContent=d.cpu+"%"; rx.textContent=d.rx+" Mbps"; tx.textContent=d.tx+" Mbps"; uptime.textContent=d.uptime;
    hotspotCount.textContent=d.hotspot.length; pppoeCount.textContent=d.pppoe.length; queueCount.textContent=d.queues.length;
    healthCpu.textContent=d.cpu+"%"; memory.textContent=bytes(d.free_memory); healthBoard.textContent=d.board; version.textContent=d.version; routerIdentity.textContent=d.identity; onlineUsers.textContent=d.hotspot.length;
    bizRevenue.textContent="Ksh "+d.business.revenue; bizClients.textContent=d.business.clients; bizPackages.textContent=d.business.packages; bizVouchers.textContent=d.business.vouchers;
    labels.push(d.updated);rxData.push(d.rx);txData.push(d.tx);if(labels.length>12){labels.shift();rxData.shift();txData.shift();}trafficChart.update();
    clientPie.data.datasets[0].data=[d.hotspot.length,d.pppoe.length,d.leases.length]; clientPie.update();
    voucherPie.data.datasets[0].data=[d.business.unused_vouchers,d.business.used_vouchers]; voucherPie.update();
    revenueChart.data.datasets[0].data=[d.business.revenue]; revenueChart.update();
    rows("hotspotTable",d.hotspot.map(u=>`<tr><td><span class="pill good">${esc(u.user)}</span></td><td>${esc(u.address)}</td><td>${esc(u["mac-address"])}</td><td>${esc(u.uptime)}</td><td>${bytes(u["bytes-out"])}</td><td>${bytes(u["bytes-in"])}</td></tr>`).join(""));
    rows("pppoeTable",d.pppoe.map(u=>`<tr><td><span class="pill good">${esc(u.name)}</span></td><td>${esc(u.address)}</td><td>${esc(u["caller-id"])}</td><td>${esc(u.uptime)}</td><td>${esc(u.service)}</td></tr>`).join(""));
    rows("queueTable",d.queues.map(q=>`<tr><td>${esc(q.name)}</td><td>${esc(q.target)}</td><td><span class="pill">${esc(q["max-limit"])}</span></td><td>${esc(q.bytes)}</td><td>${q.disabled==="true"?`<span class="pill bad">Disabled</span>`:`<span class="pill good">Active</span>`}</td></tr>`).join(""));
    rows("leaseTable",d.leases.map(l=>`<tr><td>${esc(l.address)}</td><td>${esc(l["mac-address"])}</td><td>${esc(l["host-name"])}</td><td><span class="pill">${esc(l.status)}</span></td></tr>`).join(""));
    rows("ifaceTable",d.interfaces.map(i=>`<tr><td>${esc(i.name)}</td><td>${esc(i.type)}</td><td>${i.running==="true"?`<span class="pill good">Running</span>`:`<span class="pill bad">Down</span>`}</td><td>${i.disabled==="true"?`<span class="pill bad">Disabled</span>`:`<span class="pill good">Enabled</span>`}</td></tr>`).join(""));
}
live(); setInterval(live,5000);
</script>

<script>
async function loadNocApi(){
  try{
    const r = await fetch("live_noc_api.php?t=" + Date.now(), {cache:"no-store"});
    const d = await r.json();

    const text = document.body.innerHTML;

    if(d.status === "online"){
      document.body.innerHTML = document.body.innerHTML
        .replaceAll("OFFLINE","ONLINE")
        .replaceAll("Offline","Online")
        .replaceAll("offline","online")
        .replace(/Router Identity[\s\S]*?<\\/div>/i, match => match.replace("-", d.identity || "MikroTik"));

      document.querySelectorAll("*").forEach(el=>{
        if(el.innerText && el.innerText.trim()==="0%" && d.cpu !== undefined){
          el.innerText = d.cpu + "%";
        }
        if(el.innerText && el.innerText.trim()==="0 B" && d.memory_free !== undefined){
          el.innerText = d.memory_free + " B";
        }
        if(el.innerText && el.innerText.trim()==="-"){
          if(el.previousElementSibling && el.previousElementSibling.innerText.includes("RouterOS")){
            el.innerText = d.version || "-";
          }
        }
      });
    }
  }catch(e){
    console.log("Live NOC API error", e);
  }
}
loadNocApi();
setInterval(loadNocApi,5000);
</script>

<script src="mikrotik_live_sync.js"></script>
</body>
</html>
