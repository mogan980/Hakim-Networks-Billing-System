<?php ?>
<!DOCTYPE html>
<html>
<head>
<title>Hakim Networks Modules</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;font-family:Arial;background:#eaf0f5;color:#020617}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020b1a;color:white;padding:20px}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;border-radius:10px;margin:6px 0;font-weight:800}
.sidebar a:hover,.active{background:#064e3b}
.main{margin-left:260px;padding:35px}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.card{background:white;border-radius:22px;padding:24px;box-shadow:0 18px 45px rgba(15,23,42,.09);border:1px solid #e5e7eb}
.card h3{margin:0 0 10px}
.card p{color:#64748b}
.badge{display:inline-block;background:#dcfce7;color:#166534;padding:7px 12px;border-radius:999px;font-weight:900;font-size:12px}
@media(max-width:1000px){.grid{grid-template-columns:1fr}.main{margin-left:0}.sidebar{display:none}}
</style>
</head>
<body>
<div class="sidebar">
<h2>M.Hakim</h2>
<a href="noc_final_clean.php">Live NOC</a>
<a href="routers.php">Routers</a>
<a class="active" href="modules.php">Modules</a>
<a href="clients.php">Clients</a>
<a href="payments.php">Payments</a>
</div>

<div class="main">
<h1>Hakim Networks Pro Modules</h1>
<p>Professional ISP operation modules installed and ready for Phase 2 pages.</p>

<div class="grid">

<div class="card"><h3>Open Phase 2 Pages</h3>
<p>Launch the new working modules.</p>
<a class="badge" href="client_crm.php">Client CRM</a>
<a class="badge" href="hotspot_manager.php">Hotspot Manager</a>
<a class="badge" href="queue_control.php">Queue Control</a>
<a class="badge" href="network_health_center.php">Health Center</a>
<a class="badge" href="finance_center.php">Finance</a>
<a class="badge" href="installations.php">Installations</a>
<a class="badge" href="support_tickets.php">Tickets</a>
<a class="badge" href="coverage_zones.php">Coverage</a>
<a class="badge" href="notifications_center.php">Notifications</a>
<a class="badge" href="backup_center.php">Backups</a>
</div>

<?php
$mods = [
"Client CRM"=>"Subscribers, status, expiry and notes.",
"Auto Expiry Engine"=>"Disable expired users automatically.",
"Hotspot Manager"=>"Sessions, kick, pause and device control.",
"Queue Control"=>"Speed limits, burst and priorities.",
"Topology Map"=>"MikroTik → switches → APs → clients.",
"Health Center"=>"CPU, uptime, ping and outage alerts.",
"Finance & Expenses"=>"Revenue, costs and profit tracking.",
"Installations"=>"Technician and install request workflow.",
"SMS / WhatsApp"=>"Payment, expiry and outage messages.",
"PPPoE Manager"=>"PPPoE users and active sessions.",
"Roles & Staff"=>"Admin, cashier, technician and NOC roles.",
"Backups"=>"Router and database backup records.",
"Notifications"=>"Real-time system alerts.",
"Coverage Zones"=>"AP zones and expansion planning.",
"Client Portal"=>"Self-care portal foundation.",
"Traffic Analytics"=>"Per-client and router traffic history.",
"Support Tickets"=>"Client complaints and resolutions."
];

foreach($mods as $name=>$desc){
echo "<div class='card'><span class='badge'>Installed</span><h3>$name</h3><p>$desc</p></div>";
}
?>
</div>
</div>
</body>
</html>
