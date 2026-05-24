<?php
date_default_timezone_set("Africa/Nairobi");
require_once __DIR__ . "/config/database.php";

$pdo->exec("SET time_zone = '+03:00'");

$pdo->exec("
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    username VARCHAR(100) NULL,
    password VARCHAR(100) NULL,
    package_id INT NULL,
    status VARCHAR(30) DEFAULT 'active',
    type VARCHAR(50) DEFAULT 'hotspot',
    starts_at DATETIME NULL,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

$added = 0;

/* Sync valid STK clients */
$stk = $pdo->query("
SELECT phone, client_ip, created_at, expires_at
FROM payments
WHERE status='paid'
AND client_ip IS NOT NULL
AND client_ip!=''
AND expires_at IS NOT NULL
AND expires_at > NOW()
")->fetchAll(PDO::FETCH_ASSOC);

foreach($stk as $r){
    $username = "stk_" . preg_replace('/\D/','',$r["phone"]);

    $check = $pdo->prepare("SELECT id FROM clients WHERE username=? LIMIT 1");
    $check->execute([$username]);

    if(!$check->fetch()){
        $ins = $pdo->prepare("
            INSERT INTO clients(full_name, phone, username, password, status, type, starts_at, expires_at)
            VALUES(?,?,?,?,?,?,?,?)
        ");
        $ins->execute([
            "STK User",
            $r["phone"],
            $username,
            "",
            "active",
            "hotspot",
            $r["created_at"],
            $r["expires_at"]
        ]);
        $added++;
    }else{
        $upd = $pdo->prepare("UPDATE clients SET status='active', expires_at=? WHERE username=?");
        $upd->execute([$r["expires_at"],$username]);
    }
}

/* Sync valid voucher clients */
$vouchers = $pdo->query("
SELECT voucher_code, used_by, used_at, expires_at
FROM smart_vouchers
WHERE status='used'
AND used_by IS NOT NULL
AND used_by!=''
AND expires_at IS NOT NULL
AND expires_at > NOW()
")->fetchAll(PDO::FETCH_ASSOC);

foreach($vouchers as $r){
    $username = $r["voucher_code"];

    $check = $pdo->prepare("SELECT id FROM clients WHERE username=? LIMIT 1");
    $check->execute([$username]);

    if(!$check->fetch()){
        $ins = $pdo->prepare("
            INSERT INTO clients(full_name, phone, username, password, status, type, starts_at, expires_at)
            VALUES(?,?,?,?,?,?,?,?)
        ");
        $ins->execute([
            "Voucher User",
            "",
            $username,
            $username,
            "active",
            "hotspot",
            $r["used_at"],
            $r["expires_at"]
        ]);
        $added++;
    }else{
        $upd = $pdo->prepare("UPDATE clients SET status='active', expires_at=? WHERE username=?");
        $upd->execute([$r["expires_at"],$username]);
    }
}

echo "Synced clients: ".$added.PHP_EOL;
