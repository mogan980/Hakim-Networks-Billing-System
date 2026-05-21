<?php
require_once __DIR__ . '/../access_guard.php';
?>
<!DOCTYPE html>
<html>
<head><title>Network</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="/mhakim-billing-system/tenant/assets/pro.css">
</head>
<body>
<?php include __DIR__ . "/tenant_layout.php"; ?>

<div class="main">
<div class="hero">
<h1>Network Operations Center</h1>
<p>Live MikroTik health, hotspot users and queue monitoring.</p>
</div>

<div class="grid">
<div class="card"><h3>Router Status</h3><strong id="status">Checking...</strong></div>
<div class="card"><h3>CPU Load</h3><strong id="cpu">0%</strong></div>
<div class="card"><h3>Hotspot Users</h3><strong id="users">0</strong></div>
<div class="card"><h3>Queues</h3><strong id="queues">0</strong></div>
</div>


<div class="info-grid">

<div class="info-box">
<h4>Router Health</h4>
<strong id="healthBox">LIVE</strong>
</div>

<div class="info-box">
<h4>WAN Traffic</h4>
<strong id="trafficBox">Monitoring</strong>
</div>

<div class="info-box">
<h4>Queue Engine</h4>
<strong id="queueBox">Ready</strong>
</div>

<div class="info-box">
<h4>Tenant Isolation</h4>
<strong>SECURE</strong>
</div>

</div>

<div class="panel" style="margin-top:24px;">
<h2>Router Details</h2>
<table>
<tr><th>Router</th><td id="router">-</td></tr>
<tr><th>Board</th><td id="board">-</td></tr>
<tr><th>Uptime</th><td id="uptime">-</td></tr>
<tr><th>Memory</th><td id="memory">-</td></tr>
<tr><th>Last Sync</th><td id="sync">-</td></tr>
</table>
</div>
</div>

<script>
async function loadNetwork(){
    try{
        const d = await (await fetch("tenant_router_live.php?t="+Date.now(),{cache:"no-store"})).json();

        status.innerText = d.status === "online" ? "ONLINE" : "OFFLINE";
        cpu.innerText = (d.cpu_load || 0) + "%";
        users.innerText = d.active_hotspot_users || 0;
        queues.innerText = d.simple_queues || 0;
        router.innerText = d.router_name || "-";
        board.innerText = d.board || "-";
        uptime.innerText = d.uptime || "-";
        memory.innerText = d.free_memory || "-";
        sync.innerText = d.updated_at || "-";
    }catch(e){}
}
loadNetwork();
setInterval(loadNetwork,10000);
</script>
</body>
</html>
