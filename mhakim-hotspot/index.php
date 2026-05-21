<?php
require_once "/var/www/html/mhakim-billing-system/config/database.php";
$packages = $pdo->query("SELECT * FROM packages ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hakim Networks</title>
<link rel="stylesheet" href="assets/hakim-ui.css">
</head>
<body>
<div class="page">
  <div class="topbar">
    <div class="brand"><div class="logo">H</div><div>Hakim<br><span class="green">Networks</span></div></div>
    <div class="help">Need Help?<br><b>0704 919 887</b></div>
  </div>

  <div class="hero">
    <h1>Fast. <span class="green">Reliable.</span> Secure Internet</h1>
    <p>Choose a package or redeem your voucher and get connected instantly.</p>
  </div>

  <h2>Internet Packages <span class="green" style="font-size:14px">M-PESA Ready</span></h2>

  <?php foreach($packages as $p): ?>
  <div class="card pkg">
    <div>
      <h3><?= htmlspecialchars($p["name"]) ?></h3>
      <div class="price">KES <?= number_format((float)$p["price"],2) ?></div>
      <div class="meta">
        <span>⬇ <?= htmlspecialchars($p["speed_down"]) ?> Download</span>
        <span>⬆ <?= htmlspecialchars($p["speed_up"]) ?> Upload</span>
        <span>⏱ <?= htmlspecialchars($p["duration_hours"]) ?> Hours</span>
      </div>
    </div>
    <a class="btn" href="pay_now.php?package_id=<?= (int)$p["id"] ?>">Buy with M-PESA →</a>
  </div>
  <?php endforeach; ?>

  <div class="card pkg" style="border-color:#22c55e">
    <div>
      <h3>Have a Voucher?</h3>
      <p>Enter your Hakim Networks voucher code and connect instantly.</p>
    </div>
    <a class="btn dark" href="/mhakim-billing-system/smart_voucher_redeem_page.php">Redeem Voucher</a>
  </div>

  <div class="footer">Instant Activation • Secure Payments • Fast & Stable • 24/7 Support</div>
</div>
</body>
</html>
