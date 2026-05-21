<?php
require_once __DIR__ . "/config/database.php";

$packages = $pdo->query("
SELECT id,name,duration_hours,speed_down,speed_up,price,status
FROM packages
WHERE status='active'
ORDER BY price ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hakim Networks</title>
<style>
*{box-sizing:border-box}
body{
 margin:0;
 font-family:Arial,Helvetica,sans-serif;
 background:radial-gradient(circle at left,#0f7a3b,#052e2b 45%,#020617);
 color:white;
 min-height:100vh;
}
.wrap{max-width:1200px;margin:auto;padding:28px}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}
.brand{display:flex;gap:14px;align-items:center}
.logo{background:#4ade80;color:#052e16;width:52px;height:52px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:25px}
.status{background:#071827;border:1px solid #14532d;padding:12px 18px;border-radius:999px;color:#bbf7d0}
.hero{text-align:center;margin:28px 0}
.hero h1{font-size:34px;margin:0 0 10px}
.hero p{color:#d1fae5;line-height:1.6}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:25px}
.card{
 background:#0b1728;
 border:1px solid rgba(74,222,128,.25);
 border-radius:22px;
 padding:20px;
 box-shadow:0 20px 50px rgba(0,0,0,.28);
}
.card h3{margin:0 0 14px;font-size:20px}
.price{font-size:32px;font-weight:900;color:#22c55e;margin-bottom:12px}
.tags{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.tag{background:#020617;border:1px solid #1e293b;padding:7px 11px;border-radius:999px;font-size:12px;color:#cbd5e1}
.pay{
 display:block;
 width:100%;
 background:linear-gradient(135deg,#22c55e,#86efac);
 color:#052e16;
 padding:15px;
 border-radius:16px;
 font-weight:900;
 text-align:center;
 text-decoration:none;
 cursor:pointer;
}
.pay:hover{filter:brightness(1.08)}
.voucher{
 margin-top:35px;
 background:rgba(255,255,255,.08);
 border:1px solid rgba(255,255,255,.12);
 border-radius:24px;
 padding:22px;
 max-width:520px;
}
input{width:100%;padding:15px;border-radius:14px;border:1px solid #334155;background:#020617;color:white;margin-top:10px}
button{width:100%;background:#22c55e;color:#052e16;border:0;padding:15px;border-radius:16px;font-weight:900;margin-top:12px}
.support{position:fixed;right:22px;bottom:22px;background:#22c55e;color:#052e16;padding:14px 20px;border-radius:999px;text-decoration:none;font-weight:900}
@media(max-width:1000px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:650px){.grid{grid-template-columns:1fr}.top{align-items:flex-start;gap:12px;flex-direction:column}.hero h1{font-size:28px}}
</style>
</head>
<body>

<div class="wrap">
 <div class="top">
  <div class="brand">
   <div class="logo">H</div>
   <div>
    <h2 style="margin:0">Hakim Networks</h2>
    <small>Fast • Secure • Reliable Internet</small>
   </div>
  </div>
  <div class="status">● Live Hotspot</div>
 </div>

 <div class="hero">
  <h1>Buy Internet Access Instantly</h1>
  <p>Select a package, pay with M-Pesa STK Push, and connect automatically.</p>
 </div>

 <div class="grid">
 <?php foreach($packages as $pkg): ?>
  <div class="card">
   <h3><?php echo htmlspecialchars($pkg["name"]); ?></h3>
   <div class="price">KES <?php echo number_format((float)$pkg["price"]); ?></div>
   <div class="tags">
    <span class="tag">⚡ <?php echo htmlspecialchars($pkg["speed_down"]."/".$pkg["speed_up"]); ?></span>
    <span class="tag">⏱ <?php echo htmlspecialchars($pkg["duration_hours"]); ?> hrs</span>
   </div>

   <a class="pay" href="pay_now.php?package_id=<?php echo (int)$pkg["id"]; ?>">
    Pay with M-Pesa STK
   </a>
  </div>
 <?php endforeach; ?>
 </div>

 <div class="voucher">
  <h2>Redeem Voucher</h2>
  <form method="POST" action="redeem_voucher.php">
   <input name="voucher_code" placeholder="Enter voucher code e.g. MH-XXXXXXX" required>
   <button type="submit">Activate Voucher</button>
  </form>
 </div>
</div>

<a class="support" href="https://wa.me/254704467699">💬 WhatsApp Support</a>

</body>
</html>
