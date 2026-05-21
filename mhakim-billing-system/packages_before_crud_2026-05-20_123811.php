<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$packages = $pdo->query("SELECT * FROM packages ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Packages - M.Hakim Billing System</title>
    <style>
        body { margin:0; font-family:Arial, sans-serif; background:#f4f6f9; }
        .sidebar { width:240px; height:100vh; background:#111827; color:white; position:fixed; padding:20px; }
        .sidebar h2 { color:#22c55e; margin-bottom:5px; }
        .sidebar p { color:#cbd5e1; font-size:14px; }
        .sidebar a { display:block; color:white; text-decoration:none; padding:12px; margin:8px 0; border-radius:8px; }
        .sidebar a:hover, .active { background:#1f2937; }
        .main { margin-left:280px; padding:30px; }
        .top { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
        .btn { background:#16a34a; color:white; padding:12px 18px; border-radius:10px; text-decoration:none; font-weight:bold; }
        .card { background:white; padding:25px; border-radius:16px; box-shadow:0 8px 25px rgba(0,0,0,.08); }
        table { width:100%; border-collapse:collapse; }
        th { background:#f1f5f9; color:#334155; text-align:left; padding:14px; font-size:14px; }
        td { padding:14px; border-bottom:1px solid #e5e7eb; }
        tr:hover { background:#f9fafb; }
        .status { background:#dcfce7; color:#166534; padding:6px 10px; border-radius:20px; font-size:13px; font-weight:bold; }
        .price { font-weight:bold; color:#16a34a; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>M.Hakim</h2>
    <p>ISP Billing System</p>
    <a href="/mhakim-billing-system/noc_final_clean.php">Dashboard</a>
    <a href="/mhakim-billing-system/noc.php">NOC Center</a>
<a href="/mhakim-billing-system/clients.php">Clients</a>
<a href="/mhakim-billing-system/packages.php">Packages</a>
<a href="/mhakim-billing-system/vouchers.php">Vouchers</a>
<a href="/mhakim-billing-system/payments.php">Payments</a>
    <a href="/mhakim-billing-system/analytics.php">Analytics</a>
<a href="/mhakim-billing-system/health_check.php">Health Check</a>
<a href="/mhakim-billing-system/routers.php">Routers</a>
    <a href="/mhakim-billing-system/mikrotik.php">MikroTik</a>
<a href="/mhakim-billing-system/reports.php">Reports</a>
<a href="/mhakim-billing-system/logout.php">Logout</a>
</div>

<div class="main">
    <div class="top">
        <div>
            <h1>Packages</h1>
            <p>Manage internet plans, speed limits, prices, and duration.</p>
        </div>
        <a href="add_package.php" class="btn">+ Add Package</a>
    </div>

    <div class="card">
        <table>
            <tr>
                <th>ID</th>
                <th>Package</th>
                <th>Duration</th>
                <th>Download</th>
                <th>Upload</th>
                <th>Price</th>
                <th>Status</th>
            </tr>

            <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?php echo $package["id"]; ?></td>
                    <td><strong><?php echo htmlspecialchars($package["name"]); ?></strong></td>
                    <td><?php echo $package["duration_hours"]; ?> hrs</td>
                    <td><?php echo htmlspecialchars($package["speed_down"]); ?></td>
                    <td><?php echo htmlspecialchars($package["speed_up"]); ?></td>
                    <td class="price">Ksh <?php echo number_format($package["price"]); ?></td>
                    <td><span class="status"><?php echo htmlspecialchars($package["status"]); ?></span></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<style>
body {
    background: #f1f5f9 !important;
    color: #0f172a !important;
}

main, .main, .content, .card, .box, .panel, table {
    background: #ffffff !important;
    color: #0f172a !important;
}

h1, h2, h3, p, label, td, th, span {
    color: #0f172a !important;
}

.sidebar, .sidebar *, nav, nav * {
    color: #e5e7eb !important;
}
</style>

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
