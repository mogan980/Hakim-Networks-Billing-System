<?php
require_once "config/database.php";

date_default_timezone_set("Africa/Nairobi");

$now = date("Y-m-d H:i:s");

$stmt = $pdo->query("
    SELECT *
    FROM subscriptions
    WHERE
        status='paid'
        AND expires_at IS NOT NULL
        AND expires_at < NOW()
");

$expired = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($expired as $sub){

    $companyId = (int)$sub["company_id"];

    // expire subscription
    $pdo->prepare("
        UPDATE subscriptions
        SET status='expired'
        WHERE id=?
    ")->execute([$sub["id"]]);

    // suspend company
    $pdo->prepare("
        UPDATE companies
        SET
            status='expired',
            router_connected=0
        WHERE id=?
    ")->execute([$companyId]);

    // log activity
    $pdo->prepare("
        INSERT INTO tenant_router_logs
        (company_id, action_type, router_ip, message)
        VALUES (?, 'expiry', '', ?)
    ")->execute([
        $companyId,
        "Tenant subscription expired automatically"
    ]);
}

echo "Tenant expiry sync completed at {$now}\n";
