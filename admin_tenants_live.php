<?php
session_start();
require_once "config/database.php";
header("Content-Type: application/json");

if (($_SESSION["tenant_role"] ?? "") !== "super_admin") {
    echo json_encode([
        "total_tenants"=>0,
        "active_tenants"=>0,
        "connected_routers"=>0,
        "tenant_revenue"=>0
    ]);
    exit;
}

echo json_encode([
    "total_tenants" => (int)$pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn(),
    "active_tenants" => (int)$pdo->query("SELECT COUNT(*) FROM companies WHERE status='active'")->fetchColumn(),
    "connected_routers" => (int)$pdo->query("SELECT COUNT(*) FROM companies WHERE router_ip IS NOT NULL AND router_ip!=''")->fetchColumn(),
    "tenant_revenue" => (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE status IN ('paid','active')")->fetchColumn(),
    "updated_at" => date("Y-m-d H:i:s")
]);
