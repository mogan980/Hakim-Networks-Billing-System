<?php
date_default_timezone_set("Africa/Nairobi");
require_once __DIR__ . "/config/database.php";

function vcount($pdo, $sql){
    $v = $pdo->query($sql)->fetchColumn();
    return $v === false || $v === null ? 0 : (int)$v;
}

$total = vcount($pdo, "SELECT COUNT(*) FROM smart_vouchers");
$used = vcount($pdo, "SELECT COUNT(*) FROM smart_vouchers WHERE status='used'");
$unused = vcount($pdo, "SELECT COUNT(*) FROM smart_vouchers WHERE status='unused'");
$usedPct = $total > 0 ? round(($used / $total) * 100) : 0;
$unusedPct = $total > 0 ? 100 - $usedPct : 100;
?>
<style>
.voucher-status-pro{
  background:#fff;
  border-radius:22px;
  padding:18px;
  margin:18px 0;
  box-shadow:0 16px 38px rgba(15,23,42,.08);
  border:1px solid #e2e8f0;
  font-family:Inter,Arial,sans-serif;
  max-height:360px;
  overflow:hidden;
}
.vsp-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.vsp-head h2{font-size:22px;margin:0;color:#0f172a}
.vsp-head p{font-size:13px;margin:5px 0 0;color:#64748b}
.vsp-live{background:#dcfce7;color:#166534;padding:7px 12px;border-radius:999px;font-weight:900;font-size:12px}
.vsp-body{display:grid;grid-template-columns:160px 1fr;gap:18px;align-items:center}
.vsp-pie{
  width:145px;height:145px;border-radius:50%;
  background:conic-gradient(#3b9eea 0 <?=$unusedPct?>%, #ff5b8a <?=$unusedPct?>% 100%);
  position:relative;margin:auto;
}
.vsp-pie:after{
  content:"";position:absolute;inset:34px;background:white;border-radius:50%;
  box-shadow:inset 0 0 0 1px #e5e7eb;
}
.vsp-center{
  position:absolute;inset:0;display:grid;place-items:center;z-index:2;
  font-weight:900;color:#0f172a;font-size:20px;
}
.vsp-stats{display:grid;gap:10px}
.vsp-box{border-radius:16px;padding:12px 14px;min-height:65px}
.vsp-box b{display:block;font-size:24px;color:#0f172a}
.vsp-box span{font-size:12px;font-weight:800;color:#475569}
.vsp-unused{background:#bfdbfe}
.vsp-used{background:#f9a8d4}
.vsp-total{background:#cbd5e1}
.vsp-legend{display:flex;gap:16px;justify-content:center;margin-top:12px;font-size:12px;color:#475569}
.vsp-dot{display:inline-block;width:18px;height:8px;border-radius:999px;margin-right:6px}
@media(max-width:800px){
  .vsp-body{grid-template-columns:1fr}
  .voucher-status-pro{max-height:none}
}
</style>

<section class="voucher-status-pro">
  <div class="vsp-head">
    <div>
      <h2>🎫 Voucher Status</h2>
      <p>Used and unused vouchers from the live billing database.</p>
    </div>
    <span class="vsp-live">● Live</span>
  </div>

  <div class="vsp-body">
    <div>
      <div class="vsp-pie">
        <div class="vsp-center"><?=$total?></div>
      </div>
      <div class="vsp-legend">
        <span><i class="vsp-dot" style="background:#3b9eea"></i>Unused</span>
        <span><i class="vsp-dot" style="background:#ff5b8a"></i>Used</span>
      </div>
    </div>

    <div class="vsp-stats">
      <div class="vsp-box vsp-unused"><span>Unused Vouchers</span><b><?=$unused?></b></div>
      <div class="vsp-box vsp-used"><span>Used Vouchers</span><b><?=$used?></b></div>
      <div class="vsp-box vsp-total"><span>Total Vouchers</span><b><?=$total?></b></div>
    </div>
  </div>
</section>
