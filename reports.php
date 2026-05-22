<?php
require_once __DIR__ . '/access_guard.php';
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: saas_auth.php");
    exit;
}

$totalRevenue = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn();
$todayRevenue = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()")->fetchColumn();
$totalClients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$activeClients = $pdo->query("SELECT COUNT(*) FROM clients WHERE status='active'")->fetchColumn();
$totalVouchers = $pdo->query("SELECT COUNT(*) FROM vouchers")->fetchColumn();
$usedVouchers = $pdo->query("SELECT COUNT(*) FROM vouchers WHERE status='used'")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports - M.Hakim Billing System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .report-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:25px}
        .report-card{background:white;padding:24px;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.08)}
        .report-card span{color:#64748b;font-weight:bold;font-size:14px}
        .report-card h2{margin:12px 0 0;font-size:30px}
        .green{color:#16a34a}.blue{color:#2563eb}.orange{color:#f97316}
    </style>
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
    <a href="/mhakim-billing-system/payments.php">Payments</a>
    <a href="/mhakim-billing-system/analytics.php">Analytics</a>
    <a href="/mhakim-billing-system/pppoe.php">PPPoE</a>
    <a href="/mhakim-billing-system/routers.php">Routers</a>
    <a href="/mhakim-billing-system/mikrotik.php">MikroTik</a>
    <a href="/mhakim-billing-system/backups.php">Backups</a>
    <a href="/mhakim-billing-system/users.php">Users</a>

    <a class="active" href="/mhakim-billing-system/reports.php">Reports</a>
    <a href="/mhakim-billing-system/logout.php">Logout</a>
</div>

<div class="main">

    <div class="page-header">
        <div>
            <h1>Reports</h1>
            <p>Revenue, clients, vouchers and ISP business performance summary.</p>
        </div>
    </div>

    <div class="report-grid">
        <div class="report-card">
            <span>Total Revenue</span>
            <h2 class="green">Ksh <?php echo number_format($totalRevenue); ?></h2>
        </div>

        <div class="report-card">
            <span>Today Revenue</span>
            <h2 class="blue">Ksh <?php echo number_format($todayRevenue); ?></h2>
        </div>

        <div class="report-card">
            <span>Total Clients</span>
            <h2><?php echo $totalClients; ?></h2>
        </div>

        <div class="report-card">
            <span>Active Clients</span>
            <h2 class="green"><?php echo $activeClients; ?></h2>
        </div>
    </div>

    <div class="report-grid">
        <div class="report-card">
            <span>Total Vouchers</span>
            <h2 class="blue"><?php echo $totalVouchers; ?></h2>
        </div>

        <div class="report-card">
            <span>Used Vouchers</span>
            <h2 class="orange"><?php echo $usedVouchers; ?></h2>
        </div>

        <div class="report-card">
            <span>Collection Rate</span>
            <h2 class="green"><?php echo $totalClients > 0 ? round(($activeClients / $totalClients) * 100) : 0; ?>%</h2>
        </div>

        <div class="report-card">
            <span>System Status</span>
            <h2 class="green">Live</h2>
        </div>
    </div>

    <?php
$latestPayments = $pdo->query("
    SELECT payments.*, clients.full_name, packages.name AS package_name
    FROM payments
    LEFT JOIN clients ON payments.client_id = clients.id
    LEFT JOIN packages ON payments.package_id = packages.id
    ORDER BY payments.id DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$packageStats = $pdo->query("
    SELECT 
        packages.id,
        packages.name,
        packages.price,
        (
            SELECT COUNT(*) 
            FROM clients 
            WHERE clients.package_id = packages.id
        ) AS users_count
    FROM packages
    ORDER BY users_count DESC, packages.price ASC
")->fetchAll(PDO::FETCH_ASSOC);
$expiredClients = $pdo->query("SELECT COUNT(*) FROM clients WHERE status='expired'")->fetchColumn();
$blockedClients = $pdo->query("SELECT COUNT(*) FROM clients WHERE status='blocked'")->fetchColumn();
$unusedVouchers = $pdo->query("SELECT COUNT(*) FROM vouchers WHERE status='unused'")->fetchColumn();
?>

<div class="card">
    <h2>Business Summary</h2>

    <div class="report-grid">
        <div class="report-card">
            <span>Expired Clients</span>
            <h2 class="orange"><?php echo $expiredClients; ?></h2>
        </div>

        <div class="report-card">
            <span>Blocked Clients</span>
            <h2 class="orange"><?php echo $blockedClients; ?></h2>
        </div>

        <div class="report-card">
            <span>Unused Vouchers</span>
            <h2 class="blue"><?php echo $unusedVouchers; ?></h2>
        </div>

        <div class="report-card">
            <span>Average Revenue/User</span>
            <h2 class="green">Ksh <?php echo $totalClients > 0 ? number_format($totalRevenue / $totalClients) : 0; ?></h2>
        </div>
    </div>

    <h2>Latest Payments</h2>

    <table class="table">
        <tr>
            <th>Client</th>
            <th>Package</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Date</th>
        </tr>

        <?php foreach($latestPayments as $payment): ?>
            <tr>
                <td><?php echo htmlspecialchars($payment["full_name"] ?? "Walk-in"); ?></td>
                <td><?php echo htmlspecialchars($payment["package_name"] ?? "-"); ?></td>
                <td class="price">Ksh <?php echo number_format($payment["amount"]); ?></td>
                <td><?php echo htmlspecialchars($payment["method"]); ?></td>
                <td><?php echo $payment["created_at"]; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <br>

    <h2>Package Performance</h2>

    <table class="table">
        <tr>
            <th>Package</th>
            <th>Price</th>
            <th>Subscribed Clients</th>
        </tr>

        <?php foreach($packageStats as $package): ?>
            <tr>
                <td><?php echo htmlspecialchars($package["name"]); ?></td>
                <td class="price">Ksh <?php echo number_format($package["price"]); ?></td>
                <td><?php echo $package["users_count"]; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</div>


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

</body>
</html>
