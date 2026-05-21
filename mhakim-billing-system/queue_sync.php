<?php
require_once "config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

date_default_timezone_set("Africa/Nairobi");

$logFile = __DIR__ . "/queue_sync.log";

function logMsg($msg){
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $msg . PHP_EOL, FILE_APPEND);
}

function cleanSpeed($speed, $fallback){
    $speed = trim((string)$speed);
    if ($speed === "") return $fallback;
    if (preg_match('/^\d+$/', $speed)) return $speed . "M";
    return $speed;
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

    $clients = $pdo->query("
        SELECT 
            clients.id,
            clients.full_name,
            clients.username,
            clients.client_ip,
            clients.status,
            clients.connection_type,
            packages.speed_down,
            packages.speed_up,
            packages.name AS package_name
        FROM clients
        LEFT JOIN packages ON clients.package_id = packages.id
        WHERE clients.status='active'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $synced = 0;

    foreach ($clients as $client) {
        $username = trim($client["username"] ?? "");
        $clientIp = trim($client["client_ip"] ?? "");

        if (!$username || !$clientIp) {
            logMsg("Skipped client without username/IP: " . ($client["full_name"] ?? "Unknown"));
            continue;
        }

        $down = cleanSpeed($client["speed_down"] ?? "", "6M");
        $up = cleanSpeed($client["speed_up"] ?? "", "2M");

        // Force voucher/hotspot short packages to 6M/2M
        $packageName = strtolower($client["package_name"] ?? "");
        $connectionType = strtolower($client["connection_type"] ?? "hotspot");

        if ($connectionType === "hotspot" && strpos($packageName, "monthly") === false) {
            $down = "6M";
            $up = "2M";
        }

        $maxLimit = $down . "/" . $up;
        $queueName = "CLIENT-" . $username;
        $target = $clientIp . "/32";

        $queue = $api->query(
            (new Query("/queue/simple/print"))
                ->where("name", $queueName)
        )->read();

        if (!empty($queue)) {
            $api->query(
                (new Query("/queue/simple/set"))
                    ->equal(".id", $queue[0][".id"])
                    ->equal("target", $target)
                    ->equal("max-limit", $maxLimit)
                    ->equal("disabled", "no")
            )->read();

            logMsg("Updated queue: {$queueName} {$target} {$maxLimit}");
        } else {
            $api->query(
                (new Query("/queue/simple/add"))
                    ->equal("name", $queueName)
                    ->equal("target", $target)
                    ->equal("max-limit", $maxLimit)
                    ->equal("comment", "auto-sync:" . $username)
                    ->equal("disabled", "no")
            )->read();

            logMsg("Created queue: {$queueName} {$target} {$maxLimit}");
        }

        $synced++;
    }

    echo "Queue sync completed. Synced: " . $synced . PHP_EOL;

} catch (Exception $e) {
    logMsg("Queue sync failed: " . $e->getMessage());
    echo "Queue sync failed: " . $e->getMessage() . PHP_EOL;
}
