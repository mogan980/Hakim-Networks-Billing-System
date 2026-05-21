<?php
require_once "config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

date_default_timezone_set("Africa/Nairobi");

try {
    $settings = $pdo->query("
        SELECT * FROM mikrotik_settings
        ORDER BY id DESC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    $routerStatus = "offline";
    $onlineHotspot = 0;
    $onlinePppoe = 0;
    $wanRx = 0;
    $wanTx = 0;

    if ($settings) {
        $api = new Client(new Config([
            "host" => $settings["router_ip"],
            "user" => $settings["router_username"],
            "pass" => $settings["router_password"],
            "port" => (int)$settings["api_port"],
        ]));

        $routerStatus = "online";

        $hotspotActive = $api->query(new Query("/ip/hotspot/active/print"))->read();
$hotspotHosts  = $api->query(new Query("/ip/hotspot/host/print"))->read();
$pppoe         = $api->query(new Query("/ppp/active/print"))->read();

$onlineHotspot = max(count($hotspotActive), count($hotspotHosts));
$onlinePppoe = count($pppoe);

        $traffic = $api->query(
            (new Query("/interface/monitor-traffic"))
                ->equal("interface", "ether1")
                ->equal("once", "")
        )->read();

        $rx = (float)($traffic[0]["rx-bits-per-second"] ?? 0);
        $tx = (float)($traffic[0]["tx-bits-per-second"] ?? 0);

        $wanRx = round($rx / 1000000, 2);
        $wanTx = round($tx / 1000000, 2);
    }

    $stmt = $pdo->prepare("
        UPDATE system_stats
        SET router_status=?,
            online_hotspot=?,
            online_pppoe=?,
            wan_rx=?,
            wan_tx=?,
            updated_at=NOW()
        WHERE id=1
    ");

    $stmt->execute([
        $routerStatus,
        $onlineHotspot,
        $onlinePppoe,
        $wanRx,
        $wanTx
    ]);

    echo "Stats synced successfully\n";

} catch (Exception $e) {
    $stmt = $pdo->prepare("
        UPDATE system_stats
        SET router_status='offline',
            updated_at=NOW()
        WHERE id=1
    ");
    $stmt->execute();

    echo "Stats sync failed: " . $e->getMessage() . "\n";
}
