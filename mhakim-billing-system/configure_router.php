<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: saas_auth.php");
    exit;
}

$message = "";

$settings = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $router_ip = trim($_POST["router_ip"]);
    $router_username = trim($_POST["router_username"]);
    $router_password = trim($_POST["router_password"]);
    $api_port = trim($_POST["api_port"]);

    $pdo->query("DELETE FROM mikrotik_settings");

    $stmt = $pdo->prepare("
        INSERT INTO mikrotik_settings 
        (router_ip, router_username, router_password, api_port)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $router_ip,
        $router_username,
        $router_password,
        $api_port
    ]);

    header("Location: mikrotik.php?saved=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Configure Router - M.Hakim Billing System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <h2>M.Hakim</h2>
        <p>Advanced ISP Billing System</p>
    </div>

    <a href="/mhakim-billing-system/noc_final_clean.php">Dashboard</a>
    <a href="/mhakim-billing-system/clients.php">Clients</a>
    <a href="/mhakim-billing-system/packages.php">Packages</a>
    <a href="/mhakim-billing-system/vouchers.php">Vouchers</a>
    <a href="/mhakim-billing-system/payments.php">Payments</a>
    <a class="active" href="/mhakim-billing-system/mikrotik.php">MikroTik</a>
    <a href="/mhakim-billing-system/reports.php">Reports</a>
    <a href="/mhakim-billing-system/logout.php">Logout</a>
</div>

<div class="main">

    <div class="page-header">
        <div>
            <h1>Configure MikroTik Router</h1>
            <p>Save router API details for hotspot user activation.</p>
        </div>

        <a class="btn btn-secondary" href="mikrotik.php">← Back</a>
    </div>

    <div class="card">
        <form method="POST" class="form-grid">

            <div class="form-group">
                <label>Router IP Address</label>
                <input type="text" name="router_ip" value="<?php echo htmlspecialchars($settings["router_ip"] ?? "192.168.88.1"); ?>" required>
            </div>

            <div class="form-group">
                <label>API Port</label>
                <input type="number" name="api_port" value="<?php echo htmlspecialchars($settings["api_port"] ?? "8728"); ?>" required>
            </div>

            <div class="form-group">
                <label>Router Username</label>
                <input type="text" name="router_username" value="<?php echo htmlspecialchars($settings["router_username"] ?? "admin"); ?>" required>
            </div>

            <div class="form-group">
                <label>Router Password</label>
                <input type="password" name="router_password" value="<?php echo htmlspecialchars($settings["router_password"] ?? "12345678">
            </div>

            <div class="form-group full">
                <button class="btn" type="submit">Save Router Details</button>
            </div>

        </form>
    </div>

</div>

</body>
</html>
