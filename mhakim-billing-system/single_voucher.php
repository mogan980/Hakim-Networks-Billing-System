<?php
require_once __DIR__ . "/config/database.php";
$id = (int)($_GET["id"] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM smart_vouchers WHERE id=?");
$stmt->execute([$id]);
$v = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$v){ die("Voucher not found"); }
?>
<!DOCTYPE html>
<html>
<head>
<title>Voucher</title>
<style>
body{font-family:Arial;background:#eaf0f5;display:flex;align-items:center;justify-content:center;min-height:100vh}
.card{background:white;border-radius:26px;padding:35px;text-align:center;box-shadow:0 20px 55px rgba(15,23,42,.15)}
.code{font-size:34px;font-weight:900;color:#064e3b;margin:20px}
button{background:#16a34a;color:white;border:0;padding:13px 20px;border-radius:12px;font-weight:900}
</style>
</head>
<body>
<div class="card">
<h1>Hakim Networks Voucher</h1>
<div class="code"><?php echo htmlspecialchars($v["voucher_code"]); ?></div>
<p><?php echo htmlspecialchars($v["package_name"]); ?></p>
<p><?php echo htmlspecialchars($v["speed_down"]."/".$v["speed_up"]); ?> • <?php echo (int)$v["duration_hours"]; ?> hrs</p>
<p>KES <?php echo number_format((float)$v["price"]); ?></p>
<button onclick="window.print()">Print</button>
</div>
</body>
</html>
