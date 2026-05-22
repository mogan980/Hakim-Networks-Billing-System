<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];

$stmt = $pdo->prepare("SELECT * FROM companies WHERE id=? LIMIT 1");
$stmt->execute([$companyId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

$msg = "";

if(isset($_POST["reconnect"])){

    $router_ip   = trim($_POST["router_ip"] ?? "");
    $router_user = trim($_POST["router_user"] ?? "");
    $router_pass = trim($_POST["router_pass"] ?? "");
    $router_port = trim($_POST["router_port"] ?? "8728");
    $router_name = trim($_POST["router_name"] ?? "");

    $pdo->prepare("
        UPDATE companies
        SET
            router_ip=?,
            router_user=?,
            router_pass=?,
            router_port=?,
            router_name=?,
            router_connected=1
        WHERE id=?
    ")->execute([
        $router_ip,
        $router_user,
        $router_pass,
        $router_port,
        $router_name,
        $companyId
    ]);

    $pdo->prepare("
        INSERT INTO tenant_router_logs
        (company_id,action_type,router_ip,message)
        VALUES (?,?,?,?)
    ")->execute([
        $companyId,
        "reconnect",
        $router_ip,
        "Tenant reconnected MikroTik router"
    ]);

    header("Location: tenant_router.php");
    exit;
}



if(isset($_POST["disconnect"])){

    $pdo->prepare("
        UPDATE companies
        SET router_connected=0
        WHERE id=?
    ")->execute([$companyId]);

    $pdo->prepare("
        INSERT INTO tenant_router_logs
        (company_id,action_type,router_ip,message)
        VALUES (?,?,?,?)
    ")->execute([
        $companyId,
        "disconnect",
        $company["router_ip"] ?? "",
        "Tenant disconnected MikroTik router"
    ]);

    header("Location: tenant_router.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["disconnect"])) {

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

    $pdo->prepare("
        INSERT INTO tenant_router_logs
        (company_id,action_type,router_ip,message)
        VALUES (?,?,?,?)
    ")->execute([
        $companyId,
        "connect",
        $router_ip,
        "Tenant connected MikroTik router"
    ]);

    $msg = "Router configuration saved.";

    $stmt->execute([$companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
}

$logs = $pdo->prepare("
    SELECT *
    FROM tenant_router_logs
    WHERE company_id=?
    ORDER BY id DESC
    LIMIT 20
");
$logs->execute([$companyId]);
$routerLogs = $logs->fetchAll(PDO::FETCH_ASSOC);
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
<h1>Tenant MikroTik Center</h1>
<p>Secure isolated MikroTik management for your ISP.</p>
</div>

<?php if($msg): ?>
<div class="panel" style="margin-top:18px;color:#22c55e;font-weight:900;">
<?php echo htmlspecialchars($msg); ?>
</div>
<?php endif; ?>

<div class="grid">

<div class="panel">

<h2>Router Configuration</h2>

<form method="POST">

<input type="text" name="router_name"
placeholder="Router Name"
value="<?php echo htmlspecialchars($company["router_name"] ?? ""); ?>">

<input type="text" name="router_ip"
placeholder="Router IP"
value="<?php echo htmlspecialchars($company["router_ip"] ?? ""); ?>">

<input type="text" name="router_user"
placeholder="API Username"
value="<?php echo htmlspecialchars($company["router_user"] ?? ""); ?>">

<input type="password" name="router_pass"
placeholder="API Password"
value="<?php echo htmlspecialchars($company["router_pass"] ?? ""); ?>">

<input type="text" name="router_port"
placeholder="API Port"
value="<?php echo htmlspecialchars($company["router_port"] ?? "8728"); ?>">

<button class="btn" type="submit">
💾 Save Router
</button>

<button class="btn" name="reconnect" value="1" type="submit" style="background:#2563eb;color:white;margin-top:10px;">
🔄 Reconnect Router
</button>

</form>

<form method="POST" style="margin-top:12px;">
<input type="hidden" name="disconnect" value="1">
<button class="btn" style="background:#dc2626;color:white;">
🔌 Disconnect Router
</button>
</form>




</div>

<div class="panel">

<h2>Router Connection History</h2>

<table>
<tr>
<th>Action</th>
<th>Router IP</th>
<th>Date</th>
</tr>

<?php foreach($routerLogs as $log): ?>

<tr>
<td><?php echo htmlspecialchars($log["action_type"]); ?></td>
<td><?php echo htmlspecialchars($log["router_ip"]); ?></td>
<td><?php echo htmlspecialchars($log["created_at"]); ?></td>
</tr>

<?php endforeach; ?>

</table>

</div>

</div>

</div>

</body>
</html>
