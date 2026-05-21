<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];

$stmt = $pdo->prepare("
    SELECT *
    FROM payments
    WHERE company_id=?
    ORDER BY id DESC
");

$stmt->execute([$companyId]);

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>My Payments</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;font-family:Arial;background:#0f172a;color:white}
.wrap{padding:30px}
.card{background:#111827;border-radius:20px;padding:20px}
table{width:100%;border-collapse:collapse}
th,td{padding:14px;border-bottom:1px solid #1e293b}
th{color:#22c55e;text-align:left}
.amount{color:#22c55e;font-weight:900}
</style>
</head>
<body>
<div class="wrap">
<div class="card">
<h2>My Payments</h2>

<table>
<tr>
<th>Amount</th>
<th>Method</th>
<th>Status</th>
<th>Date</th>
</tr>

<?php foreach($payments as $p): ?>

<tr>
<td class="amount">
KES <?php echo number_format($p["amount"] ?? 0); ?>
</td>

<td><?php echo htmlspecialchars($p["method"] ?? ""); ?></td>
<td><?php echo htmlspecialchars($p["status"] ?? ""); ?></td>
<td><?php echo htmlspecialchars($p["created_at"] ?? ""); ?></td>
</tr>

<?php endforeach; ?>

</table>
</div>
</div>
</body>
</html>
