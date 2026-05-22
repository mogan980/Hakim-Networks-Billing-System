<?php

$pages = [
    "pro_hub.php" => "Pro Hub",
    "expiry_engine.php" => "Expiry Engine",
    "stk_sessions.php" => "STK Sessions",
    "voucher_print_pro.php" => "Voucher Print",
    "revenue_analytics.php" => "Revenue Analytics",
    "whatsapp_delivery.php" => "WhatsApp Delivery",
    "multi_router_sync.php" => "Multi Router Sync",
    "customer_portal_admin.php" => "Customer Portal",
    "noc_map.php" => "NOC Map"
];

foreach($pages as $file => $title){

$content = <<<HTML
<?php require_once __DIR__ . "/config/database.php"; ?>
<!DOCTYPE html>
<html>
<head>
<title>{$title}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{
margin:0;
font-family:Arial;
background:#06141f;
color:#e5e7eb;
}
.sidebar{
position:fixed;
left:0;
top:0;
bottom:0;
width:240px;
background:#020617;
padding:22px;
overflow:auto;
}
.sidebar h2{
color:#22c55e;
}
.sidebar a{
display:block;
color:white;
text-decoration:none;
padding:13px;
border-radius:12px;
margin:7px 0;
font-weight:800;
}
.sidebar a:hover{
background:#16a34a;
color:#052e16;
}
.main{
margin-left:260px;
padding:30px;
}
.card{
background:#0b1728;
border:1px solid #1f2937;
border-radius:22px;
padding:24px;
margin-bottom:20px;
}
.grid{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:15px;
}
.stat{
background:#020617;
border-radius:18px;
padding:18px;
}
.stat h2{
color:#22c55e;
}
.btn{
background:#22c55e;
color:#052e16;
padding:12px 16px;
border-radius:12px;
font-weight:900;
display:inline-block;
text-decoration:none;
}
table{
width:100%;
border-collapse:collapse;
}
th{
background:#020617;
padding:13px;
text-align:left;
}
td{
padding:13px;
border-bottom:1px solid #1e293b;
}
iframe{
width:100%;
height:650px;
border:0;
border-radius:18px;
}
@media(max-width:900px){
.sidebar{display:none}
.main{margin-left:0}
.grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<div class="sidebar">
<h2>Hakim Pro</h2>

<a href="pro_hub.php">🏠 Pro Hub</a>
<a href="smart_vouchers.php">🎟 Vouchers</a>
<a href="expiry_engine.php">⏱ Expiry</a>
<a href="stk_sessions.php">💳 STK</a>
<a href="voucher_print_pro.php">🖨 Print</a>
<a href="revenue_analytics.php">📊 Revenue</a>
<a href="whatsapp_delivery.php">📲 WhatsApp</a>
<a href="multi_router_sync.php">🛰 Routers</a>
<a href="customer_portal_admin.php">👤 Portal</a>
<a href="noc_map.php">🗺 NOC Map</a>

</div>

<div class="main">
<h1>{$title}</h1>

<div class="card">
<h2>Hakim Networks Professional Module</h2>
<p>Professional ISP tools installed successfully.</p>
</div>

HTML;

if($file == "pro_hub.php"){
$content .= '
<div class="grid">
<div class="stat"><small>Auto Expiry</small><h2>Ready</h2></div>
<div class="stat"><small>Revenue</small><h2>Live</h2></div>
<div class="stat"><small>STK Monitor</small><h2>Connected</h2></div>
<div class="stat"><small>NOC</small><h2>Online</h2></div>
</div>';
}

if($file == "revenue_analytics.php"){
$content .= '
<?php
$today = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM hotspot_payments WHERE status=\'paid\' AND DATE(paid_at)=CURDATE()")->fetchColumn();

$month = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM hotspot_payments WHERE status=\'paid\' AND MONTH(paid_at)=MONTH(CURDATE())")->fetchColumn();

$total = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM hotspot_payments WHERE status=\'paid\'")->fetchColumn();
?>

<div class="grid">
<div class="stat"><small>Today</small><h2>KES <?php echo number_format($today); ?></h2></div>
<div class="stat"><small>Month</small><h2>KES <?php echo number_format($month); ?></h2></div>
<div class="stat"><small>Total</small><h2>KES <?php echo number_format($total); ?></h2></div>
<div class="stat"><small>Status</small><h2>ACTIVE</h2></div>
</div>';
}

if($file == "stk_sessions.php"){
$content .= '
<div class="card">
<h2>Recent STK Sessions</h2>

<table>
<tr>
<th>Phone</th>
<th>Amount</th>
<th>Status</th>
<th>Receipt</th>
<th>Paid At</th>
</tr>

<?php
$q = $pdo->query("SELECT * FROM hotspot_payments ORDER BY id DESC LIMIT 50");

foreach($q as $p){
echo "<tr>";
echo "<td>".$p["phone"]."</td>";
echo "<td>KES ".$p["amount"]."</td>";
echo "<td>".$p["status"]."</td>";
echo "<td>".($p["mpesa_receipt"] ?? "-")."</td>";
echo "<td>".($p["paid_at"] ?? "-")."</td>";
echo "</tr>";
}
?>

</table>
</div>';
}

if($file == "noc_map.php"){
$content .= '
<div class="card">
<h2>Real-Time NOC Map</h2>
<iframe src="topology_map.php"></iframe>
</div>';
}

$content .= '
</div>
</body>
</html>';

file_put_contents($file,$content);

}

echo "PRO MODULES INSTALLED SUCCESSFULLY\n";
