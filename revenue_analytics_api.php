<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config/database.php";

function val($pdo,$sql){
    return (float)$pdo->query($sql)->fetchColumn();
}

$today = val($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()");
$week  = val($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)");
$month = val($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
$total = val($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'");

echo json_encode([
  "success"=>true,
  "today"=>$today,
  "week"=>$week,
  "month"=>$month,
  "total"=>$total
]);
