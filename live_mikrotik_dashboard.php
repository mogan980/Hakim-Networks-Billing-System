<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function mbps($bits){ return round(((float)$bits) / 1000000, 2); }

$router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$error = null;
$data = [
    "status" => "offline",
    "identity" => "-",
    "cpu" => "-",
    "memory" => "-",
    "uptime" => "-",
    "board" => "-",
    "rx" => 0,
    "tx" => 0,
    "hotspot" => [],
    "pppoe" => [],
    "queues" => [],
    "leases" => []
];

try {
    if (!$router) throw new Exception("No router configured in super_admin_router");

    $client = new Client(new Config([
        "host" => $router["router_ip"],
        "user" => $router["router_user"],
        "pass" => $router["router_pass"],
        "port" => (int)$router["router_port"],
        "timeout" => 5,
        "attempts" => 1
    ]));

    $identity = $client->query(new Query("/system/identity/print"))->read();
    $resource = $client->query(new Query("/system/resource/print"))->read();

    $hotspot = $client->query(new Query("/ip/hotspot/active/print"))->read();
    $pppoe   = $client->query(new Query("/ppp/active/print"))->read();
    $queues  = $client->query(new Query("/queue/simple/print"))->read();
    $leases  = $client->query(new Query("/ip/dhcp-server/lease/print"))->read();

    $traffic = $client->query(
        (new Query("/interface/monitor-traffic"))
            ->equal("interface", "ether1")
            ->equal("once", "")
    )->read();

    $data["status"] = "online";
    $data["identity"] = $identity[0]["name"] ?? "MikroTik";
    $data["cpu"] = $resource[0]["cpu-load"] ?? "-";
    $data["memory"] = $resource[0]["free-memory"] ?? "-";
    $data["uptime"] = $resource[0]["uptime"] ?? "-";
    $data["board"] = $resource[0]["board-name"] ?? "-";
    $data["rx"] = mbps($traffic[0]["rx-bits-per-second"] ?? 0);
    $data["tx"] = mbps($traffic[0]["tx-bits-per-second"] ?? 0);
    $data["hotspot"] = $hotspot;
    $data["pppoe"] = $pppoe;
    $data["queues"] = $queues;
    $data["leases"] = $leases;

} catch (Exception $ex) {
    $error = $ex->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Live MikroTik Dashboard</title>
<meta http-equiv="refresh" content="10">
<style>
body{font-family:Arial;background:#eef7fb;margin:0;padding:25px;color:#071427}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.card{background:#fff;border-radius:18px;padding:20px;box-shadow:0 10px 30px rgba(0,0,0,.06)}
h1,h2{margin-top:0}
.badge{padding:7px 14px;border-radius:30px;font-weight:bold;font-size:12px}
.online{background:#d8ffe7;color:#047a34}
.offline{background:#ffe0e0;color:#b00000}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden}
th{background:#061126;color:white;text-align:left}
th,td{padding:12px;border-bottom:1px solid #e5e7eb;font-size:14px}
.section{margin-top:22px}
</style>
</head>
<body>

<h1>Live MikroTik Dashboard</h1>
<p>
Router: <b><?= e($router["router_name"] ?? "MikroTik Main Router") ?></b>
<span class="badge <?= $data["status"] ?>"><?= strtoupper($data["status"]) ?></span>
</p>

<?php if($error): ?>
<div class="card" style="color:#b00000"><?= e($error) ?></div>
<?php endif; ?>

<div class="grid">
<div class="card"><h3>Router Identity</h3><h2><?= e($data["identity"]) ?></h2></div>
<div class="card"><h3>CPU Load</h3><h2><?= e($data["cpu"]) ?>%</h2></div>
<div class="card"><h3>Download RX</h3><h2><?= e($data["rx"]) ?> Mbps</h2></div>
<div class="card"><h3>Upload TX</h3><h2><?= e($data["tx"]) ?> Mbps</h2></div>
<div class="card"><h3>Hotspot Online</h3><h2><?= count($data["hotspot"]) ?></h2></div>
<div class="card"><h3>PPPoE Online</h3><h2><?= count($data["pppoe"]) ?></h2></div>
<div class="card"><h3>Simple Queues</h3><h2><?= count($data["queues"]) ?></h2></div>
<div class="card"><h3>Uptime</h3><h2><?= e($data["uptime"]) ?></h2></div>
</div>

<div class="section card">
<h2>Online Hotspot Users</h2>
<table>
<tr><th>User</th><th>IP</th><th>MAC</th><th>Uptime</th><th>Bytes In</th><th>Bytes Out</th></tr>
<?php foreach($data["hotspot"] as $u): ?>
<tr>
<td><?= e($u["user"] ?? "-") ?></td>
<td><?= e($u["address"] ?? "-") ?></td>
<td><?= e($u["mac-address"] ?? "-") ?></td>
<td><?= e($u["uptime"] ?? "-") ?></td>
<td><?= e($u["bytes-in"] ?? "0") ?></td>
<td><?= e($u["bytes-out"] ?? "0") ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<div class="section card">
<h2>Simple Queues / Speed Limits</h2>
<table>
<tr><th>Name</th><th>Target</th><th>Max Limit</th><th>Bytes</th><th>Disabled</th></tr>
<?php foreach($data["queues"] as $q): ?>
<tr>
<td><?= e($q["name"] ?? "-") ?></td>
<td><?= e($q["target"] ?? "-") ?></td>
<td><?= e($q["max-limit"] ?? "-") ?></td>
<td><?= e($q["bytes"] ?? "-") ?></td>
<td><?= e($q["disabled"] ?? "-") ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<div class="section card">
<h2>Known DHCP Clients</h2>
<table>
<tr><th>IP</th><th>MAC</th><th>Host Name</th><th>Status</th></tr>
<?php foreach($data["leases"] as $l): ?>
<tr>
<td><?= e($l["address"] ?? "-") ?></td>
<td><?= e($l["mac-address"] ?? "-") ?></td>
<td><?= e($l["host-name"] ?? "-") ?></td>
<td><?= e($l["status"] ?? "-") ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

</body>
</html>
