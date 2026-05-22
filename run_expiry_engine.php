<?php
require_once __DIR__ . "/config/database.php";

$pdo->exec("
UPDATE smart_vouchers
SET status='expired'
WHERE status='used'
AND expires_at IS NOT NULL
AND expires_at < NOW()
");

echo "<h2 style='font-family:Arial;color:green'>Expiry engine completed ✅</h2>";
echo "<a href='expiry_engine.php'>Back</a>";
