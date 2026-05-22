<?php
date_default_timezone_set("Africa/Nairobi");
require_once __DIR__ . "/config/database.php";

function qval($pdo,$sql){
    try{
        $v=$pdo->query($sql)->fetchColumn();
        return is_numeric($v) ? (float)$v : 0;
    }catch(Exception $e){ return 0; }
}
function qcount($pdo,$table){
    try{
        return (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    }catch(Exception $e){ return 0; }
}
function money($n){ return "Ksh ".number_format((float)$n,0); }

$today=qval($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()");
$week=qval($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)");
$month=qval($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
$total=qval($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'");

$clients=qcount($pdo,"clients");
$packages=qcount($pdo,"packages");
$vouchers=qcount($pdo,"smart_vouchers");
?>
<style>
.clean-revenue-card{background:#fff;border-radius:26px;padding:26px;margin:20px 0;box-shadow:0 20px 50px rgba(15,23,42,.10);border:1px solid #e2e8f0;font-family:Inter,Arial,sans-serif;color:#071327}
.cr-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.cr-head h2{margin:0;font-size:26px;font-weight:900}.cr-head p{margin:6px 0 0;color:#64748b}
.cr-toggle{background:#fff;border:1px solid #dbe4ef;border-radius:16px;padding:10px 14px;font-weight:800;cursor:pointer;box-shadow:0 8px 22px rgba(15,23,42,.08)}
.cr-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.cr-card{position:relative;overflow:hidden;border-radius:20px;padding:20px;min-height:145px;box-shadow:0 12px 28px rgba(15,23,42,.07)}
.cr-card:after{content:"";position:absolute;right:-30px;top:-30px;width:110px;height:110px;background:rgba(255,255,255,.45);border-radius:50%}
.cr-card small{display:block;color:#475569;font-weight:900;margin-bottom:14px}
.cr-money{font-size:28px;font-weight:950;letter-spacing:-.5px}.cr-card p{margin:10px 0 0;color:#526072;font-weight:700;font-size:13px}
.c1{background:linear-gradient(135deg,#dcfce7,#bbf7d0)}.c2{background:linear-gradient(135deg,#dbeafe,#bfdbfe)}.c3{background:linear-gradient(135deg,#fef3c7,#fde68a)}.c4{background:linear-gradient(135deg,#ede9fe,#ddd6fe)}
.cr-mini{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:18px}
.cr-mini a{display:flex;justify-content:space-between;align-items:center;text-decoration:none;color:#071327;border:1px solid #e2e8f0;border-radius:16px;padding:14px 18px;font-weight:900;background:#fff;box-shadow:0 8px 18px rgba(15,23,42,.04)}
.cr-mini span{color:#16a34a;font-size:20px}.cr-hidden .cr-money,.cr-hidden .cr-mini span{filter:blur(8px);user-select:none}
@media(max-width:900px){.cr-grid{grid-template-columns:1fr 1fr}.cr-mini{grid-template-columns:1fr}}
</style>

<section class="clean-revenue-card" id="cleanRevenueCard">
  <div class="cr-head">
    <div>
      <h2>💰 Revenue Performance</h2>
      <p>Live payment summary from your billing system.</p>
    </div>
    <button class="cr-toggle" onclick="toggleCleanRevenue()">👁 <span id="crToggleText">Hide Numbers</span></button>
  </div>

  <div class="cr-grid">
    <div class="cr-card c1"><small>Today Revenue</small><div class="cr-money"><?=money($today)?></div><p>Collected today</p></div>
    <div class="cr-card c2"><small>This Week</small><div class="cr-money"><?=money($week)?></div><p>Current week total</p></div>
    <div class="cr-card c3"><small>This Month</small><div class="cr-money"><?=money($month)?></div><p>Monthly revenue</p></div>
    <div class="cr-card c4"><small>Total Revenue</small><div class="cr-money"><?=money($total)?></div><p>All-time collections</p></div>
  </div>

  <div class="cr-mini">
    <a href="clients.php">Total Clients <span><?=$clients?></span></a>
    <a href="packages.php">Packages <span><?=$packages?></span></a>
    <a href="vouchers.php">Vouchers <span><?=$vouchers?></span></a>
  </div>
</section>

<script>
function toggleCleanRevenue(){
  const box=document.getElementById("cleanRevenueCard");
  const txt=document.getElementById("crToggleText");
  box.classList.toggle("cr-hidden");
  txt.textContent=box.classList.contains("cr-hidden")?"Show Numbers":"Hide Numbers";
}
</script>
