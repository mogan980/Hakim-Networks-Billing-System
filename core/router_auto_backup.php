<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/database.php";

use RouterOS\Client;
use RouterOS\Query;

$hosts = ["10.10.10.1", "192.168.88.1"]; // VPN first, LAN second
$config = require __DIR__ . "/../config/mikrotik.php";

$connected = false;
$error = "";

foreach ($hosts as $host) {
    try {
        $client = new Client([
            "host" => $host,
            "user" => $config["user"],
            "pass" => $config["pass"],
            "port" => $config["port"],
            "timeout" => 8
        ]);

        $connected = true;
        $activeHost = $host;
        break;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if (!$connected) {
    $stmt = $pdo->prepare("INSERT INTO router_backup_logs(message,status,created_at) VALUES(?,?,NOW())");
    $stmt->execute(["Backup failed: " . $error, "failed"]);
    exit("Backup failed\n");
}

$backupName = "hakim_backup_" . date("Y-m-d_H-i");

try {
    $client->query(
        (new Query("/system/backup/save"))
            ->equal("name", $backupName)
    )->read();

    $stmt = $pdo->prepare("INSERT INTO router_backup_logs(message,status,created_at) VALUES(?,?,NOW())");
    $stmt->execute(["Router backup created successfully via {$activeHost}: {$backupName}", "success"]);

    echo "Backup success: {$backupName}\n";

} catch (Exception $e) {
    $stmt = $pdo->prepare("INSERT INTO router_backup_logs(message,status,created_at) VALUES(?,?,NOW())");
    $stmt->execute(["Backup failed after connection: " . $e->getMessage(), "failed"]);
    echo "Backup failed\n";
}
