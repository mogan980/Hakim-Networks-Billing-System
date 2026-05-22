<?php
date_default_timezone_set("Africa/Nairobi");
require_once __DIR__ . "/config/database.php";

function one($pdo,$sql){
    try { $v=$pdo->query($sql)->fetchColumn(); return $v ?: 0; }
    catch(Exception $e){ return 0; }
}
function money($n){ return "Ksh ".number_format((float)$n,0); }

$today = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()");
$week  = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)");
$month = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
$total = one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'");

$clients  = one($pdo,"SELECT COUNT(*) FROM clients");
$packages = one($pdo,"SELECT COUNT(*) FROM packages");
$vouchers = one($pdo,"SELECT COUNT(*) FROM smart_vouchers");

$payments = $pdo->query("SELECT * FROM payments ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Revenue Performance</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;background:#064e3b;font-family:Inter,Arial,sans-serif;color:#0f172a}
.wrap{margin:22px;background:white;border-radius:28px;padding:20px;box-shadow:0 25px 60px rgba(0,0,0,.18)}
.head{display:flex;justify-content:space-between;align-items:center}
h1{font-size:38px;margin:0}.sub{color:#64748b;font-size:14px}
.toggle{border:1px solid #16a34a;color:#15803d;background:white;border-radius:14px;padding:13px 22px;font-weight:900;cursor:pointer}
.cards{display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:14px;margin:18px 0}
.card{border-radius:22px;padding:18px;min-height:120px;box-shadow:0 12px 28px rgba(15,23,42,.08)}
.card h3{font-size:14px}.amount{font-size:22px;font-weight:950}
.green{background:#dcfce7}.blue{background:#dbeafe}.yellow{background:#fef3c7}.purple{background:#ede9fe}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.stat{border:1px solid #e2e8f0;border-radius:18px;padding:14px;display:flex;justify-content:space-between;font-size:15px;font-weight:800}
.stat span{color:#16a34a}
.table{margin-top:16px;border:1px solid #e2e8f0;border-radius:22px;padding:16px}
table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:14px;border-bottom:1px solid #e5e7eb}
.hide .amount,.hide .money{filter:blur(9px)}
@media(max-width:900px){.cards{grid-template-columns:1fr 1fr}.stats{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap" id="box">
  <div class="head">
    <div>
      <h1>💰 Revenue Performance</h1>
      <div class="sub">Live payment summary from your billing system.</div>
    </div>
    <button class="toggle" onclick="toggleMoney()">👁 Hide Amounts</button>
  </div>

  <div class="cards">
    <div class="card green"><h3>Today Revenue</h3><div class="amount"><?=money($today)?></div><p>Collected today</p></div>
    <div class="card blue"><h3>This Week</h3><div class="amount"><?=money($week)?></div><p>Current week total</p></div>
    <div class="card yellow"><h3>This Month</h3><div class="amount"><?=money($month)?></div><p>Monthly revenue</p></div>
    <div class="card purple"><h3>Total Revenue</h3><div class="amount"><?=money($total)?></div><p>All-time collections</p></div>
  </div>

  <div class="stats">
    <div class="stat">Total Clients <span><?=$clients?></span></div>
    <div class="stat">Packages <span><?=$packages?></span></div>
    <div class="stat">Vouchers <span><?=$vouchers?></span></div>
  </div>

  <div class="table">
    <h2>Recent Payments</h2>
    <table>
      <tr><th>ID</th><th>Phone</th><th>Amount</th><th>Method</th><th>Status</th><th>Time</th></tr>
      <?php foreach($payments as $p): ?>
      <tr>
        <td><?=$p["id"] ?? ""?></td>
        <td><?=$p["phone"] ?? "-"?></td>
        <td class="money"><?=money($p["amount"] ?? 0)?></td>
        <td><?=$p["method"] ?? "-"?></td>
        <td><?=$p["status"] ?? "-"?></td>
        <td><?=$p["created_at"] ?? "-"?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<script>
function toggleMoney(){
  document.getElementById("box").classList.toggle("hide");
}
setInterval(()=>location.reload(),30000);
</script>
</body>
</html>
