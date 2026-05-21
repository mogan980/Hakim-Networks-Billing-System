<?php
require_once __DIR__ . '/access_guard.php';
session_start();
require_once "config/database.php";
require "vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

if (!isset($_SESSION["user_id"])) {
    header("Location: saas_auth.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("
        INSERT INTO routers(router_name, router_ip, router_username, router_password, api_port, location, status)
        VALUES (?, ?, ?, ?, ?, ?, 'active')
    ");

    $stmt->execute([
        $_POST["router_name"],
        $_POST["router_ip"],
        $_POST["router_username"],
        $_POST["router_password"],
        $_POST["api_port"],
        $_POST["location"]
    ]);

    header("Location: mikrotik.php");
    exit;
}

$routers = $pdo->query("SELECT * FROM routers WHERE status='active' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

function routerStats($router) {
    $data = [
        "connected" => false,
        "identity" => "Offline",
        "uptime" => "-",
        "cpu" => 0,
        "memory" => "-",
        "hotspot_users" => 0,
        "online_users" => 0,
        "queues" => 0,
        "rx" => "0 bps",
        "tx" => "0 bps"
    ];

    try {
        $client = new Client(new Config([
            "host" => $router["router_ip"],
            "user" => $router["router_username"],
            "pass" => $router["router_password"],
            "port" => (int)$router["api_port"],
        ]));

        $identity = $client->query(new Query('/system/identity/print'))->read();
        $resource = $client->query(new Query('/system/resource/print'))->read();
        $users = $client->query(new Query('/ip/hotspot/user/print'))->read();
        $active = $client->query(new Query('/ip/hotspot/active/print'))->read();
        $queues = $client->query(new Query('/queue/simple/print'))->read();

        $data["connected"] = true;
        $data["identity"] = $identity[0]["name"] ?? "MikroTik";
        $data["uptime"] = $resource[0]["uptime"] ?? "-";
        $data["cpu"] = $resource[0]["cpu-load"] ?? 0;
        $data["memory"] = $resource[0]["free-memory"] ?? "-";
        $data["hotspot_users"] = count($users);
        $data["online_users"] = count($active);
        $data["queues"] = count($queues);

    } catch (Exception $e) {
        $data["connected"] = false;
    }

    return $data;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MikroTik Control Center</title>
    <meta http-equiv="refresh" content="20">

    <style>
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial,sans-serif;background:#eef3f8;color:#0f172a}
        .sidebar{width:245px;height:100vh;position:fixed;left:0;top:0;background:#0f172a;color:white;padding:22px}
        .brand h2{margin:0;color:#22c55e}
        .brand p{font-size:12px;color:#94a3b8}
        .sidebar a{display:block;color:white;text-decoration:none;padding:12px;border-radius:12px;margin:8px 0}
        .sidebar a.active,.sidebar a:hover{background:#1e293b}
        .main{margin-left:245px;padding:28px}
        .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
        .top h1{margin:0;font-size:30px}
        .top p{color:#64748b}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:18px;margin-bottom:22px}
        .card{background:white;border-radius:22px;padding:22px;box-shadow:0 12px 30px rgba(15,23,42,.08)}
        .router-card{border:1px solid #e5e7eb}
        .router-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px}
        .router-head h2{margin:0;font-size:21px}
        .router-head p{margin:6px 0 0;color:#64748b;font-size:13px}
        .badge{padding:6px 11px;border-radius:20px;font-size:12px;font-weight:bold}
        .green{background:#dcfce7;color:#166534}
        .orange{background:#ffedd5;color:#9a3412}
        .red{background:#fee2e2;color:#991b1b}
        .blue{background:#dbeafe;color:#1d4ed8}
        .row{display:flex;justify-content:space-between;padding:11px 0;border-bottom:1px solid #e5e7eb;font-size:14px}
        .row:last-child{border-bottom:none}
        .number{font-weight:bold}
        .form-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
        input{width:100%;padding:13px;border:1px solid #cbd5e1;border-radius:12px}
        label{font-weight:bold;font-size:13px;color:#334155}
        .btn{background:#16a34a;color:white;border:0;padding:13px 18px;border-radius:13px;font-weight:bold;cursor:pointer;text-decoration:none}
        .full{grid-column:1/-1}
        .status-box{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:22px}
        .stat h3{margin:0;color:#64748b;font-size:13px}
        .stat strong{display:block;margin-top:8px;font-size:26px}
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <h2>M.Hakim</h2>
        <p>Advanced ISP Billing System</p>
    </div>

    <a href="noc_final_clean.php">Dashboard</a>
<a href="noc.php">NOC Center</a>
    <a href="clients.php">Clients</a>
    <a href="packages.php">Packages</a>
    <a href="vouchers.php">Vouchers</a>
    <a href="payments.php">Payments</a>
<a href="analytics.php">Analytics</a>
<a href="health_check.php">Health Check</a>
    <a class="active" href="mikrotik.php">MikroTik</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <div class="top">
        <div>
            <h1>MikroTik Control Center</h1>
            <p>Manage multiple MikroTik routers, hotspot users, live sync, queues and router health.</p>
        </div>
    </div>

    <div class="card" style="margin-bottom:22px;">
        <h2>Add New MikroTik Router</h2>

        <form method="POST" class="form-grid">
            <div>
                <label>Router Name</label>
                <input type="text" name="router_name" placeholder="Main Router" required>
            </div>

            <div>
                <label>Router IP</label>
                <input type="text" name="router_ip" placeholder="192.168.88.1" required>
            </div>

            <div>
                <label>API Port</label>
                <input type="number" name="api_port" value="8728" required>
            </div>

            <div>
                <label>Username</label>
                <input type="text" name="router_username" placeholder="mhakimapi" required>
            </div>

            <div>
                <label>Password</label>
                <input type="password" name="router_password" placeholder="Router API password" required>
            </div>

            <div>
                <label>Location</label>
                <input type="text" name="location" placeholder="Main Site / Plot A" required>
            </div>

            <div class="full">
                <button class="btn" type="submit">+ Add Router</button>
            </div>
        </form>
    </div>

    <div class="grid">
        <?php foreach($routers as $router): ?>
            <?php $stats = routerStats($router); ?>

            <div class="card router-card">
                <div class="router-head">
                    <div>
                        <h2><?php echo htmlspecialchars($router["router_name"]); ?></h2>
                        <p><?php echo htmlspecialchars($router["location"]); ?> • <?php echo htmlspecialchars($router["router_ip"]); ?></p>
                    </div>

                    <?php if($stats["connected"]): ?>
                        <span class="badge green">Online</span>
                    <?php else: ?>
                        <span class="badge red">Offline</span>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <span>Router Identity</span>
                    <span class="number"><?php echo htmlspecialchars($stats["identity"]); ?></span>
                </div>

                <div class="row">
                    <span>Hotspot User Sync</span>
                    <span class="badge <?php echo $stats["hotspot_users"] > 0 ? 'green' : 'orange'; ?>">
                        <?php echo $stats["hotspot_users"] > 0 ? 'Active' : 'Pending'; ?>
                    </span>
                </div>

                <div class="row">
                    <span>Bandwidth Queue Control</span>
                    <span class="badge <?php echo $stats["queues"] > 0 ? 'green' : 'orange'; ?>">
                        <?php echo $stats["queues"] > 0 ? 'Active' : 'Pending'; ?>
                    </span>
                </div>

                <div class="row">
                    <span>Auto Expiry Engine</span>
                    <span class="badge green">Ready</span>
                </div>

                <div class="row">
                    <span>Online Users</span>
                    <span class="badge blue"><?php echo $stats["online_users"]; ?></span>
                </div>

                <div class="row">
                    <span>Simple Queues</span>
                    <span class="badge blue"><?php echo $stats["queues"]; ?></span>
                </div>

                <div class="row">
                    <span>CPU Load</span>
                    <span class="badge <?php echo $stats["cpu"] > 70 ? 'red' : 'green'; ?>">
                        <?php echo $stats["cpu"]; ?>%
                    </span>
                </div>

                <div class="row">
                    <span>Free Memory</span>
                    <span class="number"><?php echo htmlspecialchars($stats["memory"]); ?></span>
                </div>

                <div class="row">
                    <span>Uptime</span>
                    <span class="number"><?php echo htmlspecialchars($stats["uptime"]); ?></span>
                </div>
            </div>

        <?php endforeach; ?>
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

<script src="mikrotik_live_sync.js"></script>
</body>
</html>
