<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/database.php";

if (!isset($_SESSION["tenant_user_id"])) {
    header("Location: /mhakim-billing-system/saas_auth.php");
    exit;
}

$role = $_SESSION["tenant_role"] ?? "";
$companyId = $_SESSION["tenant_company_id"] ?? null;

if ($role === "super_admin") {
    return;
}

if (!$companyId) {
    session_destroy();
    header("Location: /mhakim-billing-system/saas_auth.php?error=no_company");
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM subscriptions
    WHERE company_id=?
    AND status IN ('paid','active')
    AND expires_at > NOW()
    ORDER BY id DESC
    LIMIT 1
");

$stmt->execute([$companyId]);
$sub = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sub) {
    session_destroy();
    header("Location: /mhakim-billing-system/subscription_payment.php?company_id=" . urlencode($companyId));
    exit;
}
