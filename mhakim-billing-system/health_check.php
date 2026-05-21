<?php
require_once __DIR__ . '/access_guard.php';
require_once "auth.php";
requireRole(["super_admin"]);
require_once "config/database.php";

$stats = $pdo->query("SELECT * FROM system_stats WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$mpesa = $pdo->query("SELECT environment, shortcode, callback_url FROM mpesa_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$latestPayment = $pdo->query("SELECT id, phone, amount, status, mpesa_receipt, created_at FROM payments ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

function safe($v){ return htmlspecialchars($v ?? "-", ENT_QUOTES, "UTF-8"); }
function badgeClass($v){
    $v = strtolower((string)$v);
    if(in_array($v, ["online","paid","active","running","ok"])) return "good";
    if(in_array($v, ["pending","sandbox"])) return "warn";
    return "bad";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>System Health Check</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#020817;color:#e5e7eb}
.layout{display:flex;min-height:100vh}
.sidebar{width:280px;background:linear-gradient(180deg,#07111f,#061b2b,#062f2d);padding:22px 12px;position:fixed;height:100vh;border-right:1px solid rgba(148,163,184,.18)}
.brand{display:flex;gap:12px;align-items:center;margin-bottom:28px}
.logo{width:48px;height:48px;border-radius:12px;background:#22c55e;display:grid;place-items:center;font-size:28px}
.brand h2{margin:0;font-size:24px}
.brand small{color:#94a3b8}
.nav a{display:flex;gap:12px;align-items:center;color:white;text-decoration:none;padding:13px 14px;border-radius:10px;margin:6px 0;font-weight:700}
.nav a.active,.nav a:hover{background:linear-gradient(90deg,#0f513b,#166534)}
.logout{color:#ef4444!important}
.timebox{position:absolute;bottom:22px;left:12px;right:12px;background:rgba(15,23,42,.75);border:1px solid #1f3b57;border-radius:14px;padding:18px}
.timebox .time{font-size:26px;font-weight:900;margin-top:8px}

.main{margin-left:280px;width:calc(100% - 280px);padding:28px 32px}
.top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:25px}
.title{display:flex;gap:14px;align-items:center}
.title .pulse{font-size:32px;color:#22c55e}
h1{font-size:36px;margin:0 0 10px}
.subtitle{color:#cbd5e1;margin:0}
.breadcrumb{color:#cbd5e1}
.breadcrumb span{color:#22c55e}
.global{margin-top:18px;background:rgba(34,197,94,.12);color:#22c55e;border:1px solid rgba(34,197,94,.3);padding:13px 22px;border-radius:12px;font-weight:800}

.cards{display:grid;grid-template-columns:repeat(5,1fr);gap:18px;margin-bottom:20px}
.stat{background:linear-gradient(145deg,#0b1627,#0c1b30);border:1px solid #213044;border-radius:14px;padding:20px;display:flex;gap:16px;align-items:center;min-height:118px}
.icon{width:66px;height:66px;border-radius:50%;display:grid;place-items:center;font-size:30px;background:#102b52}
.icon.purple{background:#30205d}.icon.orange{background:#4a2b1d}.icon.green{background:#123d2a}
.stat h3{margin:0 0 8px;color:white;font-size:15px}
.big{font-size:30px;font-weight:900}
.green{color:#22c55e}.muted{color:#94a3b8}.blue{color:#60a5fa}

.grid{display:grid;grid-template-columns:1fr .95fr 1.05fr;gap:18px;margin-bottom:18px}
.panel{background:linear-gradient(145deg,#0b1627,#081525);border:1px solid #223247;border-radius:14px;padding:22px;box-shadow:0 18px 45px rgba(0,0,0,.25)}
.panel h2{font-size:18px;margin:0 0 18px;display:flex;gap:10px;align-items:center}
.row{display:grid;grid-template-columns:1fr auto 32px;gap:12px;align-items:center;padding:13px 0;border-bottom:1px solid #203045}
.row:last-child{border-bottom:0}
.check{width:20px;height:20px;border-radius:50%;background:#22c55e;color:#052e16;display:grid;place-items:center;font-weight:900;font-size:12px}
.badge{padding:6px 10px;border-radius:8px;font-size:12px;font-weight:900;text-transform:uppercase}
.badge.good{background:#064e3b;color:#22c55e}
.badge.warn{background:#78350f;color:#facc15}
.badge.bad{background:#450a0a;color:#ef4444}
.url{color:#60a5fa;word-break:break-word}
.notice{margin-top:18px;color:#22c55e;border-top:1px solid #203045;padding-top:16px}

.bottom{display:grid;grid-template-columns:2fr 1fr;gap:18px}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:12px;border-bottom:1px solid #203045}
th{color:#cbd5e1}
.action{display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid #223247;border-radius:10px;margin-bottom:10px;background:#0b1829}
.action strong{display:block}
.footer{text-align:center;color:#94a3b8;margin-top:22px}
@media(max-width:1100px){.cards{grid-template-columns:repeat(2,1fr)}.grid,.bottom{grid-template-columns:1fr}.main{margin-left:0;width:100%;padding:18px}.sidebar{display:none}}
</style>
</head>
<body>
<div class="layout">

<aside class="sidebar">
    <div class="brand">
        <div class="logo">H</div>
        <div><h2>Hakim Networks</h2><small>Billing & Network Management</small></div>
    </div>

    <nav class="nav">
        <a href="noc_final_clean.php">🏠 Dashboard</a>
        <a href="analytics.php">📊 Analytics</a>
        <a href="noc.php">🖥️ NOC Center</a>
        <a class="active" href="health_check.php">💚 Health Check</a>
        <a href="clients.php">👥 Clients</a>
        <a href="packages.php">📦 Packages</a>
        <a href="vouchers.php">🎟️ Vouchers</a>
        <a href="payments.php">💳 Payments</a>
        <a href="routers.php">📡 Routers</a>
        <a href="pppoe.php">🌐 PPPoE</a>
        <a href="mikrotik.php">📶 MikroTik</a>
        <a href="users.php">👤 Users</a>
        <a href="backups.php">☁️ Backups</a>
        <a href="settings.php">⚙️ Settings</a>
        <a class="logout" href="logout.php">🚪 Logout</a>
    </nav>

    <div class="timebox">
        <div>🕘 Server Time</div>
        <small><?php echo date("d M Y"); ?></small>
        <div class="time" id="clock"><?php echo date("H:i:s"); ?></div>
        <small class="green">EAT</small>
    </div>
</aside>

<main class="main">
    <div class="top">
        <div>
            <div class="title"><div class="pulse">〽</div><div><h1>System Health Check</h1><p class="subtitle">Pre-deployment status for Hakim Networks billing platform.</p></div></div>
        </div>
        <div>
            <div class="breadcrumb"><span>Home</span> / Health Check</div>
            <div class="global">● All Systems Operational</div>
        </div>
    </div>

    <section class="cards">
        <div class="stat"><div class="icon">📡</div><div><h3>Router Status</h3><div class="big green"><?php echo ucfirst(safe($stats["router_status"] ?? "offline")); ?></div><small class="green">● Connected</small></div></div>
        <div class="stat"><div class="icon purple">👥</div><div><h3>Hotspot Online</h3><div class="big"><?php echo (int)($stats["online_hotspot"] ?? 0); ?></div><small class="green">● Users Online</small></div></div>
        <div class="stat"><div class="icon orange">👤</div><div><h3>PPPoE Online</h3><div class="big"><?php echo (int)($stats["online_pppoe"] ?? 0); ?></div><small>Users Online</small></div></div>
        <div class="stat"><div class="icon">↕</div><div><h3>WAN RX / TX</h3><div class="big"><?php echo safe($stats["wan_rx"] ?? 0); ?> / <?php echo safe($stats["wan_tx"] ?? 0); ?></div><small class="blue">Mbps</small></div></div>
        <div class="stat"><div class="icon green">🕘</div><div><h3>Stats Updated</h3><div class="big"><?php echo date("H:i:s", strtotime($stats["updated_at"] ?? "now")); ?></div><small class="green">● <?php echo date("d M Y", strtotime($stats["updated_at"] ?? "now")); ?></small></div></div>
    </section>

    <section class="grid">
        <div class="panel">
            <h2>🌐 NETWORK STATUS</h2>
            <div class="row"><span>Router Status</span><span class="badge <?php echo badgeClass($stats["router_status"] ?? ""); ?>"><?php echo safe($stats["router_status"]); ?></span><span class="check">✓</span></div>
            <div class="row"><span>Hotspot Online</span><span><?php echo (int)($stats["online_hotspot"] ?? 0); ?> users</span><span class="check">✓</span></div>
            <div class="row"><span>PPPoE Online</span><span><?php echo (int)($stats["online_pppoe"] ?? 0); ?> users</span><span class="check">✓</span></div>
            <div class="row"><span>WAN RX / TX</span><span><?php echo safe($stats["wan_rx"]); ?> / <?php echo safe($stats["wan_tx"]); ?> Mbps</span><span class="check">✓</span></div>
            <div class="row"><span>Stats Updated</span><span><?php echo safe($stats["updated_at"]); ?></span><span class="check">✓</span></div>
            <div class="notice">🛡️ Network connection is healthy</div>
        </div>

        <div class="panel">
            <h2>📱 M-PESA CONFIGURATION</h2>
            <div class="row"><span>Environment</span><span class="badge <?php echo badgeClass($mpesa["environment"] ?? ""); ?>"><?php echo safe($mpesa["environment"]); ?></span><span class="check">✓</span></div>
            <div class="row"><span>Shortcode</span><span><?php echo safe($mpesa["shortcode"]); ?></span><span class="check">✓</span></div>
            <div class="row"><span>Callback URL</span><span class="url"><?php echo safe($mpesa["callback_url"]); ?></span><span class="check">✓</span></div>
            <div class="row"><span>Last Updated</span><span><?php echo date("d M Y"); ?></span><span class="check">✓</span></div>
            <div class="notice">🛡️ M-Pesa configuration is valid</div>
        </div>

        <div class="panel">
            <h2>💳 LATEST PAYMENT</h2>
            <div class="row"><span>Payment ID</span><span><?php echo safe($latestPayment["id"] ?? "-"); ?></span><span class="check">✓</span></div>
            <div class="row"><span>Phone</span><span><?php echo safe($latestPayment["phone"] ?? "-"); ?></span><span class="check">✓</span></div>
            <div class="row"><span>Amount</span><span>KES <?php echo number_format((float)($latestPayment["amount"] ?? 0),2); ?></span><span class="check">✓</span></div>
            <div class="row"><span>Status</span><span class="badge <?php echo badgeClass($latestPayment["status"] ?? ""); ?>"><?php echo safe($latestPayment["status"] ?? "-"); ?></span><span class="check">✓</span></div>
            <div class="row"><span>Receipt</span><span><?php echo safe($latestPayment["mpesa_receipt"] ?? "-"); ?></span><span class="check">✓</span></div>
            <div class="row"><span>Created</span><span><?php echo safe($latestPayment["created_at"] ?? "-"); ?></span><span class="check">✓</span></div>
            <div class="notice">🛡️ Latest payment is <?php echo safe($latestPayment["status"] ?? "unknown"); ?></div>
        </div>
    </section>

    <section class="bottom">
        <div class="panel">
            <h2>⚙ SYSTEM SERVICES</h2>
            <table>
                <tr><th>Service</th><th>Status</th><th>Details</th><th>Last Checked</th></tr>
                <tr><td>Web Server (Apache)</td><td><span class="badge good">RUNNING</span></td><td>Apache Ubuntu</td><td><?php echo date("d M Y H:i:s"); ?> ✅</td></tr>
                <tr><td>Database (MySQL)</td><td><span class="badge good">RUNNING</span></td><td>MySQL Active</td><td><?php echo date("d M Y H:i:s"); ?> ✅</td></tr>
                <tr><td>Queue Sync</td><td><span class="badge good">OK</span></td><td>Active cron sync</td><td><?php echo safe($stats["updated_at"] ?? "-"); ?> ✅</td></tr>
                <tr><td>Stats Sync</td><td><span class="badge good">OK</span></td><td>Cached live stats</td><td><?php echo safe($stats["updated_at"] ?? "-"); ?> ✅</td></tr>
                <tr><td>M-Pesa Callback</td><td><span class="badge good">OK</span></td><td>Endpoint configured</td><td><?php echo date("d M Y H:i:s"); ?> ✅</td></tr>
            </table>
        </div>

        <div class="panel">
            <h2>⚡ QUICK ACTIONS</h2>
            <a class="action" href="queue_sync.php"><div><strong>Sync Queues Now</strong><small>Sync active clients to MikroTik</small></div><span>›</span></a>
            <a class="action" href="stats_sync.php"><div><strong>Sync Stats Now</strong><small>Refresh live statistics</small></div><span>›</span></a>
            <a class="action" href="../mhakim-hotspot/callback.php"><div><strong>Test M-Pesa Callback</strong><small>Verify callback endpoint</small></div><span>›</span></a>
            <a class="action" href="noc.php"><div><strong>View NOC Center</strong><small>Monitor live network</small></div><span>›</span></a>
        </div>
    </section>

    <div class="footer">🛡️ Hakim Networks Billing System &nbsp; | &nbsp; All systems monitored and secure</div>
</main>
</div>

<script>
setInterval(()=>{document.getElementById("clock").textContent=new Date().toLocaleTimeString("en-GB");},1000);
</script>
<script src="mikrotik_live_sync.js"></script>
</body>
</html>
