<?php
session_start();
require_once "config/database.php";

if (($_SESSION["tenant_role"] ?? "") !== "super_admin") {
    header("Location: saas_auth.php");
    exit;
}

$id = (int)($_GET["id"] ?? 0);
if (!$id) die("Missing tenant ID.");

$stmt = $pdo->prepare("SELECT * FROM companies WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$company) die("Tenant not found.");

function one($pdo,$sql,$id){
    $s=$pdo->prepare($sql);
    $s->execute([$id]);
    return $s->fetchColumn();
}

$clients = one($pdo,"SELECT COUNT(*) FROM clients WHERE company_id=?",$id);
$packages = one($pdo,"SELECT COUNT(*) FROM packages WHERE company_id=?",$id);
$vouchers = one($pdo,"SELECT COUNT(*) FROM vouchers WHERE company_id=?",$id);
$revenue = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE company_id=? AND status='paid'",$id);

$sub = $pdo->prepare("SELECT * FROM subscriptions WHERE company_id=? ORDER BY id DESC LIMIT 1");
$sub->execute([$id]);
$subscription = $sub->fetch(PDO::FETCH_ASSOC);

$logs = $pdo->prepare("SELECT * FROM tenant_router_logs WHERE company_id=? ORDER BY id DESC LIMIT 10");
$logs->execute([$id]);
$activity = $logs->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Tenant Details</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;font-family:Arial;background:#e7f8ff;color:#0f172a}
.main{padding:28px}
.hero,.card,.panel{background:white;border-radius:22px;padding:22px;box-shadow:0 12px 35px rgba(15,23,42,.08);margin-bottom:18px}
.hero{background:linear-gradient(135deg,#0f172a,#166534);color:white}
.hero h1{margin:0}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
.card h3{margin:0;color:#475569;font-size:13px}.card strong{display:block;margin-top:10px;font-size:28px;color:#16a34a}
table{width:100%;border-collapse:collapse}td,th{padding:13px;border-bottom:1px solid #e5e7eb;text-align:left}
.badge{padding:7px 10px;border-radius:999px;font-weight:900;font-size:12px}.green{background:#dcfce7;color:#15803d}.red{background:#fee2e2;color:#dc2626}.orange{background:#ffedd5;color:#ea580c}
.btn{display:inline-block;background:#16a34a;color:white;padding:11px 14px;border-radius:12px;text-decoration:none;font-weight:900}
</style>
</head>
<body>
<div class="main">

<a class="btn" href="admin_tenants.php">← Back to Tenants</a>

<div class="hero">
<h1><?php echo htmlspecialchars($company["company_name"]); ?></h1>
<p>Owner: <?php echo htmlspecialchars($company["owner_name"] ?? "-"); ?> • <?php echo htmlspecialchars($company["email"] ?? "-"); ?></p>
</div>

<div class="grid">
<div class="card"><h3>Clients</h3><strong><?php echo $clients; ?></strong></div>
<div class="card"><h3>Packages</h3><strong><?php echo $packages; ?></strong></div>
<div class="card"><h3>Vouchers</h3><strong><?php echo $vouchers; ?></strong></div>
<div class="card"><h3>Revenue</h3><strong>KES <?php echo number_format($revenue); ?></strong></div>
</div>

<div class="panel">
<h2>Subscription</h2>
<table>
<tr><th>Status</th><td><span class="badge <?php echo (($subscription["status"] ?? "")==="paid")?"green":"orange"; ?>"><?php echo htmlspecialchars($subscription["status"] ?? "none"); ?></span></td></tr>
<tr><th>Amount</th><td>KES <?php echo number_format($subscription["amount"] ?? 0); ?></td></tr>
<tr><th>Payment Phone</th><td><?php echo htmlspecialchars($subscription["payment_phone"] ?? "-"); ?></td></tr>
<tr><th>Reference</th><td><?php echo htmlspecialchars($subscription["payment_reference"] ?? "-"); ?></td></tr>
<tr><th>Expires</th><td><?php echo htmlspecialchars($subscription["expires_at"] ?? "-"); ?></td></tr>
</table>
</div>

<div class="panel">
<h2>Router</h2>
<table>
<tr><th>Router IP</th><td><?php echo htmlspecialchars($company["router_ip"] ?? "-"); ?></td></tr>
<tr><th>Router Name</th><td><?php echo htmlspecialchars($company["router_name"] ?? "-"); ?></td></tr>
<tr><th>Status</th><td><?php echo empty($company["router_ip"]) ? "<span class='badge red'>Disconnected</span>" : "<span class='badge green'>Connected</span>"; ?></td></tr>
</table>
</div>

<div class="panel">
<h2>Recent Activity</h2>
<table>
<tr><th>Action</th><th>Message</th><th>Date</th></tr>
<?php foreach($activity as $a): ?>
<tr>
<td><?php echo htmlspecialchars($a["action_type"]); ?></td>
<td><?php echo htmlspecialchars($a["message"]); ?></td>
<td><?php echo htmlspecialchars($a["created_at"]); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

</div>
</body>
</html>
