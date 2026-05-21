<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];

$stmt = $pdo->prepare("SELECT * FROM companies WHERE id=? LIMIT 1");
$stmt->execute([$companyId]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);

$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $router_ip   = trim($_POST["router_ip"]);
    $router_user = trim($_POST["router_user"]);
    $router_pass = trim($_POST["router_pass"]);
    $router_port = trim($_POST["router_port"]);
    $router_name = trim($_POST["router_name"]);

    $update = $pdo->prepare("
        UPDATE companies
        SET
            router_ip=?,
            router_user=?,
            router_pass=?,
            router_port=?,
            router_name=?
        WHERE id=?
    ");

    $update->execute([
        $router_ip,
        $router_user,
        $router_pass,
        $router_port,
        $router_name,
        $companyId
    ]);

    $success = "Router configuration saved successfully.";

    $stmt->execute([$companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Tenant Router Setup</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php include __DIR__ . "/tenant_layout.php"; ?>

<div class="main">

<div class="hero">
<h1>Tenant MikroTik Connection</h1>
<p>Connect your own MikroTik router securely for live hotspot analytics and automation.</p>
</div>

<?php if($success): ?>
<div class="panel" style="margin-top:20px;border-color:#22c55e;">
<strong style="color:#22c55e;"><?php echo htmlspecialchars($success); ?></strong>
</div>
<?php endif; ?>

<div class="panel" style="margin-top:24px;max-width:760px;">

<h2>Router Configuration</h2>

<form method="POST">

<label>Router Name</label>
<input type="text" name="router_name"
value="<?php echo htmlspecialchars($company["router_name"] ?? ""); ?>"
placeholder="Hakim Router">

<label>Router IP</label>
<input type="text" name="router_ip"
value="<?php echo htmlspecialchars($company["router_ip"] ?? ""); ?>"
placeholder="192.168.88.1">

<label>API Username</label>
<input type="text" name="router_user"
value="<?php echo htmlspecialchars($company["router_user"] ?? ""); ?>"
placeholder="admin">

<label>API Password</label>
<input type="password" name="router_pass"
value="<?php echo htmlspecialchars($company["router_pass"] ?? ""); ?>"
placeholder="Router Password">

<label>API Port</label>
<input type="text" name="router_port"
value="<?php echo htmlspecialchars($company["router_port"] ?? "8728"); ?>"
placeholder="8728">

<button class="btn" type="submit">
💾 Save Router Configuration
</button>

</form>

</div>

<div class="grid">

<div class="card">
<h3>Live Features</h3>
<p style="color:#cbd5e1;">
✔ Live Hotspot Users<br>
✔ WAN Monitoring<br>
✔ Voucher Analytics<br>
✔ Queue Monitoring<br>
✔ Router Health
</p>
</div>

<div class="card">
<h3>Security</h3>
<p style="color:#cbd5e1;">
✔ Private Router Isolation<br>
✔ Tenant-only Access<br>
✔ Protected API Credentials<br>
✔ No Shared Data
</p>
</div>

</div>

</div>

</body>
</html>
