<?php
session_start();
require_once "config/database.php";

header("Content-Type: application/json");

$data = $pdo->query("
    SELECT *
    FROM router_bandwidth
    ORDER BY id DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

$data = array_reverse($data);

$labels = [];
$rx = [];
$tx = [];

foreach($data as $d){

    $labels[] = date("H:i", strtotime($d["checked_at"]));
    $rx[] = (float)$d["rx_mbps"];
    $tx[] = (float)$d["tx_mbps"];
}

echo json_encode([
    "labels"=>$labels,
    "rx"=>$rx,
    "tx"=>$tx
]);
