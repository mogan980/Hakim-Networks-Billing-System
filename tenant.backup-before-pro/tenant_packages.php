<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];

$stmt = $pdo->prepare("
    SELECT *
    FROM packages
    WHERE company_id=?
    ORDER BY id DESC
");

$stmt->execute([$companyId]);

$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>My Packages</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;font-family:Arial;background:#0f172a;color:white}
.wrap{padding:30px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}
.card{background:#111827;border-radius:20px;padding:20px}
.price{font-size:28px;color:#22c55e;font-weight:900}
</style>
</head>
<body>
<div class="wrap">

<h2>My Internet Packages</h2>

<div class="grid">

<?php foreach($packages as $p): ?>

<div class="card">
<h3><?php echo htmlspecialchars($p["name"] ?? ""); ?></h3>

<div class="price">
KES <?php echo number_format($p["price"] ?? 0); ?>
</div>

<p>
<?php echo htmlspecialchars($p["speed_down"] ?? ""); ?>
/
<?php echo htmlspecialchars($p["speed_up"] ?? ""); ?>
</p>

<p>
<?php echo htmlspecialchars($p["duration_hours"] ?? ""); ?> hrs
</p>
</div>

<?php endforeach; ?>

</div>
</div>
</body>
</html>
