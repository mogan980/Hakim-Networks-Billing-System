<?php
require_once "config/database.php";

date_default_timezone_set("Africa/Nairobi");

$companies = $pdo->query("
    SELECT *
    FROM companies
    WHERE
        router_ip IS NOT NULL
        AND router_ip != ''
        AND online_status='online'
")->fetchAll(PDO::FETCH_ASSOC);

foreach($companies as $c){

    $companyId = (int)$c["id"];

    // simulated live metrics for now
    // later replaced with RouterOS API

    $rx = rand(5,120);
    $tx = rand(2,80);
    $users = rand(1,40);
    $cpu = rand(5,90) . "%";

    $pdo->prepare("
        INSERT INTO router_bandwidth
        (
            company_id,
            rx_mbps,
            tx_mbps,
            active_users,
            cpu_load
        )
        VALUES
        (?,?,?,?,?)
    ")->execute([
        $companyId,
        $rx,
        $tx,
        $users,
        $cpu
    ]);
}

echo "Bandwidth sync completed\n";
