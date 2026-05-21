<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("
        INSERT INTO packages
        (company_id, name, duration_hours, speed_down, speed_up, price, status)
        VALUES (?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([
        $companyId,
        trim($_POST["name"]),
        $_POST["duration_hours"],
        trim($_POST["speed_down"]),
        trim($_POST["speed_up"]),
        $_POST["price"]
    ]);
    $msg = "Package added successfully.";
}

$stmt = $pdo->prepare("SELECT * FROM packages WHERE company_id=? ORDER BY id DESC");
$stmt->execute([$companyId]);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><title>My Packages</title><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body>
<?php include __DIR__ . "/tenant_layout.php"; ?>
<div class="main">
<div class="hero"><h1>Internet Packages</h1><p>Create packages for your own ISP clients.</p></div>

<?php if($msg): ?><div class="panel" style="margin-top:18px;color:#22c55e;font-weight:900;"><?php echo $msg; ?></div><?php endif; ?>

<div class="panel" style="margin-top:24px;">
<h2>Add Package</h2>
<form method="POST">
<input name="name" placeholder="Package name e.g. 3 Hours" required>
<input name="duration_hours" type="number" step="0.01" placeholder="Duration hours" required>
<input name="speed_down" placeholder="Download e.g. 6M" required>
<input name="speed_up" placeholder="Upload e.g. 2M" required>
<input name="price" type="number" step="0.01" placeholder="Price KES" required>
<button class="btn">Add Package</button>
</form>
</div>

<div class="grid">
<?php foreach($packages as $p): ?>
<div class="card">
<h3><?php echo htmlspecialchars($p["name"]); ?></h3>
<strong>KES <?php echo number_format($p["price"]); ?></strong>
<p><?php echo htmlspecialchars($p["speed_down"]); ?> / <?php echo htmlspecialchars($p["speed_up"]); ?></p>
<p><?php echo htmlspecialchars($p["duration_hours"]); ?> hrs</p>
</div>
<?php endforeach; ?>
</div>
</div></body></html>
