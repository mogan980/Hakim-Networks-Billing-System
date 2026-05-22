<?php require_once __DIR__ . "/config/database.php"; ?>
<!DOCTYPE html>
<html>
<head>
<title>Queue Control</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;font-family:Arial;background:#eaf0f5;color:#020617}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020b1a;color:white;padding:20px}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;border-radius:10px;margin:6px 0;font-weight:800}
.sidebar a:hover,.active{background:#064e3b}
.main{margin-left:260px;padding:35px}
.card{background:white;border-radius:22px;padding:24px;margin-bottom:22px;box-shadow:0 18px 45px rgba(15,23,42,.09)}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.stat{background:#f8fafc;border:1px solid #e2e8f0;border-radius:18px;padding:18px}
.stat h2{margin:5px 0 0}
.badge{display:inline-block;background:#dcfce7;color:#166534;padding:7px 12px;border-radius:999px;font-weight:900;font-size:12px}
table{width:100%;border-collapse:collapse}
th{background:#020617;color:white;text-align:left;padding:13px}
td{padding:13px;border-bottom:1px solid #e5e7eb}
.btn{background:#16a34a;color:white;padding:11px 16px;border-radius:12px;text-decoration:none;font-weight:900}
@media(max-width:900px){.main{margin-left:0}.sidebar{display:none}.grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<a href="noc_final_clean.php">Live NOC</a>
<a href="modules.php">Modules</a>
<a href="hotspot_manager.php">Hotspot</a>
<a href="queue_control.php">Queues</a>
<a href="network_health_center.php">Health</a>
<a href="pppoe_manager.php">PPPoE</a>
<a href="traffic_analytics.php">Traffic</a>
<a href="topology_map.php">Topology</a>
</div>

<div class="main">
<h1>Queue Control</h1>
<p>Live MikroTik connected module.</p>

<div class="grid">
<div class="stat"><small>Router Status</small><h2 id="routerStatus">Loading</h2></div>
<div class="stat"><small>Router Identity</small><h2 id="routerIdentity">-</h2></div>
<div class="stat"><small>Total Records</small><h2 id="recordCount">0</h2></div>
<div class="stat"><small>Last Update</small><h2 id="lastUpdate">-</h2></div>
</div>

<div class="card">
<h2>Live Data</h2>
<table id="liveTable">
<tr><th>Name</th><th>Target</th><th>Max Limit</th><th>Disabled</th><th>Bytes</th></tr>
<tr><td colspan="10">Loading MikroTik data...</td></tr>
</table>
<br>
<a class="btn" href="javascript:loadModule()">Refresh</a>
</div>
</div>

<script>
const MODULE_API = "queues";

async function loadModule(){
    const r = await fetch("phase3_live_api.php?module=" + MODULE_API + "&t=" + Date.now(), {cache:"no-store"});
    const d = await r.json();

    document.getElementById("routerStatus").innerText = d.status || "offline";
    document.getElementById("routerIdentity").innerText = d.identity || "-";
    document.getElementById("recordCount").innerText = d.count || 0;
    document.getElementById("lastUpdate").innerText = d.updated_at || "-";

    const table = document.getElementById("liveTable");
    table.innerHTML = d.html || "<tr><th>Name</th><th>Target</th><th>Max Limit</th><th>Disabled</th><th>Bytes</th></tr><tr><td colspan='10'>No data</td></tr>";
}
loadModule();
setInterval(loadModule,5000);
</script>
<script src="mikrotik_actions.js"></script>
</body>
</html>