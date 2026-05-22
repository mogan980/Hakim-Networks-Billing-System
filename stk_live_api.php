<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/config/database.php";

function one($pdo, $sql){
    $v = $pdo->query($sql)->fetchColumn();
    return $v === false || $v === null ? 0 : $v;
}

$totalRevenue = one($pdo, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND method IN ('mpesa','stk','M-PESA')");
$paidCount    = one($pdo, "SELECT COUNT(*) FROM payments WHERE status='paid' AND method IN ('mpesa','stk','M-PESA')");
$pendingCount = one($pdo, "SELECT COUNT(*) FROM payments WHERE status='pending' AND method IN ('mpesa','stk','M-PESA')");
$failedCount  = one($pdo, "SELECT COUNT(*) FROM payments WHERE status IN ('failed','cancelled') AND method IN ('mpesa','stk','M-PESA')");

$rows = $pdo->query("
    SELECT 
        id,
        phone,
        amount,
        status,
        reference,
        mpesa_receipt,
        checkout_id,
        client_ip,
        created_at
    FROM payments
    WHERE method IN ('mpesa','stk','M-PESA')
    ORDER BY id DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "total_revenue" => (float)$totalRevenue,
    "today_revenue" => (float)$todayRevenue,
    "paid" => (int)$paidCount,
    "pending" => (int)$pendingCount,
    "failed" => (int)$failedCount,
    "rows" => $rows,
    "time" => date("Y-m-d H:i:s")
]);
