<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$clients = $pdo->query("SELECT * FROM clients ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$packages = $pdo->query("SELECT * FROM packages WHERE status='active' ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $client_id = $_POST["client_id"] ?: null;
    $package_id = $_POST["package_id"] ?: null;
    $amount = $_POST["amount"];
    $method = $_POST["method"];
    $reference = trim($_POST["reference"]);

    $stmt = $pdo->prepare("
        INSERT INTO payments (client_id, package_id, amount, method, reference, status)
        VALUES (?, ?, ?, ?, ?, 'paid')
    ");

    $stmt->execute([$client_id, $package_id, $amount, $method, $reference]);

    header("Location: payments.php");
    exit;
}

$payments = $pdo->query("
    SELECT payments.*, clients.full_name, packages.name AS package_name
    FROM payments
    LEFT JOIN clients ON payments.client_id = clients.id
    LEFT JOIN packages ON payments.package_id = packages.id
    ORDER BY payments.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payments - M.Hakim Billing System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <h2>M.Hakim</h2>
        <p>Advanced ISP Billing System</p>
    </div>

    <a href="/mhakim-billing-system/noc_final_clean.php">Dashboard</a>
    <a href="/mhakim-billing-system/noc.php">NOC Center</a>
    <a href="/mhakim-billing-system/clients.php">Clients</a>
    <a href="/mhakim-billing-system/packages.php">Packages</a>
    <a href="/mhakim-billing-system/vouchers.php">Vouchers</a>
    <a class="active" href="/mhakim-billing-system/payments.php">Payments</a>
    <a href="/mhakim-billing-system/pppoe.php">PPPoE</a>
    <a href="/mhakim-billing-system/routers.php">Routers</a>
    <a href="/mhakim-billing-system/mikrotik.php">MikroTik</a>
    <a href="/mhakim-billing-system/backups.php">Backups</a>
    <a href="/mhakim-billing-system/users.php">Users</a>

    <a href="/mhakim-billing-system/reports.php">Reports</a>
    <a href="/mhakim-billing-system/logout.php">Logout</a>
</div>

<div class="main">

    <div class="page-header">
        <div>
            <h1>Payments</h1>
            <p>Record client payments and track ISP revenue.</p>
        </div>
    </div>

    <div class="card" style="margin-bottom:25px;">
        <h2>Record Payment</h2>

        <form method="POST" class="form-grid">
            <div class="form-group">
                <label>Client</label>
                <select name="client_id">
                    <option value="">Walk-in / Voucher Buyer</option>
                    <?php foreach($clients as $client): ?>
                        <option value="<?php echo $client["id"]; ?>">
                            <?php echo htmlspecialchars($client["full_name"]); ?> - <?php echo htmlspecialchars($client["username"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Package</label>
                <select name="package_id">
                    <option value="">No Package</option>
                    <?php foreach($packages as $package): ?>
                        <option value="<?php echo $package["id"]; ?>">
                            <?php echo htmlspecialchars($package["name"]); ?> - Ksh <?php echo number_format($package["price"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Amount</label>
                <input type="number" name="amount" placeholder="e.g. 50" required>
            </div>

            <div class="form-group">
                <label>Method</label>
                <select name="method" required>
                    <option value="cash">Cash</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="voucher">Voucher</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Reference</label>
                <input type="text" name="reference" placeholder="M-Pesa code, cash note, voucher code">
            </div>

            <div class="form-group full">
                <button class="btn" type="submit">Save Payment</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Payment History</h2>

        <table class="table">
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Package</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Date</th>
            </tr>

            <?php foreach($payments as $payment): ?>
                <tr>
                    <td><?php echo $payment["id"]; ?></td>
                    <td><?php echo htmlspecialchars($payment["full_name"] ?? "Walk-in"); ?></td>
                    <td><?php echo htmlspecialchars($payment["package_name"] ?? "-"); ?></td>
                    <td class="price">Ksh <?php echo number_format($payment["amount"]); ?></td>
                    <td><?php echo htmlspecialchars($payment["method"]); ?></td>
                    <td><?php echo htmlspecialchars($payment["reference"]); ?></td>
                    <td><?php echo $payment["created_at"]; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const tables = document.querySelectorAll("table");
    if (!tables.length) return;

    const paymentTable = tables[tables.length - 1];
    const rows = paymentTable.querySelectorAll("tbody tr");

    if (!rows.length) return;

    const controlBox = document.createElement("div");
    controlBox.style.margin = "15px 0";
    controlBox.innerHTML = `
        <label style="font-weight:700; margin-right:10px;">Show Payments:</label>
        <select id="paymentLimit" style="padding:10px 14px; border-radius:10px; border:1px solid #cbd5e1;">
            <option value="10">10 payments</option>
            <option value="25">25 payments</option>
            <option value="50">50 payments</option>
            <option value="all">All payments</option>
        </select>
    `;

    paymentTable.parentNode.insertBefore(controlBox, paymentTable);

    function applyLimit(limit) {
        rows.forEach((row, index) => {
            if (limit === "all" || index < parseInt(limit)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    document.getElementById("paymentLimit").addEventListener("change", function () {
        applyLimit(this.value);
    });

    applyLimit("10");
});
</script>

<style>
.sidebar{
    width:260px !important;
    height:100vh !important;
    position:fixed !important;
    left:0 !important;
    top:0 !important;
    overflow-y:auto !important;
    overflow-x:hidden !important;
    background:linear-gradient(180deg,#020617,#071827,#052e2b) !important;
    padding:18px 12px !important;
    scrollbar-width:thin;
    scrollbar-color:#22c55e #020617;
}
.sidebar::-webkit-scrollbar{width:6px;}
.sidebar::-webkit-scrollbar-track{background:#020617;}
.sidebar::-webkit-scrollbar-thumb{background:#22c55e;border-radius:20px;}
.sidebar h2{font-size:22px !important;margin:0 0 4px !important;color:#fff !important;}
.sidebar p{font-size:12px !important;color:#94a3b8 !important;margin:0 0 14px !important;}
.sidebar a{
    display:flex !important;
    align-items:center !important;
    gap:10px !important;
    padding:10px 12px !important;
    margin:4px 0 !important;
    border-radius:12px !important;
    color:#dbeafe !important;
    font-size:13px !important;
    font-weight:700 !important;
    text-decoration:none !important;
    transition:.25s !important;
    white-space:nowrap !important;
}
.sidebar a:hover,.sidebar a.active,.sidebar .active{
    background:rgba(34,197,94,.18) !important;
    color:#fff !important;
    transform:translateX(4px);
}
.sidebar a[href*="dashboard"]::before{content:"📊";}
.sidebar a[href*="clients"]::before{content:"👥";}
.sidebar a[href*="packages"]::before{content:"📦";}
.sidebar a[href*="vouchers"]::before{content:"🎟️";}
.sidebar a[href*="payments"]::before{content:"💳";}
.sidebar a[href*="pppoe"]::before{content:"🌐";}
.sidebar a[href*="routers"]::before{content:"🛰️";}
.sidebar a[href*="mikrotik"]::before{content:"📡";}
.sidebar a[href*="backups"]::before{content:"🛡️";}
.sidebar a[href*="reports"]::before{content:"📈";}
.sidebar a[href*="logout"]::before{content:"🚪";}
.main{margin-left:280px !important;}
@media(max-width:900px){
    .sidebar{position:relative !important;width:100% !important;height:auto !important;max-height:60vh !important;}
    .main{margin-left:0 !important;}
}
</style>

<script src="mikrotik_live_sync.js"></script>
</body>
</html>
