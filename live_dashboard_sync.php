<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/config/database.php";

function one($pdo, $sql) {
    $v = $pdo->query($sql)->fetchColumn();
    return $v === null ? 0 : $v;
}

$todayRevenue = one($pdo, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()");
$weekRevenue = one($pdo, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)");
$monthRevenue = one($pdo, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
$totalRevenue = one($pdo, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'");

$paidCount = one($pdo, "SELECT COUNT(*) FROM payments WHERE status='paid'");
$pendingCount = one($pdo, "SELECT COUNT(*) FROM payments WHERE status='pending'");
$failedCount = one($pdo, "SELECT COUNT(*) FROM payments WHERE status='failed'");
$voucherRevenue = one($pdo, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND method='voucher'");
$mpesaRevenue = one($pdo, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND method='mpesa'");

$recent = $pdo->query("
    SELECT 
        p.id,
        p.phone,
        p.amount,
        p.method,
        p.status,
        p.checkout_id,
        p.mpesa_receipt,
        p.client_ip,
        p.created_at,
        pk.name AS package_name
    FROM payments p
    LEFT JOIN packages pk ON p.package_id = pk.id
    ORDER BY p.id DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "time" => date("Y-m-d H:i:s"),
    "revenue" => [
        "today" => (float)$todayRevenue,
        "week" => (float)$weekRevenue,
        "month" => (float)$monthRevenue,
        "total" => (float)$totalRevenue,
        "voucher" => (float)$voucherRevenue,
        "mpesa" => (float)$mpesaRevenue
    ],
    "payments" => [
        "paid" => (int)$paidCount,
        "pending" => (int)$pendingCount,
        "failed" => (int)$failedCount
    ],
    "recent" => $recent
]);
