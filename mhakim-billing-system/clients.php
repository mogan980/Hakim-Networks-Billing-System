<?php
$tenantCompanyId = $_SESSION["tenant_company_id"] ?? null;
$isSuperAdmin = ($_SESSION["tenant_role"] ?? "") === "super_admin";

require_once __DIR__ . '/access_guard.php';
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: saas_auth.php");
    exit;
}

$clients = $pdo->query("
    SELECT clients.*, packages.name AS package_name
    FROM clients
    LEFT JOIN packages ON clients.package_id = packages.id
    ORDER BY clients.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Clients - M.Hakim Billing System</title>
    <style>
        body { margin:0; font-family:Arial; background:#f4f6f9; }
        .sidebar { width:240px; height:100vh; background:#111827; color:white; position:fixed; padding:20px; }
        .sidebar h2 { color:#22c55e; }
        .sidebar a { display:block; color:white; text-decoration:none; padding:12px; margin:8px 0; border-radius:8px; }
        .sidebar a:hover, .active { background:#1f2937; }
        .main { margin-left:280px; padding:30px; }
        .top { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
        .btn {
            background:#16a34a;
            color:white;
            padding:12px 18px;
            border-radius:10px;
            text-decoration:none;
            font-weight:bold;
        }
        .card {
            background:white;
            padding:25px;
            border-radius:16px;
            box-shadow:0 8px 25px rgba(0,0,0,.08);
        }
        table {
            width:100%;
            border-collapse:collapse;
        }
        th {
            background:#f1f5f9;
            padding:14px;
            text-align:left;
        }
        td {
            padding:14px;
            border-bottom:1px solid #e5e7eb;
        }
        .status-active {
            background:#dcfce7;
            color:#166534;
            padding:6px 10px;
            border-radius:20px;
            font-size:13px;
            font-weight:bold;
        }
        .status-expired {
            background:#fee2e2;
            color:#991b1b;
            padding:6px 10px;
            border-radius:20px;
            font-size:13px;
            font-weight:bold;
        }
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
            <h1>Clients</h1>
            <p>Manage hotspot users and subscriptions.</p>
        </div>

        <a href="add_client.php" class="btn">+ Add Client</a>
    </div>

    <div class="card">
<div class="search-box">
    <input type="text" id="clientSearch" placeholder="Search client, phone, username...">
</div>

<style>
.search-box{
    margin-bottom:18px;
}

.search-box input{
    width:100%;
    padding:14px 16px;
    border-radius:14px;
    border:1px solid #cbd5e1;
    background:#fff;
    font-size:15px;
    box-shadow:0 8px 20px rgba(0,0,0,.05);
}
</style>
        <table>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Phone</th>
                <th>Username</th>
                <th>Package</th>
                <th>Status</th>
                <th>Bought At</th>
                <th>Expiry</th>
            </tr>

            <?php foreach ($clients as $client): ?>

            <tr>
                <td><?php echo $client["id"]; ?></td>

                <td>
                    <strong>
                        <?php echo htmlspecialchars($client["full_name"]); ?>
                    </strong>
                </td>

                <td><?php echo htmlspecialchars($client["phone"]); ?></td>

                <td><?php echo htmlspecialchars($client["username"]); ?></td>

                <td>
                    <?php echo htmlspecialchars($client["package_name"] ?? "None"); ?>
                </td>

                <td>
                    <?php if ($client["status"] === "active"): ?>
                        <span class="status-active">active</span>
                    <?php else: ?>
                        <span class="status-expired">expired</span>
                    <?php endif; ?>
                </td>

                <td>
    <?php echo $client["starts_at"] ?: "-"; ?>
</td>

<td>
    <?php echo $client["expires_at"] ?: "-"; ?>
</td>
            </tr>

            <?php endforeach; ?>

        </table>

    </div>

</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const tables = document.querySelectorAll("table");
    if (!tables.length) return;

    const clientTable = tables[tables.length - 1];
    const rows = clientTable.querySelectorAll("tbody tr");
    if (!rows.length) return;

    const box = document.createElement("div");
    box.style.margin = "15px 0";
    box.innerHTML = `
        <label style="font-weight:700; margin-right:10px;">Show Clients:</label>
        <select id="clientLimit" style="padding:10px 14px; border-radius:10px; border:1px solid #cbd5e1;">
            <option value="10">10 clients</option>
            <option value="25">25 clients</option>
            <option value="50">50 clients</option>
            <option value="all">All clients</option>
        </select>
    `;

    clientTable.parentNode.insertBefore(box, clientTable);

    function applyLimit(limit) {
        rows.forEach((row, index) => {
            row.style.display = (limit === "all" || index < parseInt(limit)) ? "" : "none";
        });
    }

    document.getElementById("clientLimit").addEventListener("change", function () {
        applyLimit(this.value);
    });

    applyLimit("10");
});
</script>
<script>
const clientSearch=document.getElementById("clientSearch");

if(clientSearch){

    clientSearch.addEventListener("keyup",function(){

        const value=this.value.toLowerCase();

        const rows=document.querySelectorAll("table tbody tr");

        rows.forEach(row=>{

            const text=row.innerText.toLowerCase();

            row.style.display=text.includes(value) ? "" : "none";

        });

    });

}
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
