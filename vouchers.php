<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$packages = $pdo->query("SELECT * FROM packages WHERE status='active' ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $package_id = $_POST["package_id"];
    $quantity = (int)$_POST["quantity"];

    for ($i = 0; $i < $quantity; $i++) {
        $code = strtoupper("MH-" . substr(md5(uniqid(rand(), true)), 0, 8));

        $stmt = $pdo->prepare("INSERT INTO vouchers (code, package_id, status) VALUES (?, ?, 'unused')");
        $stmt->execute([$code, $package_id]);
    }

    header("Location: vouchers.php");
    exit;
}

$vouchers = $pdo->query("
    SELECT vouchers.*, packages.name AS package_name, packages.price
    FROM vouchers
    LEFT JOIN packages ON vouchers.package_id = packages.id
    ORDER BY vouchers.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Vouchers - M.Hakim Billing System</title>
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
    <a class="active" href="/mhakim-billing-system/vouchers.php">Vouchers</a>
    <a href="/mhakim-billing-system/payments.php">Payments</a>
    <a href="/mhakim-billing-system/analytics.php">Analytics</a>
<a href="/mhakim-billing-system/health_check.php">Health Check</a>
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
            <h1>Vouchers</h1>
            <p>Generate recharge cards for hotspot customers.</p>
        </div>
    </div>

    <div class="card" style="margin-bottom:25px;">
        <h2>Generate Vouchers</h2>

        <form method="POST" class="form-grid">
            <div class="form-group">
                <label>Select Package</label>
                <select name="package_id" required>
                    <?php foreach($packages as $package): ?>
                        <option value="<?php echo $package["id"]; ?>">
                            <?php echo htmlspecialchars($package["name"]); ?> - Ksh <?php echo number_format($package["price"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" min="1" max="100" value="1" required>
            </div>

            <div class="form-group full">
                <button class="btn" type="submit">Generate Vouchers</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Voucher List</h2>

        <table class="table">
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Package</th>
                <th>Price</th>
                <th>Status</th>
                <th>Created</th>
            </tr>

            <?php foreach($vouchers as $voucher): ?>
                <tr>
                    <td><?php echo $voucher["id"]; ?></td>
                    <td><strong><?php echo htmlspecialchars($voucher["code"]); ?></strong></td>
                    <td><?php echo htmlspecialchars($voucher["package_name"]); ?></td>
                    <td class="price">Ksh <?php echo number_format($voucher["price"]); ?></td>
                    <td>
                        <?php if($voucher["status"] === "unused"): ?>
                            <span class="badge-active">unused</span>
                        <?php else: ?>
                            <span class="badge-expired"><?php echo htmlspecialchars($voucher["status"]); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $voucher["created_at"]; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const tables = document.querySelectorAll("table");

    if (!tables.length) return;

    const voucherTable = tables[tables.length - 1];
    const rows = voucherTable.querySelectorAll("tbody tr");

    if (!rows.length) return;

    const dropdownBox = document.createElement("div");

    dropdownBox.style.margin = "15px 0";

    dropdownBox.innerHTML = `
        <label style="font-weight:700; margin-right:10px;">
            Show Vouchers:
        </label>

        <select id="voucherLimit"
            style="
                padding:10px 14px;
                border-radius:10px;
                border:1px solid #cbd5e1;
            ">

            <option value="10">10 vouchers</option>
            <option value="25">25 vouchers</option>
            <option value="50">50 vouchers</option>
            <option value="all">All vouchers</option>

        </select>
    `;

    voucherTable.parentNode.insertBefore(dropdownBox, voucherTable);

    function applyVoucherLimit(limit) {

        rows.forEach((row, index) => {

            if (limit === "all" || index < parseInt(limit)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }

        });

    }

    document.getElementById("voucherLimit")
        .addEventListener("change", function () {

            applyVoucherLimit(this.value);

        });

    applyVoucherLimit("10");

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
