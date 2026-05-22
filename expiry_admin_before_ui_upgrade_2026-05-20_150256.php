<?php
require_once __DIR__ . "/config/database.php";

$expired = $pdo->query("
SELECT COUNT(*) FROM smart_vouchers
WHERE status='used'
AND expires_at IS NOT NULL
AND expires_at <= NOW()
")->fetchColumn();

$log = file_exists(__DIR__."/expiry_engine.log")
    ? htmlspecialchars(file_get_contents(__DIR__."/expiry_engine.log"))
    : "No logs yet.";
?>
<!DOCTYPE html>
<html>
<head>
<title>Expiry Engine</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;font-family:Arial;background:#eaf0f5;color:#020617}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:240px;background:#020617;color:white;padding:22px}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;border-radius:12px;margin:7px 0;font-weight:800}
.sidebar a:hover,.active{background:#16a34a;color:#052e16}
.main{margin-left:260px;padding:32px}
.card{background:white;border-radius:22px;padding:24px;margin-bottom:22px;box-shadow:0 18px 45px rgba(15,23,42,.08)}
.btn{background:#16a34a;color:white;padding:13px 18px;border-radius:12px;text-decoration:none;font-weight:900;display:inline-block}
pre{background:#020617;color:#bbf7d0;padding:18px;border-radius:16px;overflow:auto;max-height:420px}
@media(max-width:900px){.sidebar{display:none}.main{margin-left:0}}
</style>
</head>
<body>
<div class="sidebar">
<h2>M.Hakim</h2>
<a href="noc_final_clean.php">📡 Live NOC</a>
<a href="smart_vouchers.php">🎟 Smart Vouchers</a>
<a class="active" href="expiry_admin.php">⏱ Expiry Engine</a>
<a href="payments.php">💳 Payments</a>
</div>

<div class="main">
<h1>Auto Expiry Engine</h1>

<div class="card">
<h2>Expired Waiting for Disconnect</h2>
<p style="font-size:38px;font-weight:900;color:#dc2626"><?php echo $expired; ?></p>
<a class="btn" href="expiry_engine.php">Run Expiry Engine Now</a>
</div>

<div class="card">
<h2>Engine Logs</h2>
<pre><?php echo $log; ?></pre>
</div>
</div>
</body>
</html>
