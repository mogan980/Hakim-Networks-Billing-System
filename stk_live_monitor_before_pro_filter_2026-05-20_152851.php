<?php
require_once __DIR__ . "/config/database.php";

$payments = $pdo->query("
SELECT *
FROM hotspot_payments
ORDER BY id DESC
LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);

$totalPaid = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM hotspot_payments WHERE status='paid'")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM hotspot_payments WHERE status='pending'")->fetchColumn();
$paid = $pdo->query("SELECT COUNT(*) FROM hotspot_payments WHERE status='paid'")->fetchColumn();
$failed = $pdo->query("SELECT COUNT(*) FROM hotspot_payments WHERE status='failed'")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
<title>Live STK Monitor</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;font-family:Arial;background:#071018;color:#e2e8f0}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#020617;padding:24px}
.sidebar h2{color:#22c55e}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;border-radius:12px;margin:7px 0;font-weight:800}
.sidebar a:hover,.active{background:#16a34a;color:#052e16}
.main{margin-left:280px;padding:32px}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.card{background:#0b1728;border:1px solid #1f2937;border-radius:22px;padding:22px;margin-bottom:22px}
.card h3{color:#94a3b8}
.big{font-size:36px;font-weight:900;color:#22c55e}
table{width:100%;border-collapse:collapse}
th{background:#020617;padding:13px;text-align:left}
td{padding:13px;border-bottom:1px solid #1f2937}
.badge{padding:6px 11px;border-radius:999px;font-weight:900;font-size:12px}
.paid{background:#dcfce7;color:#166534}.pending{background:#fef3c7;color:#92400e}.failed{background:#fee2e2;color:#991b1b}
@media(max-width:900px){.sidebar{display:none}.main{margin-left:0}.grid{grid-template-columns:1fr}}
</style>
<meta http-equiv="refresh" content="8">
</head>
<body>
<div class="sidebar">
<h2>M.Hakim</h2>
<a href="noc_final_clean.php">📡 Live NOC</a>
<a href="smart_vouchers.php">🎟 Smart Vouchers</a>
<a href="expiry_admin.php">⏱ Expiry Engine</a>
<a class="active" href="stk_live_monitor.php">💳 STK Monitor</a>
<a href="payments.php">Payments</a>
</div>

<div class="main">
<h1>Live STK Session Monitor</h1>
<p>Auto-refreshing M-Pesa STK payment sessions.</p>

<div class="grid">
<div class="card"><h3>Total Revenue</h3><div class="big">KES <?php echo number_format($totalPaid); ?></div></div>
<div class="card"><h3>Paid</h3><div class="big"><?php echo $paid; ?></div></div>
<div class="card"><h3>Pending</h3><div class="big"><?php echo $pending; ?></div></div>
<div class="card"><h3>Failed</h3><div class="big"><?php echo $failed; ?></div></div>
</div>

<div class="card">
<h2>Recent STK Sessions</h2>
<table>
<tr>
<th>ID</th><th>Phone</th><th>Amount</th><th>Status</th><th>Receipt</th><th>Checkout</th><th>Created</th><th>Paid At</th>
</tr>
<?php foreach($payments as $p): ?>
<tr>
<td><?php echo (int)$p["id"]; ?></td>
<td><?php echo htmlspecialchars($p["phone"]); ?></td>
<td>KES <?php echo number_format((float)$p["amount"]); ?></td>
<td><span class="badge <?php echo htmlspecialchars($p["status"]); ?>"><?php echo strtoupper($p["status"]); ?></span></td>
<td><?php echo htmlspecialchars($p["mpesa_receipt"] ?? "-"); ?></td>
<td><?php echo htmlspecialchars($p["checkout_request_id"]); ?></td>
<td><?php echo htmlspecialchars($p["created_at"]); ?></td>
<td><?php echo htmlspecialchars($p["paid_at"] ?? "-"); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
</body>
</html>
