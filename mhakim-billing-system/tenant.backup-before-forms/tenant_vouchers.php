<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];

$stmt = $pdo->prepare("
    SELECT *
    FROM vouchers
    WHERE company_id=?
    ORDER BY id DESC
");

$stmt->execute([$companyId]);

$vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>My Vouchers</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;font-family:Arial;background:#0f172a;color:white}
.wrap{padding:30px}
.card{background:#111827;border-radius:20px;padding:20px}
table{width:100%;border-collapse:collapse}
th,td{padding:14px;border-bottom:1px solid #1e293b}
th{color:#22c55e;text-align:left}
</style>
</head>
<body>
<div class="wrap">
<div class="card">
<h2>My Vouchers</h2>

<table>
<tr>
<th>Code</th>
<th>Status</th>
<th>Created</th>
</tr>

<?php foreach($vouchers as $v): ?>

<tr>
<td><?php echo htmlspecialchars($v["code"] ?? ""); ?></td>
<td><?php echo htmlspecialchars($v["status"] ?? ""); ?></td>
<td><?php echo htmlspecialchars($v["created_at"] ?? ""); ?></td>
</tr>

<?php endforeach; ?>

</table>
</div>
</div>
</body>
</html>
