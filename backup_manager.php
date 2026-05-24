<?php
date_default_timezone_set("Africa/Nairobi");

$db = glob("backups/database/*.sql") ?: [];
$sys = glob("backups/system/*.tar.gz") ?: [];
rsort($db);
rsort($sys);

function sizef($f){
    if(!file_exists($f)) return "-";
    $s=filesize($f);
    if($s>=1073741824) return round($s/1073741824,2)." GB";
    if($s>=1048576) return round($s/1048576,2)." MB";
    if($s>=1024) return round($s/1024,2)." KB";
    return $s." B";
}
function agef($f){
    if(!file_exists($f)) return "-";
    return date("Y-m-d H:i:s", filemtime($f));
}
$lastDb = $db[0] ?? null;
$lastSys = $sys[0] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
<title>Backup Manager</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;background:#07131f;color:#e5e7eb;font-family:Arial,sans-serif}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020617;padding:24px 16px}
.sidebar h2{color:#22c55e;margin:0 0 5px}.sidebar p{color:#94a3b8;font-size:12px;margin:0 0 25px}
.sidebar a{display:block;color:white;text-decoration:none;background:#111827;margin:9px 0;padding:13px 15px;border-radius:13px;font-weight:800}
.sidebar a.active{background:#16a34a}.sidebar a:hover{background:#14532d}
.main{margin-left:260px;padding:28px}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
h1{font-size:34px;margin:0}.sub{color:#94a3b8;margin-top:6px}
.status{background:#064e3b;color:#86efac;border:1px solid #15803d;border-radius:999px;padding:10px 16px;font-weight:900}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px}
.card{background:#0f1b2d;border:1px solid #1e293b;border-radius:20px;padding:20px;box-shadow:0 10px 25px rgba(0,0,0,.15)}
.card small{color:#94a3b8;font-weight:800}.card b{display:block;font-size:24px;margin-top:10px;color:#22c55e}
.box{background:#0f1b2d;border:1px solid #1e293b;border-radius:22px;padding:22px;margin-bottom:22px}
table{width:100%;border-collapse:collapse}
th{background:#020617;padding:13px;text-align:left}
td{padding:13px;border-bottom:1px solid #1e293b}
.btn{display:inline-block;padding:9px 13px;background:#16a34a;color:white;text-decoration:none;border-radius:11px;font-weight:900}
.btn-blue{background:#2563eb}.btn-gray{background:#334155}
.actions{display:flex;gap:10px;flex-wrap:wrap;margin:18px 0}
.empty{color:#94a3b8;padding:16px}
@media(max-width:900px){.sidebar{position:relative;width:auto}.main{margin-left:0}.cards{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<p>Hakim Networks ISP</p>
<a href="noc_final_clean.php">📊 Dashboard</a>
<a href="health_check.php">💙 Health Check</a>
<a href="queue_control_pro.php">⚡ Queue Control</a>
<a href="client_expiry_manager.php">⏱ Client Expiry</a>
<a href="smart_vouchers.php">💳 Smart Vouchers</a>
<a href="stk_live_monitor.php">💰 STK Monitor</a>
<a class="active" href="backup_manager.php">💾 Backup Manager</a>
</div>

<div class="main">
  <div class="top">
    <div>
      <h1>💾 Backup Manager</h1>
      <div class="sub">Database and system restore-point center for Hakim Networks.</div>
    </div>
    <div class="status">● Backup Ready</div>
  </div>

  <div class="cards">
    <div class="card"><small>Database Backups</small><b><?=count($db)?></b></div>
    <div class="card"><small>System Backups</small><b><?=count($sys)?></b></div>
    <div class="card"><small>Latest DB Backup</small><b><?= $lastDb ? agef($lastDb) : "-" ?></b></div>
    <div class="card"><small>Latest System Backup</small><b><?= $lastSys ? agef($lastSys) : "-" ?></b></div>
  </div>

  <div class="actions">
    <a class="btn" href="backup_manager.php">↻ Refresh</a>
    <a class="btn btn-blue" href="noc_final_clean.php">← Dashboard</a>
  </div>

  
<div class="box">
<h2>⚙️ Automatic Backup Schedule</h2>
<p style="color:#94a3b8">Backups run automatically every 6 hours using Linux cron.</p>
<div style="background:#052e16;color:#86efac;padding:14px;border-radius:14px;font-weight:900">
✅ Auto Backup Active
</div>
</div>

<div class="box">
    <h2>Database Backups</h2>
    <table>
      <tr><th>File</th><th>Size</th><th>Created</th><th>Action</th></tr>
      <?php if(!$db): ?>
        <tr><td colspan="4" class="empty">No database backups found.</td></tr>
      <?php endif; ?>
      <?php foreach($db as $f): ?>
      <tr>
        <td><?=basename($f)?></td>
        <td><?=sizef($f)?></td>
        <td><?=agef($f)?></td>
        <td><a class="btn" href="<?=$f?>">Download</a></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <div class="box">
    <h2>System Backups</h2>
    <table>
      <tr><th>File</th><th>Size</th><th>Created</th><th>Action</th></tr>
      <?php if(!$sys): ?>
        <tr><td colspan="4" class="empty">No system backups found.</td></tr>
      <?php endif; ?>
      <?php foreach($sys as $f): ?>
      <tr>
        <td><?=basename($f)?></td>
        <td><?=sizef($f)?></td>
        <td><?=agef($f)?></td>
        <td><a class="btn" href="<?=$f?>">Download</a></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
</body>
</html>
