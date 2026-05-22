<?php

require_once "config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

date_default_timezone_set("Africa/Nairobi");

$logFile = __DIR__ . "/backup_router.log";

function logMsg($msg) {
    global $logFile;
    file_put_contents(
        $logFile,
        "[" . date("Y-m-d H:i:s") . "] " . $msg . PHP_EOL,
        FILE_APPEND
    );
}

try {

    $settings = $pdo->query("
        SELECT * FROM mikrotik_settings
        ORDER BY id DESC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        throw new Exception("MikroTik settings missing.");
    }

    $api = new Client(new Config([
        "host" => $settings["router_ip"],
        "user" => $settings["router_username"],
        "pass" => $settings["router_password"],
        "port" => (int)$settings["api_port"],
    ]));

    $date = date("Y-m-d_H-i");

    $backupName = "hakim_backup_" . $date;
    $exportName = "hakim_export_" . $date;

    // Create binary backup
    $api->query(
        (new Query("/system/backup/save"))
            ->equal("name", $backupName)
    )->read();

    // Create export script
    $api->query(
        (new Query("/export"))
            ->equal("file", $exportName)
    )->read();

    logMsg("Router backup created successfully: $backupName");

    echo "Backup completed successfully.";

} catch (Exception $e) {

    logMsg("Backup failed: " . $e->getMessage());

    echo "Backup failed: " . $e->getMessage();
}
