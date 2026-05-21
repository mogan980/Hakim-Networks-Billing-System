<?php
require_once "config/database.php";

date_default_timezone_set("Africa/Nairobi");

$companies = $pdo->query("
    SELECT *
    FROM companies
    WHERE
        router_ip IS NOT NULL
        AND router_ip != ''
")->fetchAll(PDO::FETCH_ASSOC);

foreach($companies as $c){

    $companyId = (int)$c["id"];
    $routerIp = trim($c["router_ip"]);
    $routerName = trim($c["router_name"] ?? "Router");

    $start = microtime(true);

    $connected = @fsockopen($routerIp, 8728, $errno, $errstr, 3);

    $latency = round((microtime(true)-$start)*1000);

    if($connected){

        fclose($connected);

        $status = "online";

        $pdo->prepare("
            UPDATE companies
            SET online_status='online'
            WHERE id=?
        ")->execute([$companyId]);

    }else{

        $status = "offline";

        $pdo->prepare("
            UPDATE companies
            SET online_status='offline'
            WHERE id=?
        ")->execute([$companyId]);
    }

    $pdo->prepare("
        INSERT INTO router_heartbeat
        (
            company_id,
            router_name,
            router_ip,
            status,
            response_time
        )
        VALUES
        (?,?,?,?,?)
    ")->execute([
        $companyId,
        $routerName,
        $routerIp,
        $status,
        $latency . " ms"
    ]);
}

echo "Heartbeat completed\n";
