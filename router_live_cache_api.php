<?php
session_start();
require_once "config/database.php";

header("Content-Type: application/json");

$companyId = $_GET["company_id"] ?? null;

if (!$companyId && ($_SESSION["tenant_role"] ?? "") !== "super_admin") {
    $companyId = $_SESSION["tenant_company_id"] ?? 0;
}

if (!$companyId) {
    echo json_encode(["error"=>"missing_company"]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM router_live_cache WHERE company_id=? LIMIT 1");
$stmt->execute([(int)$companyId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo json_encode([
        "status"=>"not_configured",
        "message"=>"No cached router data yet"
    ]);
    exit;
}

echo json_encode($data);
