<?php
date_default_timezone_set("Africa/Nairobi");
require_once __DIR__ . "/config/database.php";

$ip = $_SERVER["REMOTE_ADDR"] ?? "";
$ip = trim($ip);

$stmt = $pdo->prepare("
    SELECT voucher_code
    FROM smart_vouchers
    WHERE used_by=?
    AND status='used'
    AND expires_at > NOW()
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute([$ip]);
$v = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$v){
    header("Location: index.php?error=no_valid_voucher");
    exit;
}

$code = urlencode($v["voucher_code"]);

/*
Change login.hakim if your hotspot DNS is different.
This sends the laptop/phone to MikroTik real hotspot login.
After this, it appears under Hotspot Active.
*/
header("Location: http://login.hakim/login?username={$code}&password={$code}");
exit;
