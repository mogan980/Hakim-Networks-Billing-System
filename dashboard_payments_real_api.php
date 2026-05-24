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

$totalRevenue = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'");
$todayRevenue = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()");
$weekRevenue  = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)");
$monthRevenue = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");

$stkRevenue = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND LOWER(method) IN ('mpesa','stk','m-pesa','cash')");
$voucherRevenue = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND LOWER(method)='voucher'");

$paidCount = one($pdo,"SELECT COUNT(*) FROM payments WHERE status='paid'");
$pendingCount = one($pdo,"SELECT COUNT(*) FROM payments WHERE status='pending'");
$failedCount = one($pdo,"SELECT COUNT(*) FROM payments WHERE status IN ('failed','cancelled')");

$clients = one($pdo,"SELECT COUNT(*) FROM clients");
$packages = one($pdo,"SELECT COUNT(*) FROM packages");
$vouchers = one($pdo,"SELECT COUNT(*) FROM smart_vouchers");

$recent = $pdo->query("
    SELECT id, phone, amount, method, status, client_ip, created_at
    FROM payments
    ORDER BY id DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success"=>true,
    "total_revenue"=>$totalRevenue,
    "today_revenue"=>$todayRevenue,
    "week_revenue"=>$weekRevenue,
    "month_revenue"=>$monthRevenue,
    "stk_revenue"=>$stkRevenue,
    "voucher_revenue"=>$voucherRevenue,
    "paid_count"=>$paidCount,
    "pending_count"=>$pendingCount,
    "failed_count"=>$failedCount,
    "clients"=>$clients,
    "packages"=>$packages,
    "vouchers"=>$vouchers,
    "recent"=>$recent,
    "time"=>date("Y-m-d H:i:s")
]);
