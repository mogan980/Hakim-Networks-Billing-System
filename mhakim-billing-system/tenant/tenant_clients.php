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
    $duration = 1;

    foreach($packages as $p){
        if($p["id"] == $packageId) $duration = $p["duration_hours"];
    }

    $expires = date("Y-m-d H:i:s", strtotime("+{$duration} hours"));

    $stmt = $pdo->prepare("
        INSERT INTO clients
        (company_id, full_name, phone, username, password, package_id, status, starts_at, expires_at, connection_type)
        VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), ?, 'hotspot')
    ");
    $stmt->execute([
        $companyId,
        trim($_POST["full_name"]),
        trim($_POST["phone"]),
        trim($_POST["username"]),
        trim($_POST["password"]),
        $packageId,
        $expires
    ]);
    $msg = "Client added successfully.";
}

$stmt = $pdo->prepare("
    SELECT c.*, p.name package_name
    FROM clients c
    LEFT JOIN packages p ON p.id=c.package_id
    WHERE c.company_id=?
    ORDER BY c.id DESC
");
$stmt->execute([$companyId]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><title>My Clients</title><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body>
<?php include __DIR__ . "/tenant_layout.php"; ?>
<div class="main">
<div class="hero"><h1>Clients</h1><p>Add and manage your own hotspot clients privately.</p></div>

<?php if($msg): ?><div class="panel" style="margin-top:18px;color:#22c55e;font-weight:900;"><?php echo $msg; ?></div><?php endif; ?>

<div class="panel" style="margin-top:24px;">
<h2>Add Client</h2>
<form method="POST">
<input name="full_name" placeholder="Client name" required>
<input name="phone" placeholder="Phone number">
<input name="username" placeholder="Hotspot username" required>
<input name="password" placeholder="Hotspot password" required>
<select name="package_id" required>
<option value="">Select package</option>
<?php foreach($packages as $p): ?>
<option value="<?php echo $p["id"]; ?>"><?php echo htmlspecialchars($p["name"]); ?> - KES <?php echo number_format($p["price"]); ?></option>
<?php endforeach; ?>
</select>
<button class="btn">Add Client</button>
</form>
</div>

<div class="panel" style="margin-top:24px;">
<h2>My Clients</h2>
<table>
<tr><th>Name</th><th>Phone</th><th>Username</th><th>Package</th><th>Status</th><th>Expiry</th></tr>
<?php foreach($clients as $c): ?>
<tr>
<td><?php echo htmlspecialchars($c["full_name"]); ?></td>
<td><?php echo htmlspecialchars($c["phone"]); ?></td>
<td><?php echo htmlspecialchars($c["username"]); ?></td>
<td><?php echo htmlspecialchars($c["package_name"] ?? "-"); ?></td>
<td><?php echo htmlspecialchars($c["status"]); ?></td>
<td><?php echo htmlspecialchars($c["expires_at"]); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div></body></html>
