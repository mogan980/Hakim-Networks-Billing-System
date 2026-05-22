<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$name = $_SESSION["tenant_name"] ?? "Tenant";
?>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:linear-gradient(135deg,#020617,#071827,#052e2b);color:white}
.sidebar{width:270px;height:100vh;position:fixed;left:0;top:0;background:linear-gradient(180deg,#020617,#071827,#052e2b);padding:22px;border-right:1px solid rgba(34,197,94,.18);overflow:auto}
.logo{width:58px;height:58px;border-radius:18px;background:linear-gradient(135deg,#22c55e,#86efac);color:#052e16;display:grid;place-items:center;font-size:28px;font-weight:900;margin-bottom:14px}
.sidebar h2{margin:0}.sidebar p{color:#94a3b8;font-size:13px}
.sidebar a{display:block;color:white;text-decoration:none;padding:14px;border-radius:14px;margin:8px 0;background:rgba(15,23,42,.72);font-weight:800}
.sidebar a:hover,.sidebar a.active{background:#22c55e;color:#052e16}
.main{margin-left:290px;padding:30px}
.hero,.card,.panel{background:rgba(15,23,42,.88);border:1px solid rgba(34,197,94,.15);border-radius:26px;padding:24px;box-shadow:0 25px 80px rgba(0,0,0,.32)}
.hero h1,.panel h2{color:#22c55e;margin-top:0}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-top:22px}
.card h3{margin:0;color:#94a3b8;font-size:14px}.card strong{display:block;color:#22c55e;font-size:30px;margin-top:12px}
table{width:100%;border-collapse:collapse}th,td{padding:14px;border-bottom:1px solid rgba(148,163,184,.12);text-align:left}th{color:#22c55e}
.btn{display:inline-block;padding:12px 15px;border-radius:14px;background:#22c55e;color:#052e16;text-decoration:none;font-weight:900;border:0}
input,select{width:100%;padding:13px;border-radius:14px;border:1px solid rgba(148,163,184,.18);background:#020617;color:white;margin-bottom:10px}
@media(max-width:850px){.sidebar{position:relative;width:100%;height:auto}.main{margin-left:0;padding:18px}}
</style>

<div class="sidebar">
<div class="logo">H</div>
<h2>Hakim Networks</h2>
<p><?php echo htmlspecialchars($name); ?></p>

<a href="/mhakim-billing-system/tenant_noc_final_clean.php">📊 Dashboard</a>
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
