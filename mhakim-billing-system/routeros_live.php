<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

date_default_timezone_set("Africa/Nairobi");

$companies = $pdo->query("
    SELECT *
    FROM companies
    WHERE router_ip IS NOT NULL
    AND router_ip != ''
    AND online_status='online'
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($companies as $c) {

    try {
        $config = new Config([
            "host" => $c["router_ip"],
            "user" => $c["router_username"],
            "pass" => $c["router_password"],
            "port" => (int)($c["router_port"] ?: 8728),
            "timeout" => 5,
        ]);

        $client = new Client($config);

        $traffic = $client->query(
            (new Query("/interface/monitor-traffic"))
                ->equal("interface", "ether1")
                ->equal("once", "1")
        )->read();

        $rx = 0;
        $tx = 0;

        if (isset($traffic[0])) {
            $rx = round(((float)($traffic[0]["rx-bits-per-second"] ?? 0)) / 1000000, 2);
            $tx = round(((float)($traffic[0]["tx-bits-per-second"] ?? 0)) / 1000000, 2);
        }

        $activeUsers = $client->query(new Query("/ip/hotspot/active/print"))->read();
        $activeCount = count($activeUsers);

        $resource = $client->query(new Query("/system/resource/print"))->read();
        $cpu = $resource[0]["cpu-load"] ?? "0";

        $pdo->prepare("
            INSERT INTO router_bandwidth
            (company_id, rx_mbps, tx_mbps, active_users, cpu_load)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $c["id"],
            $rx,
            $tx,
            $activeCount,
            $cpu . "%"
        ]);

        $pdo->prepare("
            UPDATE companies
            SET online_status='online', last_seen=NOW()
            WHERE id=?
        ")->execute([$c["id"]]);

        echo "OK: {$c["company_name"]} RX={$rx} TX={$tx} Users={$activeCount} CPU={$cpu}%\n";

    } catch (Exception $e) {

        $pdo->prepare("
            UPDATE companies
            SET online_status='offline'
            WHERE id=?
        ")->execute([$c["id"]]);

        echo "ERROR: {$c["company_name"]} - " . $e->getMessage() . "\n";
    }
}
