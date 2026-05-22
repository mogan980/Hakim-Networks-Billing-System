<?php
require_once __DIR__ . "/config/database.php";

$vouchers = $pdo->query("
SELECT * FROM smart_vouchers
WHERE status='unused'
ORDER BY id DESC
LIMIT 200
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Print Hakim Vouchers</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:Arial;background:#eef3f7;margin:0;padding:25px;color:#020617}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
button{background:#16a34a;color:white;border:0;padding:12px 18px;border-radius:12px;font-weight:900}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.card{background:white;border:2px dashed #16a34a;border-radius:18px;padding:18px;page-break-inside:avoid}
.brand{font-size:20px;font-weight:900;color:#064e3b}
.code{font-size:28px;font-weight:900;margin:14px 0;color:#020617}
.meta{font-size:13px;color:#334155;line-height:1.7}
.badge{display:inline-block;background:#dcfce7;color:#166534;padding:6px 10px;border-radius:999px;font-weight:900;font-size:12px}
@media print{
.top{display:none}
body{background:white;padding:0}
.grid{grid-template-columns:repeat(3,1fr)}
.card{box-shadow:none}
}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="top">
<div>
<h1>Hakim Networks Vouchers</h1>
<p>Printing unused generated vouchers only.</p>
</div>
<button onclick="window.print()">Print Now</button>
</div>

<div class="grid">
<?php foreach($vouchers as $v): ?>
<div class="card">
<div class="brand">Hakim Networks</div>
<span class="badge">UNUSED</span>
<div class="code"><?php echo htmlspecialchars($v["voucher_code"]); ?></div>
<div class="meta">
<b>Package:</b> <?php echo htmlspecialchars($v["package_name"]); ?><br>
<b>Speed:</b> <?php echo htmlspecialchars($v["speed_down"]."/".$v["speed_up"]); ?><br>
<b>Duration:</b> <?php echo htmlspecialchars($v["duration_hours"]); ?> hrs<br>
<b>Price:</b> KES <?php echo number_format((float)$v["price"]); ?><br>
<b>Created:</b> <?php echo htmlspecialchars($v["created_at"]); ?>
</div>
</div>
<?php endforeach; ?>
</div>
</body>
</html>
