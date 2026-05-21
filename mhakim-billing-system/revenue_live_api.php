<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config/database.php";

function one($pdo,$sql){
    $v = $pdo->query($sql)->fetchColumn();
    return (float)($v ?: 0);
}

echo json_encode([
    "success" => true,
    "today" => one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()"),
    "week"  => one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)"),
    "month" => one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())"),
    "total" => one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'"),
    "clients" => (int)$pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn(),
    "packages" => (int)$pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn(),
    "vouchers" => (int)$pdo->query("SELECT COUNT(*) FROM smart_vouchers WHERE status='unused'")->fetchColumn()
]);
