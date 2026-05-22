<?php
require_once "auth.php";
requireLogin();
require_once "config/database.php";

header("Content-Type: application/json");

$stats = $pdo->query("SELECT * FROM system_stats WHERE id=1")->fetch(PDO::FETCH_ASSOC);

$data = [];

$data["total_clients"] = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$data["active_clients"] = $pdo->query("SELECT COUNT(*) FROM clients WHERE status='active'")->fetchColumn();
$data["expired_clients"] = $pdo->query("SELECT COUNT(*) FROM clients WHERE status='expired'")->fetchColumn();
$data["blocked_clients"] = $pdo->query("SELECT COUNT(*) FROM clients WHERE status='blocked'")->fetchColumn();

$data["today_revenue"] = $pdo->query("
    SELECT COALESCE(SUM(amount),0) FROM payments 
    WHERE status='paid' AND DATE(created_at)=CURDATE()
")->fetchColumn();

$data["monthly_revenue"] = $pdo->query("
    SELECT COALESCE(SUM(amount),0) FROM payments 
    WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())
")->fetchColumn();

$data["total_revenue"] = $pdo->query("
    SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'
")->fetchColumn();

$data["total_packages"] = $pdo->query("SELECT COUNT(*) FROM packages WHERE status='active'")->fetchColumn();
$data["total_vouchers"] = $pdo->query("SELECT COUNT(*) FROM vouchers")->fetchColumn();
$data["unused_vouchers"] = $pdo->query("SELECT COUNT(*) FROM vouchers WHERE status='unused'")->fetchColumn();
$data["used_vouchers"] = $pdo->query("SELECT COUNT(*) FROM vouchers WHERE status='used'")->fetchColumn();

$data["recent_payments"] = $pdo->query("
    SELECT id, phone, amount, status, created_at 
    FROM payments 
    ORDER BY id DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "stats" => $stats,
    "dashboard" => $data,
    "time" => date("H:i:s")
]);
