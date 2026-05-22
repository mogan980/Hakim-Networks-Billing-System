<?php
header("Content-Type: application/json");
require_once "config/database.php";

$mpesaToday = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM payments
    WHERE status='paid'
    AND DATE(created_at)=CURDATE()
")->fetchColumn();

$mpesaTotal = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM payments
    WHERE status='paid'
")->fetchColumn();

$voucherToday = (float)$pdo->query("
    SELECT COALESCE(SUM(packages.price),0)
    FROM vouchers
    LEFT JOIN packages ON packages.id=vouchers.package_id
    WHERE vouchers.status='used'
    AND DATE(vouchers.updated_at)=CURDATE()
")->fetchColumn();

$voucherTotal = (float)$pdo->query("
    SELECT COALESCE(SUM(packages.price),0)
    FROM vouchers
    LEFT JOIN packages ON packages.id=vouchers.package_id
    WHERE vouchers.status='used'
")->fetchColumn();

echo json_encode([
    "mpesa_today" => $mpesaToday,
    "mpesa_total" => $mpesaTotal,
    "voucher_today" => $voucherToday,
    "voucher_total" => $voucherTotal,
    "today_revenue" => $mpesaToday + $voucherToday,
    "total_revenue" => $mpesaTotal + $voucherTotal
]);
