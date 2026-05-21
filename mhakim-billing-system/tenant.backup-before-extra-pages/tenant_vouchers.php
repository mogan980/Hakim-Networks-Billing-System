<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];
$msg = "";

$pkg = $pdo->prepare("SELECT * FROM packages WHERE company_id=? ORDER BY price ASC");
$pkg->execute([$companyId]);
$packages = $pkg->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $packageId = $_POST["package_id"];
    $quantity = max(1, (int)$_POST["quantity"]);

    $stmt = $pdo->prepare("INSERT INTO vouchers (company_id, code, package_id, status) VALUES (?, ?, ?, 'unused')");

    for($i=0; $i<$quantity; $i++){
        $code = strtoupper("MH-" . substr(md5(uniqid(rand(), true)), 0, 8));
        $stmt->execute([$companyId, $code, $packageId]);
    }

    $msg = "$quantity voucher(s) generated successfully.";
}

$stmt = $pdo->prepare("
    SELECT v.*, p.name package_name, p.price
    FROM vouchers v
    LEFT JOIN packages p ON p.id=v.package_id
    WHERE v.company_id=?
    ORDER BY v.id DESC
");
$stmt->execute([$companyId]);
$vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><title>My Vouchers</title><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body>
<?php include __DIR__ . "/tenant_layout.php"; ?>
<div class="main">
<div class="hero"><h1>Vouchers</h1><p>Generate and track vouchers for your own clients.</p></div>

<?php if($msg): ?><div class="panel" style="margin-top:18px;color:#22c55e;font-weight:900;"><?php echo $msg; ?></div><?php endif; ?>

<div class="panel" style="margin-top:24px;">
<h2>Generate Vouchers</h2>
<form method="POST">
<select name="package_id" required>
<option value="">Select package</option>
<?php foreach($packages as $p): ?>
<option value="<?php echo $p["id"]; ?>"><?php echo htmlspecialchars($p["name"]); ?> - KES <?php echo number_format($p["price"]); ?></option>
<?php endforeach; ?>
</select>
<input type="number" name="quantity" placeholder="Quantity" value="1" min="1" required>
<button class="btn">Generate Vouchers</button>
</form>
</div>

<div class="panel" style="margin-top:24px;">
<h2>My Vouchers</h2>
<table>
<tr><th>Code</th><th>Package</th><th>Value</th><th>Status</th><th>Created</th></tr>
<?php foreach($vouchers as $v): ?>
<tr>
<td><b><?php echo htmlspecialchars($v["code"]); ?></b></td>
<td><?php echo htmlspecialchars($v["package_name"] ?? "-"); ?></td>
<td>KES <?php echo number_format($v["price"] ?? 0); ?></td>
<td><?php echo htmlspecialchars($v["status"]); ?></td>
<td><?php echo htmlspecialchars($v["created_at"]); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div></body></html>
