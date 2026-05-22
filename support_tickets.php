<?php require_once __DIR__ . "/config/database.php"; ?>
<!DOCTYPE html>
<html>
<head>
<title>Support Tickets</title>
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
.btn{background:#16a34a;color:white;padding:11px 16px;border-radius:12px;text-decoration:none;font-weight:900;display:inline-block}
table{width:100%;border-collapse:collapse}
th{background:#020617;color:white;text-align:left;padding:13px}
td{padding:13px;border-bottom:1px solid #e5e7eb}
@media(max-width:900px){.main{margin-left:0}.sidebar{display:none}.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="sidebar">
<h2>M.Hakim</h2>
<a href="noc_final_clean.php">Live NOC</a>
<a href="modules.php">Modules</a>
<a href="client_crm.php">Client CRM</a>
<a href="hotspot_manager.php">Hotspot</a>
<a href="queue_control.php">Queues</a>
<a href="network_health_center.php">Health</a>
<a href="finance_center.php">Finance</a>
<a href="installations.php">Installations</a>
<a href="support_tickets.php">Tickets</a>
<a href="coverage_zones.php">Coverage</a>
<a href="notifications_center.php">Alerts</a>
<a href="backup_center.php">Backups</a>
</div>

<div class="main">
<h1>Support Tickets</h1>
<p>Hakim Networks professional ISP module.</p>

<div class="grid">
<div class="stat"><small>Status</small><h2>Active</h2></div>
<div class="stat"><small>Router Sync</small><h2 data-mk="status">Live</h2></div>
<div class="stat"><small>Last Update</small><h2 id="lastUpdate">-</h2></div>
<div class="stat"><small>System</small><h2>Ready</h2></div>
</div>

<div class="card">
<h2>Support Tickets Records</h2>
<p>This module is installed and ready for full workflow customization.</p>
<table>
<tr><th>Name</th><th>Status</th><th>Action</th></tr>
<tr><td>Support Tickets</td><td>Ready</td><td><a class="btn" href="modules.php">Back</a></td></tr>
</table>
</div>
</div>

<script>
setInterval(()=>document.getElementById("lastUpdate").innerText=new Date().toLocaleTimeString(),1000);
</script>
<script src="mikrotik_live_sync.js"></script>
</body>
</html>