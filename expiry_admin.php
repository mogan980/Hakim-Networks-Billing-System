<?php
require_once __DIR__ . "/config/database.php";

$expired = $pdo->query("
SELECT COUNT(*) FROM smart_vouchers
WHERE status='used'
AND expires_at IS NOT NULL
AND expires_at <= NOW()
")->fetchColumn();

$totalExpired = $pdo->query("
SELECT COUNT(*) FROM smart_vouchers
WHERE status='expired'
")->fetchColumn();

$activeUsers = $pdo->query("
SELECT COUNT(*) FROM smart_vouchers
WHERE status='used'
")->fetchColumn();

$log = file_exists(__DIR__."/expiry_engine.log")
? htmlspecialchars(file_get_contents(__DIR__."/expiry_engine.log"))
: "No logs available.";

?>
<!DOCTYPE html>
<html>
<head>
<title>Hakim Networks • Expiry Engine</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{
box-sizing:border-box;
margin:0;
padding:0;
}

body{
font-family:Inter,Arial,sans-serif;
background:#071018;
color:#e2e8f0;
display:flex;
min-height:100vh;
}

.sidebar{
width:260px;
background:linear-gradient(180deg,#020617,#052e2b);
padding:28px 18px;
position:fixed;
top:0;
bottom:0;
left:0;
overflow:auto;
border-right:1px solid rgba(255,255,255,.05);
}

.logo{
font-size:30px;
font-weight:900;
margin-bottom:6px;
color:#22c55e;
}

.subtitle{
color:#94a3b8;
font-size:14px;
margin-bottom:28px;
}

.sidebar a{
display:flex;
align-items:center;
gap:12px;
padding:14px 16px;
margin-bottom:10px;
text-decoration:none;
color:#e2e8f0;
border-radius:16px;
font-weight:700;
transition:.25s;
}

.sidebar a:hover,
.sidebar .active{
background:#16a34a;
color:#04130b;
transform:translateX(4px);
}

.main{
margin-left:260px;
padding:32px;
width:100%;
}

.topbar{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:28px;
}

.topbar h1{
font-size:34px;
font-weight:900;
}

.live{
background:#052e2b;
border:1px solid #14532d;
padding:10px 18px;
border-radius:999px;
font-size:14px;
color:#4ade80;
font-weight:700;
}

.grid{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:22px;
margin-bottom:26px;
}

.card{
background:linear-gradient(180deg,#0b1728,#09121d);
border:1px solid rgba(255,255,255,.05);
border-radius:28px;
padding:24px;
box-shadow:0 20px 50px rgba(0,0,0,.25);
}

.card h3{
font-size:15px;
color:#94a3b8;
margin-bottom:14px;
}

.big{
font-size:52px;
font-weight:900;
line-height:1;
margin-bottom:10px;
}

.red{color:#ef4444}
.green{color:#22c55e}
.blue{color:#38bdf8}

.btn{
display:inline-flex;
align-items:center;
gap:10px;
background:#22c55e;
color:#04130b;
padding:15px 22px;
border-radius:18px;
font-weight:900;
text-decoration:none;
margin-top:18px;
transition:.25s;
}

.btn:hover{
transform:translateY(-2px);
box-shadow:0 12px 24px rgba(34,197,94,.25);
}

.logs{
margin-top:28px;
}

.logs h2{
margin-bottom:18px;
font-size:24px;
}

.logbox{
background:#020617;
border:1px solid rgba(255,255,255,.06);
border-radius:24px;
padding:22px;
overflow:auto;
max-height:520px;
font-family:monospace;
font-size:14px;
line-height:1.6;
color:#86efac;
white-space:pre-wrap;
}

.footer{
margin-top:28px;
text-align:center;
color:#64748b;
font-size:13px;
}

@media(max-width:1000px){
.grid{
grid-template-columns:1fr;
}

.sidebar{
display:none;
}

.main{
margin-left:0;
}
}
</style>
</head>
<body>

<div class="sidebar">
<div class="logo">M.Hakim</div>
<div class="subtitle">Hakim Networks • ISP Core</div>

<a href="dashboard.php">📊 Dashboard</a>
<a href="noc_final_clean.php">📡 Live NOC</a>
<a href="smart_vouchers.php">🎟 Smart Vouchers</a>
<a href="payments.php">💳 Payments</a>
<a href="packages.php">📦 Packages</a>
<a href="routers.php">🛰 Routers</a>
</div>

<div class="main">

<div class="topbar">
<div>
<h1>Auto Expiry Engine</h1>
</div>

<div class="live">
● Engine Online
</div>
</div>

<div class="grid">

<div class="card">
<h3>Expired Waiting Disconnect</h3>
<div class="big red"><?php echo $expired; ?></div>
<p>Users awaiting forced disconnection.</p>

<a class="btn" href="expiry_engine.php">
⚡ Run Expiry Engine
</a>
</div>

<div class="card">
<h3>Total Expired</h3>
<div class="big blue"><?php echo $totalExpired; ?></div>
<p>Total expired vouchers archived by system.</p>
</div>

<div class="card">
<h3>Currently Active</h3>
<div class="big green"><?php echo $activeUsers; ?></div>
<p>Hotspot users currently active in database.</p>
</div>

</div>

<div class="logs">
<h2>Engine Activity Logs</h2>

<div class="logbox"><?php echo $log; ?></div>
</div>

<div class="footer">
Hakim Networks • Professional ISP Billing System
</div>

</div>
<script>
</script>
</body>
</html>
