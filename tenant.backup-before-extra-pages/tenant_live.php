<?php
require_once __DIR__ . "/../access_guard.php";
require_once __DIR__ . "/../config/database.php";

header("Content-Type: application/json");

$companyId = $_SESSION["tenant_company_id"];

function one($pdo, $sql, $params){
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

echo json_encode([
    "clients" => (int)one($pdo, "SELECT COUNT(*) FROM clients WHERE company_id=?", [$companyId]),
    "active_clients" => (int)one($pdo, "SELECT COUNT(*) FROM clients WHERE company_id=? AND status='active'", [$companyId]),
    "expired_clients" => (int)one($pdo, "SELECT COUNT(*) FROM clients WHERE company_id=? AND status='expired'", [$companyId]),
    "packages" => (int)one($pdo, "SELECT COUNT(*) FROM packages WHERE company_id=?", [$companyId]),
    "vouchers" => (int)one($pdo, "SELECT COUNT(*) FROM vouchers WHERE company_id=?", [$companyId]),
    "unused_vouchers" => (int)one($pdo, "SELECT COUNT(*) FROM vouchers WHERE company_id=? AND status='unused'", [$companyId]),
    "used_vouchers" => (int)one($pdo, "SELECT COUNT(*) FROM vouchers WHERE company_id=? AND status='used'", [$companyId]),
    "today_revenue" => (float)one($pdo, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE company_id=? AND status='paid' AND DATE(created_at)=CURDATE()", [$companyId]),
    "total_revenue" => (float)one($pdo, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE company_id=? AND status='paid'", [$companyId]),
    "updated_at" => date("Y-m-d H:i:s")
]);
