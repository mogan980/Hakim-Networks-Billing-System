<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];

function one($pdo,$sql,$id){
    $s=$pdo->prepare($sql);
    $s->execute([$id]);
    return $s->fetchColumn();
}

$clients = one($pdo,"SELECT COUNT(*) FROM clients WHERE company_id=?",$companyId);
$payments = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE company_id=? AND status='paid'",$companyId);
$vouchers = one($pdo,"SELECT COUNT(*) FROM vouchers WHERE company_id=?",$companyId);
$packages = one($pdo,"SELECT COUNT(*) FROM packages WHERE company_id=?",$companyId);
?>
<!DOCTYPE html>
<html>
<head><title>Reports</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="/mhakim-billing-system/tenant/assets/pro.css">
</head>
<body>
<?php include __DIR__ . "/tenant_layout.php"; ?>

<div class="main">
<div class="hero">
<h1>ISP Reports Center</h1>
<p>Private business reports for your ISP workspace.</p>
</div>

<div class="grid">
<div class="card"><h3>Total Clients</h3><strong><?php echo $clients; ?></strong></div>
<div class="card"><h3>Total Revenue</h3><strong>KES <?php echo number_format($payments); ?></strong></div>
<div class="card"><h3>Total Vouchers</h3><strong><?php echo $vouchers; ?></strong></div>
<div class="card"><h3>Total Packages</h3><strong><?php echo $packages; ?></strong></div>
</div>

<div class="panel" style="margin-top:24px;">
<h2>Report Actions</h2>
<a class="btn" href="tenant_payments.php">View Payment Report</a>
<a class="btn" href="tenant_clients.php">View Client Report</a>
<a class="btn" href="tenant_vouchers.php">View Voucher Report</a>
</div>

<div class="panel" style="margin-top:24px;">
<h2>Business Insights</h2>

<div class="info-grid">

<div class="info-box">
<h4>Growth</h4>
<strong>Stable</strong>
</div>

<div class="info-box">
<h4>Hotspot System</h4>
<strong>ONLINE</strong>
</div>

<div class="info-box">
<h4>Revenue Engine</h4>
<strong>ACTIVE</strong>
</div>

<div class="info-box">
<h4>Voucher Engine</h4>
<strong>RUNNING</strong>
</div>

</div>

</div>

</div>
</body>
</html>
