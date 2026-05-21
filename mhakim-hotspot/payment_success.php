<?php
require_once "/var/www/html/mhakim-billing-system/config/database.php";
$checkout = $_GET["checkout_id"] ?? "";
$stmt = $pdo->prepare("
SELECT payments.*, packages.name AS package_name, packages.duration_hours 
FROM payments 
LEFT JOIN packages ON payments.package_id=packages.id 
WHERE payments.checkout_id=? OR payments.reference=? 
ORDER BY payments.id DESC LIMIT 1
");
$stmt->execute([$checkout,$checkout]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Payment Successful</title>
<link rel="stylesheet" href="assets/hakim-ui.css">
</head>
<body>
<div class="center">
<div class="panel">
  <div class="brand" style="justify-content:center"><div class="logo">H</div><div>Hakim<br><span class="green">Networks</span></div></div>

  <div class="successIcon">✓</div>
  <h2>Payment Successful!</h2>
  <p>Your internet is now active.</p>

  <div class="card">
    <div class="row"><span>Package</span><b><?= htmlspecialchars($p["package_name"] ?? "Internet Package") ?></b></div>
    <div class="row"><span>Amount</span><b>KES <?= htmlspecialchars($p["amount"] ?? "0") ?></b></div>
    <div class="row"><span>Duration</span><b><?= htmlspecialchars($p["duration_hours"] ?? "") ?> Hours</b></div>
    <div class="row"><span>Status</span><b class="green">Active</b></div>
  </div>

  <p>Enjoy fast and unlimited internet.</p>
  <a class="btn" style="width:100%" href="http://neverssl.com">Open Internet</a>
</div>
</div>
</body>
</html>
