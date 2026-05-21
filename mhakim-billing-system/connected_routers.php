<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$pdo->exec("
CREATE TABLE IF NOT EXISTS connected_routers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(150),
    ip_address VARCHAR(60),
    mac_address VARCHAR(80),
    device_type VARCHAR(80) DEFAULT 'Router/AP',
    location VARCHAR(150),
    notes TEXT,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

function mtClient($pdo){
    $router = $pdo->query("SELECT * FROM routers WHERE status='active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if(!$router){
        throw new Exception("No active MikroTik router selected.");
    }

    return new Client(new Config([
        "host"=>$router["router_ip"],
        "user"=>$router["router_username"],
        "pass"=>$router["router_password"],
        "port"=>(int)$router["api_port"],
        "timeout"=>5
    ]));
}

if(isset($_POST["save_device"])){
    if(!empty($_POST["id"])){
        $stmt = $pdo->prepare("
            UPDATE connected_routers 
            SET device_name=?, ip_address=?, mac_address=?, device_type=?, location=?, notes=?, status=?
            WHERE id=?
        ");
        $stmt->execute([
            $_POST["device_name"],
            $_POST["ip_address"],
            $_POST["mac_address"],
            $_POST["device_type"],
            $_POST["location"],
            $_POST["notes"],
            $_POST["status"],
            $_POST["id"]
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO connected_routers
            (device_name, ip_address, mac_address, device_type, location, notes, status)
            VALUES (?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $_POST["device_name"],
            $_POST["ip_address"],
            $_POST["mac_address"],
            $_POST["device_type"],
            $_POST["location"],
            $_POST["notes"],
            $_POST["status"]
        ]);
    }

    header("Location: connected_routers.php");
    exit;
}

if(isset($_GET["delete"])){
    $stmt = $pdo->prepare("DELETE FROM connected_routers WHERE id=?");
    $stmt->execute([$_GET["delete"]]);
    header("Location: connected_routers.php");
    exit;
}

if(isset($_GET["sync"])){
    try{
        $client = mtClient($pdo);

        $leases = $client->query(new Query("/ip/dhcp-server/lease/print"))->read();
        $hosts = $client->query(new Query("/ip/hotspot/host/print"))->read();

        $items = [];

        foreach($leases as $l){
            $ip = $l["address"] ?? "";
            $mac = $l["mac-address"] ?? "";
            if(!$ip && !$mac) continue;

            $items[$mac ?: $ip] = [
                "device_name" => $l["host-name"] ?? "Unknown Device",
                "ip_address" => $ip,
                "mac_address" => $mac,
                "device_type" => "Router/AP",
                "status" => $l["status"] ?? "active"
            ];
        }

        foreach($hosts as $h){
            $ip = $h["address"] ?? "";
            $mac = $h["mac-address"] ?? "";
            if(!$ip && !$mac) continue;

            if(!isset($items[$mac ?: $ip])){
                $items[$mac ?: $ip] = [
                    "device_name" => $h["host-name"] ?? "Hotspot Device",
                    "ip_address" => $ip,
                    "mac_address" => $mac,
                    "device_type" => "Hotspot Host",
                    "status" => "active"
                ];
            }
        }

        foreach($items as $item){
            $check = $pdo->prepare("SELECT id FROM connected_routers WHERE mac_address=? OR ip_address=? LIMIT 1");
            $check->execute([$item["mac_address"], $item["ip_address"]]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);

            if($existing){
                $upd = $pdo->prepare("
                    UPDATE connected_routers 
                    SET device_name=?, ip_address=?, mac_address=?, device_type=?, status=?
                    WHERE id=?
                ");
                $upd->execute([
                    $item["device_name"],
                    $item["ip_address"],
                    $item["mac_address"],
                    $item["device_type"],
                    $item["status"],
                    $existing["id"]
                ]);
            } else {
                $ins = $pdo->prepare("
                    INSERT INTO connected_routers
                    (device_name, ip_address, mac_address, device_type, status)
                    VALUES (?,?,?,?,?)
                ");
                $ins->execute([
                    $item["device_name"],
                    $item["ip_address"],
                    $item["mac_address"],
                    $item["device_type"],
                    $item["status"]
                ]);
            }
        }

        header("Location: connected_routers.php?synced=1");
        exit;

    }catch(Exception $e){
        $syncError = $e->getMessage();
    }
}

$edit = null;
if(isset($_GET["edit"])){
    $stmt = $pdo->prepare("SELECT * FROM connected_routers WHERE id=?");
    $stmt->execute([$_GET["edit"]]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$devices = $pdo->query("SELECT * FROM connected_routers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Connected Routers / APs</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;font-family:Arial;background:#eaf0f5;color:#020617}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020b1a;color:white;padding:20px}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;border-radius:10px;margin:6px 0;font-weight:800}
.sidebar a.active,.sidebar a:hover{background:#064e3b}
.main{margin-left:260px;padding:35px}
.card{background:white;border-radius:22px;padding:24px;margin-bottom:24px;box-shadow:0 18px 45px rgba(15,23,42,.09)}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
input,select,textarea{padding:13px;border:1px solid #cbd5e1;border-radius:12px;width:100%;box-sizing:border-box}
textarea{height:80px}
button,.btn{border:0;padding:11px 15px;border-radius:12px;font-weight:900;text-decoration:none;display:inline-block}
.green{background:#16a34a;color:white}.blue{background:#2563eb;color:white}.red{background:#dc2626;color:white}.gray{background:#64748b;color:white}
table{width:100%;border-collapse:collapse}
th{background:#020617;color:white;text-align:left;padding:13px}
td{padding:13px;border-bottom:1px solid #e5e7eb}
.badge{background:#dcfce7;color:#166534;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:900}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:14px}
.small{color:#64748b}
@media(max-width:900px){.main{margin-left:0}.sidebar{display:none}.grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<a href="dashboard.php">Dashboard</a>
<a href="noc_final_clean.php">Live NOC</a>
<a href="noc.php">NOC Center</a>
<a href="routers.php">Routers</a>
<a class="active" href="connected_routers.php">Connected Routers/APs</a>
<a href="clients.php">Clients</a>
<a href="packages.php">Packages</a>
<a href="vouchers.php">Vouchers</a>
<a href="payments.php">Payments</a>
</div>

<div class="main">
<div class="topbar">
<div>
<h1>Connected Routers / Access Points</h1>
<p class="small">View, edit and manage routers/APs connected behind your main MikroTik.</p>
</div>
<a class="green btn" href="?sync=1">🔄 Sync From MikroTik</a>
</div>

<?php if(isset($syncError)): ?>
<div class="card" style="color:#dc2626;font-weight:900;"><?php echo htmlspecialchars($syncError); ?></div>
<?php endif; ?>

<?php if(isset($_GET["synced"])): ?>
<div class="card" style="color:#16a34a;font-weight:900;">Devices synced from MikroTik successfully.</div>
<?php endif; ?>

<div class="card">
<h2><?php echo $edit ? "Edit Device" : "Add Device Manually"; ?></h2>

<form method="POST">
<input type="hidden" name="id" value="<?php echo htmlspecialchars($edit["id"] ?? ""); ?>">

<div class="grid">
<input name="device_name" placeholder="Device Name e.g. Tenda AP Block A" value="<?php echo htmlspecialchars($edit["device_name"] ?? ""); ?>" required>
<input name="ip_address" placeholder="IP Address e.g. 192.168.88.20" value="<?php echo htmlspecialchars($edit["ip_address"] ?? ""); ?>">
<input name="mac_address" placeholder="MAC Address" value="<?php echo htmlspecialchars($edit["mac_address"] ?? ""); ?>">
<select name="device_type">
<?php
$types = ["Router/AP","Switch","Hotspot Host","Client Router","Repeater","CPE"];
foreach($types as $t){
    $sel = (($edit["device_type"] ?? "") === $t) ? "selected" : "";
    echo "<option $sel>$t</option>";
}
?>
</select>
<input name="location" placeholder="Location e.g. Plot A / Floor 2" value="<?php echo htmlspecialchars($edit["location"] ?? ""); ?>">
<select name="status">
<option value="active" <?php if(($edit["status"] ?? "")=="active") echo "selected"; ?>>active</option>
<option value="offline" <?php if(($edit["status"] ?? "")=="offline") echo "selected"; ?>>offline</option>
<option value="maintenance" <?php if(($edit["status"] ?? "")=="maintenance") echo "selected"; ?>>maintenance</option>
</select>
</div>

<br>
<textarea name="notes" placeholder="Notes"><?php echo htmlspecialchars($edit["notes"] ?? ""); ?></textarea>
<br><br>

<button class="blue" name="save_device"><?php echo $edit ? "Update Device" : "Save Device"; ?></button>
<?php if($edit): ?><a class="gray btn" href="connected_routers.php">Cancel</a><?php endif; ?>
</form>
</div>

<div class="card">
<h2>Discovered / Registered Devices</h2>

<table>
<tr>
<th>ID</th>
<th>Device</th>
<th>IP</th>
<th>MAC</th>
<th>Type</th>
<th>Location</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php foreach($devices as $d): ?>
<tr>
<td><?php echo $d["id"]; ?></td>
<td><b><?php echo htmlspecialchars($d["device_name"]); ?></b><br><small><?php echo htmlspecialchars($d["notes"] ?? ""); ?></small></td>
<td><?php echo htmlspecialchars($d["ip_address"]); ?></td>
<td><?php echo htmlspecialchars($d["mac_address"]); ?></td>
<td><?php echo htmlspecialchars($d["device_type"]); ?></td>
<td><?php echo htmlspecialchars($d["location"]); ?></td>
<td><span class="badge"><?php echo htmlspecialchars($d["status"]); ?></span></td>
<td>
<a class="blue btn" href="?edit=<?php echo $d["id"]; ?>">Edit</a>
<a class="red btn" onclick="return confirm('Delete this device?')" href="?delete=<?php echo $d["id"]; ?>">Delete</a>
<?php if(!empty($d["ip_address"])): ?>
<a class="green btn" target="_blank" href="http://<?php echo htmlspecialchars($d["ip_address"]); ?>:80">Open Web UI</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>

</table>
</div>
</div>
</body>
</html>
