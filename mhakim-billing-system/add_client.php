<?php
session_start();
require_once "config/database.php";
require 'vendor/autoload.php';

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

if (!isset($_SESSION["user_id"])) {
    header("Location: saas_auth.php");
    exit;
}

$packages = $pdo->query("SELECT * FROM packages WHERE status='active' ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $package_id = $_POST["package_id"];
$pkg = $pdo->prepare("SELECT duration_hours FROM packages WHERE id=?");
$pkg->execute([$package_id]);
$pkg = $pkg->fetch(PDO::FETCH_ASSOC);

$duration_minutes = round($pkg["duration_hours"] * 60);
$check = $pdo->prepare("SELECT id FROM clients WHERE username=? LIMIT 1");
$check->execute([$username]);

if ($check->fetch()) {
    die("<h2 style='color:red'>Username already exists. Use another username.</h2><a href='add_client.php'>Go Back</a>");
}

    $stmt = $pdo->prepare("
        INSERT INTO clients
(full_name, phone, username, password, package_id, status, starts_at, expires_at)
VALUES (?, ?, ?, ?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL ? MINUTE))
    ");

    $stmt->execute([
    $full_name,
    $phone,
    $username,
    $password,
    $package_id,
    $duration_minutes
]);
try {

    $settings = $pdo->query("
        SELECT * FROM mikrotik_settings 
        ORDER BY id DESC 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if ($settings) {

        $config = new Config([
            'host' => $settings["router_ip"],
            'user' => $settings["router_username"],
            'pass' => $settings["router_password"],
            'port' => (int)$settings["api_port"],
        ]);

        $api = new Client($config);

        $query = new Query('/ip/hotspot/user/add');

        $query
            ->equal('name', $username)
            ->equal('password', $password);

        $api->query($query)->read();
    }

} catch (Exception $e) {

    echo "<div style='color:red'>MikroTik Error: " . $e->getMessage() . "</div>";
}
try {

    $config = new Config([
        'host' => '192.168.88.1',
        'user' => 'mhakimapi',
        'pass' => '12345678',
        'port' => 8728,
    ]);

    $clientAPI = new Client($config);

    $addUser = new Query('/ip/hotspot/user/add');

    $addUser
        ->equal('name', $username)
        ->equal('password', $password);

    $clientAPI->query($addUser)->read();

} catch (Exception $e) {

    echo "MikroTik Error: " . $e->getMessage();
}
    header("Location: clients.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Client - M.Hakim Billing System</title>
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
<a href="/mhakim-billing-system/mikrotik.php">MikroTik</a>
<a href="/mhakim-billing-system/reports.php">Reports</a>
<a href="/mhakim-billing-system/logout.php">Logout</a>
</div>

<div class="main">

    <div class="page-header">
        <div>
            <h1>Add Client</h1>
            <p>Create a hotspot customer and assign an internet package.</p>
        </div>

        <a href="clients.php" class="btn btn-secondary">← Back to Clients</a>
    </div>

    <div class="card">
        <form method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="e.g. Mogan Hakim" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="e.g. 0700000000" required>
                </div>

                <div class="form-group">
                    <label>Hotspot Username</label>
                    <input type="text" name="username" placeholder="e.g. hakim001" required>
                </div>

                <div class="form-group">
                    <label>Hotspot Password</label>
                    <input type="text" name="password" placeholder="e.g. 1234" required>
                </div>

                <div class="form-group full">
                    <label>Select Package</label>
                    <select name="package_id" required>
                        <?php foreach($packages as $package): ?>
                            <option value="<?php echo $package["id"]; ?>">
                                <?php echo htmlspecialchars($package["name"]); ?>
                                —
                                <?php echo htmlspecialchars($package["speed_down"]); ?>
                                /
                                <?php echo htmlspecialchars($package["speed_up"]); ?>
                                —
                                Ksh <?php echo number_format($package["price"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="actions">
                <button type="submit" class="btn">Save Client</button>
                <a href="clients.php" class="btn btn-secondary">Cancel</a>
            </div>

        </form>
    </div>

</div>

</body>
</html>
