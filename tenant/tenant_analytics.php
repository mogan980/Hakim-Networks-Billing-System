<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];

$rev = $pdo->prepare("
SELECT DATE(created_at) day, COALESCE(SUM(amount),0) total
FROM payments
WHERE company_id=? AND status='paid'
GROUP BY DATE(created_at)
ORDER BY day ASC
LIMIT 7
");
$rev->execute([$companyId]);
$rows=$rev->fetchAll(PDO::FETCH_ASSOC);

$labels=[];
$values=[];
foreach($rows as $r){
    $labels[]=$r["day"];
    $values[]=(float)$r["total"];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Analytics</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="/mhakim-billing-system/tenant/assets/pro.css">
</head>
<body>
<?php include __DIR__ . "/tenant_layout.php"; ?>

<div class="main">
<div class="hero">
<h1>Live Analytics</h1>
<p>Track revenue, clients and voucher performance for your own ISP business.</p>
</div>

<div class="grid">
<div class="panel">
<h2>Revenue Trend</h2>
<div class="chart-box"><canvas id="revenueChart"></canvas></div>
</div>

<div class="panel">
<h2>Business Health</h2>
<div class="card"><h3>System Mode</h3><strong>LIVE</strong></div>
<div class="card" style="margin-top:14px;"><h3>Privacy</h3><strong>Isolated</strong></div>
</div>

</div>

<div class="panel" style="margin-top:24px;">
<h2>Analytics Insights</h2>

<div class="info-grid">

<div class="info-box">
<h4>Revenue Status</h4>
<strong>LIVE</strong>
</div>

<div class="info-box">
<h4>Client Tracking</h4>
<strong>ACTIVE</strong>
</div>

<div class="info-box">
<h4>Voucher Monitoring</h4>
<strong>SYNCED</strong>
</div>

<div class="info-box">
<h4>Tenant Security</h4>
<strong>ISOLATED</strong>
</div>

</div>

</div>

</div>

<script>

new Chart(document.getElementById("revenueChart"),{
type:"line",
data:{
labels:<?php echo json_encode($labels); ?>,
datasets:[{
label:"Revenue KES",
data:<?php echo json_encode($values); ?>,
borderWidth:3,
fill:true,
tension:.4
}]
}
});
</script>
</body>
</html>
