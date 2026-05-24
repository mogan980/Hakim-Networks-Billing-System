<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/config/database.php";
$pdo->exec("SET time_zone = '+03:00'");

function one($pdo,$sql){
    try{
        $v=$pdo->query($sql)->fetchColumn();
        return is_numeric($v) ? (float)$v : 0;
    }catch(Exception $e){ return 0; }
}

$total = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'");
$month = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
$today = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()");

$rows = $pdo->query("
    SELECT DATE(created_at) day, COALESCE(SUM(amount),0) total
    FROM payments
    WHERE status='paid'
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
")->fetchAll(PDO::FETCH_ASSOC);

$map = [];
foreach($rows as $r){
    $map[$r["day"]] = (float)$r["total"];
}

$labels = [];
$data = [];

for($i=6;$i>=0;$i--){
    $d = date("Y-m-d", strtotime("-$i days"));
    $labels[] = date("D", strtotime($d));
    $data[] = $map[$d] ?? 0;
}

echo json_encode([
    "success"=>true,
    "total"=>$total,
    "month"=>$month,
    "today"=>$today,
    "labels"=>$labels,
    "data"=>$data,
    "time"=>date("Y-m-d H:i:s")
]);
