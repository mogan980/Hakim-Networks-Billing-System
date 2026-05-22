<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Query;

$message = "";
$error = "";

function mt_connect() {
    $config = require __DIR__ . "/config/mikrotik.php";

    $hosts = [
        $config["host"] ?? "192.168.88.1",
        "192.168.88.1",
        "10.10.10.1"
    ];

    $last = "";

    foreach (array_unique($hosts) as $host) {
        try {
            return new Client([
                "host" => $host,
                "user" => $config["user"],
                "pass" => $config["pass"],
                "port" => $config["port"],
                "timeout" => 5
            ]);
        } catch(Exception $e) {
            $last = $e->getMessage();
        }
    }

    throw new Exception($last ?: "MikroTik connection failed");
}

try {
    $client = mt_connect();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $action = $_POST["action"] ?? "";

        if ($action === "add") {
            $username = trim($_POST["username"] ?? "");
            $password = trim($_POST["password"] ?? "");
            $profile = trim($_POST["profile"] ?? "PPPOE-10M");
            $comment = trim($_POST["comment"] ?? "");

            if (!$username || !$password) {
                throw new Exception("Username and password are required.");
            }

            $existing = $client->query(
                (new Query("/ppp/secret/print"))->where("name", $username)
            )->read();

            if (!empty($existing)) {
                throw new Exception("PPPoE username already exists.");
            }

            $client->query(
                (new Query("/ppp/secret/add"))
                    ->equal("name", $username)
                    ->equal("password", $password)
                    ->equal("service", "pppoe")
                    ->equal("profile", $profile)
                    ->equal("comment", $comment)
            )->read();

            $message = "PPPoE client added successfully.";
        }

        if ($action === "delete") {
            $id = $_POST["id"] ?? "";
            $name = $_POST["name"] ?? "";

            if ($name) {
                $active = $client->query(
                    (new Query("/ppp/active/print"))->where("name", $name)
                )->read();

                foreach ($active as $a) {
                    if (!empty($a[".id"])) {
                        $client->query(
                            (new Query("/ppp/active/remove"))->equal(".id", $a[".id"])
                        )->read();
                    }
                }
            }

            if ($id) {
                $client->query(
                    (new Query("/ppp/secret/remove"))->equal(".id", $id)
                )->read();
            }

            $message = "PPPoE client deleted successfully.";
        }

        if ($action === "toggle") {
            $id = $_POST["id"] ?? "";
            $disabled = $_POST["disabled"] ?? "false";
            $newState = $disabled === "true" ? "false" : "true";

            if ($id) {
                $client->query(
                    (new Query("/ppp/secret/set"))
                        ->equal(".id", $id)
                        ->equal("disabled", $newState)
                )->read();
            }

            $message = $newState === "true" ? "PPPoE client disabled." : "PPPoE client enabled.";
        }

        if ($action === "disconnect") {
            $name = $_POST["name"] ?? "";

            $active = $client->query(
                (new Query("/ppp/active/print"))->where("name", $name)
            )->read();

            foreach ($active as $a) {
                if (!empty($a[".id"])) {
                    $client->query(
                        (new Query("/ppp/active/remove"))->equal(".id", $a[".id"])
                    )->read();
                }
            }

            $message = "PPPoE user disconnected.";
        }
    }

    $profiles = $client->query(new Query("/ppp/profile/print"))->read();
    $secrets = $client->query(new Query("/ppp/secret/print"))->read();
    $active = $client->query(new Query("/ppp/active/print"))->read();

} catch(Exception $e) {
    $error = $e->getMessage();
    $profiles = [];
    $secrets = [];
    $active = [];
}

$activeNames = [];
foreach ($active as $a) {
    if (!empty($a["name"])) $activeNames[$a["name"]] = $a;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>PPPoE Management - Hakim Networks</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Inter,Arial,sans-serif;background:#eef3f8;color:#071327}
.sidebar{position:fixed;left:0;top:0;width:252px;height:100vh;background:#050b1d;color:white;padding:28px 18px}
.logo{font-size:22px;font-weight:900;margin-bottom:28px}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px 14px;border-radius:10px;margin:7px 0;font-size:14px}
.sidebar a.active,.sidebar a:hover{background:#123b35}
.main{margin-left:252px;padding:36px 36px 70px}
.hero{display:flex;justify-content:space-between;gap:20px;align-items:center;margin-bottom:24px}
.hero h1{font-size:32px;margin:0}
.hero p{margin:8px 0 0;color:#526072}
.badge{background:#dcfce7;color:#166534;padding:9px 14px;border-radius:999px;font-weight:800}
.alert{padding:14px 16px;border-radius:12px;margin:14px 0;font-weight:700}
.success{background:#dcfce7;color:#166534}.error{background:#fee2e2;color:#991b1b}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:28px}
.card{background:white;border-radius:20px;padding:24px;box-shadow:0 18px 45px rgba(15,23,42,.08)}
.card h2{margin-top:0}
label{font-weight:700;font-size:14px}
input,select{width:100%;padding:13px 14px;border:1px solid #cbd5e1;border-radius:11px;margin:7px 0 14px;font-size:14px}
button{border:0;border-radius:11px;padding:12px 16px;font-weight:800;cursor:pointer}
.btn{background:#16a34a;color:white}.danger{background:#dc2626;color:white}.warn{background:#f59e0b;color:#111827}.dark{background:#0f172a;color:white}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.stat{background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:18px}
.stat b{font-size:28px;display:block}
.toolbar{display:flex;justify-content:space-between;gap:12px;align-items:center;margin:22px 0}
.search{max-width:360px}
table{width:100%;border-collapse:collapse;background:white;border-radius:18px;overflow:hidden;box-shadow:0 18px 45px rgba(15,23,42,.08)}
th{background:#050b1d;color:white;text-align:left;padding:14px;font-size:13px}
td{padding:14px;border-bottom:1px solid #e5e7eb;font-size:14px}
.pill{padding:7px 10px;border-radius:999px;font-weight:800;font-size:12px}
.online{background:#dcfce7;color:#166534}.offline{background:#e5e7eb;color:#374151}.disabled{background:#fee2e2;color:#991b1b}
.actions{display:flex;gap:7px;flex-wrap:wrap}
.actions form{display:inline}
@media(max-width:900px){.sidebar{position:relative;width:100%;height:auto}.main{margin-left:0;padding:20px}.grid{grid-template-columns:1fr}.stats{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">M.Hakim</div>
    <a href="dashboard.php">Dashboard</a>
    <a href="noc_final_clean.php">NOC Center</a>
    <a href="clients.php">Clients</a>
    <a href="packages.php">Packages</a>
    <a href="vouchers.php">Vouchers</a>
    <a href="payments.php">Payments</a>
    <a class="active" href="pppoe.php">🌐 PPPoE</a>
    <a href="mikrotik.php">MikroTik</a>
    <a href="router_backups.php">Backups</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <div class="hero">
        <div>
            <h1>PPPoE Management</h1>
            <p>Create, monitor, suspend and delete PPPoE clients directly from MikroTik.</p>
        </div>
        <span class="badge">Router Linked</span>
    </div>

    <?php if($message): ?><div class="alert success"><?=htmlspecialchars($message)?></div><?php endif; ?>
    <?php if($error): ?><div class="alert error"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <div class="grid">
        <div class="card">
            <h2>Add PPPoE Client</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <label>Username</label>
                <input name="username" placeholder="e.g. hakim001" required>

                <label>Password</label>
                <input name="password" placeholder="e.g. 1234" required>

                <label>Profile</label>
                <select name="profile">
                    <?php foreach($profiles as $p): ?>
                        <option value="<?=htmlspecialchars($p["name"] ?? "")?>">
                            <?=htmlspecialchars($p["name"] ?? "")?>
                        </option>
                    <?php endforeach; ?>
                    <?php if(empty($profiles)): ?><option>PPPOE-10M</option><?php endif; ?>
                </select>

                <label>Comment / Client Name</label>
                <input name="comment" placeholder="e.g. John Home WiFi">

                <button class="btn">Add PPPoE Client</button>
            </form>
        </div>

        <div class="card">
            <h2>Live PPPoE Users</h2>
            <div class="stats">
                <div class="stat"><b><?=count($active)?></b>Online</div>
                <div class="stat"><b><?=count($secrets)?></b>Total Users</div>
                <div class="stat"><b><?=count(array_filter($secrets, fn($s)=>($s["disabled"] ?? "false") === "true"))?></b>Disabled</div>
            </div>
            <p style="color:#64748b;margin-top:18px">Auto-refreshes every 20 seconds.</p>
        </div>
    </div>

    <div class="toolbar">
        <h2>PPPoE Clients</h2>
        <div style="display:flex;gap:10px;align-items:center">
    <select id="limitRows" onchange="filterTable()" style="width:120px">
        <option value="10">Show 10</option>
        <option value="20">Show 20</option>
        <option value="all">Show All</option>
    </select>
    <input class="search" id="search" placeholder="Search username, profile, comment..." onkeyup="filterTable()">
</div>
    </div>

    <table id="pppoeTable">
        <thead>
            <tr>
                <th>User</th>
                <th>Profile</th>
                <th>Service</th>
                <th>Status</th>
                <th>IP / Uptime</th>
                <th>Comment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($secrets as $s): 
            $name = $s["name"] ?? "";
            $isOnline = isset($activeNames[$name]);
            $isDisabled = ($s["disabled"] ?? "false") === "true";
            $a = $activeNames[$name] ?? [];
        ?>
            <tr>
                <td><b><?=htmlspecialchars($name)?></b></td>
                <td><?=htmlspecialchars($s["profile"] ?? "")?></td>
                <td><?=htmlspecialchars($s["service"] ?? "")?></td>
                <td>
                    <?php if($isDisabled): ?>
                        <span class="pill disabled">Disabled</span>
                    <?php elseif($isOnline): ?>
                        <span class="pill online">Online</span>
                    <?php else: ?>
                        <span class="pill offline">Offline</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?=htmlspecialchars($a["address"] ?? "-")?><br>
                    <small><?=htmlspecialchars($a["uptime"] ?? "")?></small>
                </td>
                <td><?=htmlspecialchars($s["comment"] ?? "")?></td>
                <td>
                    <div class="actions">
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?=htmlspecialchars($s[".id"] ?? "")?>">
                            <input type="hidden" name="disabled" value="<?=htmlspecialchars($s["disabled"] ?? "false")?>">
                            <button class="warn"><?= $isDisabled ? "Enable" : "Disable" ?></button>
                        </form>

                        <?php if($isOnline): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="disconnect">
                            <input type="hidden" name="name" value="<?=htmlspecialchars($name)?>">
                            <button class="dark">Disconnect</button>
                        </form>
                        <?php endif; ?>

                        <form method="POST" onsubmit="return confirm('Delete this PPPoE client?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?=htmlspecialchars($s[".id"] ?? "")?>">
                            <input type="hidden" name="name" value="<?=htmlspecialchars($name)?>">
                            <button class="danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function filterTable(){
    const q = document.getElementById("search").value.toLowerCase();
    const limit = document.getElementById("limitRows").value;
    const rows = Array.from(document.querySelectorAll("#pppoeTable tbody tr"));

    let shown = 0;

    rows.forEach(row=>{
        const match = row.innerText.toLowerCase().includes(q);

        if(!match){
            row.style.display = "none";
            return;
        }

        if(limit === "all" || shown < parseInt(limit)){
            row.style.display = "";
            shown++;
        } else {
            row.style.display = "none";
        }
    });
}

document.addEventListener("DOMContentLoaded", filterTable);
setTimeout(()=>location.reload(), 20000);
</script>

</body>
</html>
