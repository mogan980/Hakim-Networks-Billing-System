<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];
$msg = "";

$clientsStmt = $pdo->prepare("SELECT * FROM clients WHERE company_id=? ORDER BY full_name ASC");
$clientsStmt->execute([$companyId]);
$clients = $clientsStmt->fetchAll(PDO::FETCH_ASSOC);

$pkgStmt = $pdo->prepare("SELECT * FROM packages WHERE company_id=? ORDER BY price ASC");
$pkgStmt->execute([$companyId]);
$packages = $pkgStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("
        INSERT INTO payments
        (company_id, client_id, package_id, amount, method, reference, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'paid', NOW())
    ");
    $stmt->execute([
        $companyId,
        $_POST["client_id"] ?: null,
        $_POST["package_id"] ?: null,
        $_POST["amount"],
        $_POST["method"],
        trim($_POST["reference"])
    ]);
    $msg = "Payment recorded successfully.";
}

$stmt = $pdo->prepare("
    SELECT pay.*, c.full_name client_name, p.name package_name
    FROM payments pay
    LEFT JOIN clients c ON c.id=pay.client_id
    LEFT JOIN packages p ON p.id=pay.package_id
    WHERE pay.company_id=?
    ORDER BY pay.id DESC
");
$stmt->execute([$companyId]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><title>My Payments</title><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body>
<?php include __DIR__ . "/tenant_layout.php"; ?>
<div class="main">
<div class="hero"><h1>Payments</h1><p>Record and view payments for your own ISP business.</p></div>

<?php if($msg): ?><div class="panel" style="margin-top:18px;color:#22c55e;font-weight:900;"><?php echo $msg; ?></div><?php endif; ?>

<div class="panel" style="margin-top:24px;">
<h2>Add Payment</h2>
<form method="POST">
<select name="client_id">
<option value="">Select client optional</option>
<?php foreach($clients as $c): ?>
<option value="<?php echo $c["id"]; ?>"><?php echo htmlspecialchars($c["full_name"]); ?></option>
<?php endforeach; ?>
</select>

<select name="package_id">
<option value="">Select package optional</option>
<?php foreach($packages as $p): ?>
<option value="<?php echo $p["id"]; ?>"><?php echo htmlspecialchars($p["name"]); ?></option>
<?php endforeach; ?>
</select>

<input type="number" step="0.01" name="amount" placeholder="Amount KES" required>
<select name="method" required>
<option value="cash">Cash</option>
<option value="mpesa">M-Pesa</option>
<option value="voucher">Voucher</option>
</select>
<input name="reference" placeholder="Reference e.g. receipt/code">
<button class="btn">Record Payment</button>
</form>
</div>

<div class="panel" style="margin-top:24px;">
<h2>Payment History</h2>
<table>
<tr><th>Client</th><th>Package</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr>
<?php foreach($payments as $p): ?>
<tr>
<td><?php echo htmlspecialchars($p["client_name"] ?? "-"); ?></td>
<td><?php echo htmlspecialchars($p["package_name"] ?? "-"); ?></td>
<td><b>KES <?php echo number_format($p["amount"]); ?></b></td>
<td><?php echo htmlspecialchars($p["method"]); ?></td>
<td><?php echo htmlspecialchars($p["status"]); ?></td>
<td><?php echo htmlspecialchars($p["created_at"]); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div></body></html>
