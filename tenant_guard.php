<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/database.php";

if (!isset($_SESSION["tenant_company_id"])) {
    header("Location: saas_auth.php");
    exit;
}

$companyId = (int)$_SESSION["tenant_company_id"];

$stmt = $pdo->prepare("
    SELECT status
    FROM companies
    WHERE id=?
    LIMIT 1
");
$stmt->execute([$companyId]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company || $company["status"] !== "active") {

    session_destroy();

    header("Location: subscription_payment.php?company_id=" . $companyId);
    exit;
}
