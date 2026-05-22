<?php
require_once __DIR__ . "/config/database.php";

if(isset($_GET["delete"])){
    $stmt = $pdo->prepare("DELETE FROM smart_vouchers WHERE id=?");
    $stmt->execute([(int)$_GET["delete"]]);
    header("Location: smart_vouchers.php?deleted=1");
    exit;
}

if(isset($_POST["update_voucher"])){
    $stmt = $pdo->prepare("
        UPDATE smart_vouchers
        SET package_name=?, speed_down=?, speed_up=?, duration_hours=?, price=?, status=?, expires_at=?, updated_at=NOW()
        WHERE id=?
    ");
    $stmt->execute([
        $_POST["package_name"],
        $_POST["speed_down"],
        $_POST["speed_up"],
        $_POST["duration_hours"],
        $_POST["price"],
        $_POST["status"],
        $_POST["expires_at"] ?: null,
        (int)$_POST["id"]
    ]);
    header("Location: smart_vouchers.php?updated=1");
    exit;
}

function makeCode(){
    return "MH-" . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["generate"])){
    $qty = max(1, (int)($_POST["qty"] ?? 1));

    for($i=0; $i<$qty; $i++){
        $code = makeCode();

        $stmt = $pdo->prepare("
            INSERT INTO smart_vouchers
            (voucher_code, package_name, speed_down, speed_up, duration_hours, price, status)
            VALUES (?, ?, ?, ?, ?, ?, 'unused')
        ");
        $stmt->execute([
            $code,
            $_POST["package_name"],
            $_POST["speed_down"],
            $_POST["speed_up"],
            $_POST["duration_hours"],
            $_POST["price"]
        ]);
    }

    header("Location: smart_vouchers.php?generated=1");
    exit;
}

$edit = null;
if(isset($_GET["edit"])){
    $stmt = $pdo->prepare("SELECT * FROM smart_vouchers WHERE id=? LIMIT 1");
    $stmt->execute([(int)$_GET["edit"]]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$view = null;
if(isset($_GET["view"])){
    $stmt = $pdo->prepare("SELECT * FROM smart_vouchers WHERE id=? LIMIT 1");
    $stmt->execute([(int)$_GET["view"]]);
    $view = $stmt->fetch(PDO::FETCH_ASSOC);
}

$vouchers = $pdo->query("
SELECT * FROM smart_vouchers
ORDER BY id DESC
LIMIT 500
")->fetchAll(PDO::FETCH_ASSOC);

$total = count($vouchers);
$unused = count(array_filter($vouchers, fn($v)=>$v["status"]==="unused"));
$used = count(array_filter($vouchers, fn($v)=>$v["status"]==="used"));
$expired = count(array_filter($vouchers, fn($v)=>$v["status"]==="expired"));
?>
<!DOCTYPE html>
<html>
<head>
<title>Smart Vouchers • Hakim Networks</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#071018;color:#e2e8f0}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:linear-gradient(180deg,#020617,#062923);padding:24px;overflow:auto;border-right:1px solid rgba(255,255,255,.07)}
.sidebar h2{color:#22c55e;margin:0 0 4px;font-size:26px}
.sidebar small{color:#94a3b8}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px 15px;border-radius:14px;margin:8px 0;font-weight:800}
.sidebar a:hover,.sidebar .active{background:#16a34a;color:#052e16}
.main{margin-left:280px;padding:32px}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;gap:18px}
.top h1{margin:0;font-size:34px}
.live{background:#052e2b;border:1px solid #14532d;color:#86efac;padding:10px 16px;border-radius:999px;font-weight:900}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:22px}
.card{background:linear-gradient(180deg,#0b1728,#08111d);border:1px solid #1f2937;border-radius:24px;padding:22px;box-shadow:0 18px 45px rgba(0,0,0,.25);margin-bottom:22px}
.stat h3,.card h3{color:#94a3b8;margin:0 0 12px}
.big{font-size:36px;font-weight:900;color:#22c55e}
.formgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
input,select{width:100%;padding:13px;border-radius:14px;border:1px solid #334155;background:#020617;color:white}
button,.btn{border:0;padding:11px 15px;border-radius:13px;font-weight:900;text-decoration:none;display:inline-block;cursor:pointer;font-size:13px}
.green{background:#16a34a;color:white}.blue{background:#2563eb;color:white}.red{background:#dc2626;color:white}.gray{background:#64748b;color:white}.orange{background:#f97316;color:white}
.filters{display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin-bottom:16px}
.tablewrap{overflow:visible}
table{width:100%;border-collapse:collapse;min-width:0;table-layout:auto}
th{background:#020617;color:white;text-align:left;padding:12px;white-space:normal;font-size:13px}
td{padding:10px;border-bottom:1px solid #1f2937;white-space:normal;vertical-align:top;font-size:13px}
.badge{padding:7px 12px;border-radius:999px;font-weight:900;font-size:12px}
.unused{background:#dcfce7;color:#166534}.used{background:#dbeafe;color:#1d4ed8}.expired{background:#fee2e2;color:#991b1b}
.online{background:#dcfce7;color:#166534}.offline{background:#fee2e2;color:#991b1b}
.voucher-code{font-weight:900;color:#86efac}
.actions{display:flex;gap:6px;flex-wrap:wrap;max-width:180px}.actions .btn{padding:8px 10px;font-size:12px}
.alert{padding:14px;border-radius:16px;font-weight:900;margin-bottom:18px}
.ok{background:#dcfce7;color:#166534}.bad{background:#fee2e2;color:#991b1b}
.modal{position:fixed;inset:0;background:rgba(2,6,23,.75);display:flex;align-items:center;justify-content:center;padding:20px;z-index:9999}
.modalbox{background:#0b1728;border:1px solid #1f2937;border-radius:26px;padding:26px;max-width:560px;width:100%;box-shadow:0 30px 80px rgba(0,0,0,.45)}
.detail{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.detail div{background:#020617;border:1px solid #1f2937;border-radius:16px;padding:14px}
small{color:#94a3b8}
@media(max-width:1000px){.sidebar{display:none}.main{margin-left:0}.grid,.formgrid,.filters,.detail{grid-template-columns:1fr}.top{display:block}}
</style>

<style>

.hero{
display:flex;
justify-content:space-between;
align-items:center;
gap:24px;
margin-bottom:28px;
padding:34px;
border-radius:32px;
background:
linear-gradient(135deg,rgba(34,197,94,.16),rgba(15,23,42,.92)),
linear-gradient(180deg,#071018,#020617);
border:1px solid rgba(255,255,255,.06);
box-shadow:0 25px 70px rgba(0,0,0,.35);
overflow:hidden;
position:relative;
}

.hero::before{
content:"";
position:absolute;
right:-120px;
top:-120px;
width:320px;
height:320px;
background:radial-gradient(circle,#22c55e33,transparent 70%);
}

.hero-left{
position:relative;
z-index:2;
max-width:720px;
}

.hero-badge{
display:inline-flex;
align-items:center;
gap:8px;
padding:10px 16px;
border-radius:999px;
background:#052e2b;
border:1px solid #14532d;
color:#86efac;
font-weight:800;
font-size:13px;
margin-bottom:18px;
}

.hero h1{
font-size:46px;
margin:0 0 14px;
line-height:1.1;
}

.hero p{
font-size:16px;
line-height:1.7;
color:#cbd5e1;
max-width:650px;
margin-bottom:24px;
}

.hero-actions{
display:flex;
gap:12px;
flex-wrap:wrap;
}

.hero-btn{
padding:14px 20px;
border-radius:18px;
font-weight:900;
text-decoration:none;
display:inline-flex;
align-items:center;
gap:10px;
font-size:14px;
}

.hero-right{
display:grid;
grid-template-columns:1fr;
gap:14px;
position:relative;
z-index:2;
min-width:220px;
}

.mini-card{
padding:22px;
border-radius:22px;
background:rgba(2,6,23,.8);
border:1px solid rgba(255,255,255,.06);
backdrop-filter:blur(8px);
}

.mini-card small{
color:#94a3b8;
display:block;
margin-bottom:8px;
}

.mini-card h2{
margin:0;
font-size:26px;
color:#22c55e;
}

.top-controls{
display:flex;
justify-content:space-between;
align-items:center;
gap:16px;
margin-bottom:18px;
flex-wrap:wrap;
}

.toggle-btn{
background:#22c55e;
color:#052e16;
padding:12px 18px;
border-radius:16px;
border:none;
font-weight:900;
cursor:pointer;
box-shadow:0 10px 25px rgba(34,197,94,.25);
transition:.25s;
}

.toggle-btn:hover{
transform:translateY(-2px);
}

.filters{
display:grid;
grid-template-columns:2fr 1fr 1fr;
gap:12px;
flex:1;
}

@media(max-width:1000px){

.hero{
flex-direction:column;
align-items:flex-start;
padding:26px;
}

.hero h1{
font-size:34px;
}

.hero-right{
width:100%;
grid-template-columns:1fr 1fr;
}

.filters{
grid-template-columns:1fr;
width:100%;
}

.top-controls{
flex-direction:column;
align-items:stretch;
}

}

</style>

<style>
.hero{
padding:20px 24px !important;
border-radius:24px !important;
margin-bottom:18px !important;
}
.hero h1{
font-size:34px !important;
margin-bottom:10px !important;
}
.hero p{
font-size:14px !important;
line-height:1.5 !important;
margin-bottom:16px !important;
}
.hero-badge{
padding:7px 12px !important;
font-size:12px !important;
margin-bottom:12px !important;
}
.hero-right{
min-width:160px !important;
}
.mini-card{
padding:14px 18px !important;
border-radius:18px !important;
}
.mini-card h2{
font-size:20px !important;
}
.hero-btn{
padding:11px 16px !important;
border-radius:14px !important;
}
</style>
<link rel="stylesheet" href="smart_voucher_online_pill_fix.css">

<style id="SV_ONLINE_COMPACT_STYLE">
.sv-online-pill,.sv-offline-pill{
  display:inline-block!important;
  width:auto!important;
  height:auto!important;
  min-width:72px!important;
  padding:6px 10px!important;
  border-radius:999px!important;
  font-size:11px!important;
  line-height:1!important;
  font-weight:800!important;
  text-align:center!important;
}
.sv-online-pill{background:#dcfce7!important;color:#166534!important}
.sv-offline-pill{background:#fee2e2!important;color:#991b1b!important}
</style>
</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<small>Hakim Networks ISP</small>
<br><br>
<a href="noc_final_clean.php">📡 Live NOC</a>
<a href="client_expiry_manager.php">⏱ Expiry Engine</a>
<a href="dashboard.php">📊 Dashboard</a>
<a href="packages.php">📦 Packages</a>
<a class="active" href="smart_vouchers.php">🎟 Smart Vouchers</a>
<a href="expiry_admin.php">⏱ Expiry Engine</a>
<a href="stk_live_monitor.php">💳 STK Monitor</a>
<a href="payments.php">💰 Payments</a>
<a href="routers.php">🛰 Routers</a>
</div>

<div class="main">

<div class="hero">

<div class="hero-left">
<div class="hero-badge">⚡ Hakim Networks • ISP Core</div>

<h1>Smart Voucher Management</h1>

<p>
Generate, monitor, manage and deliver MikroTik hotspot vouchers
through a powerful professional billing environment.
</p>

<div class="hero-actions">
<a class="hero-btn green" href="#generateArea">+ Generate Voucher</a>
<a class="hero-btn blue" href="print_vouchers.php" target="_blank">🖨 Print Vouchers</a>
</div>
</div>

<div class="hero-right">
<div class="mini-card">
<small>System Status</small>
<h2>ONLINE</h2>
</div>

<div class="mini-card">
<small>Live Monitoring</small>
<h2>ACTIVE</h2>
</div>
</div>

</div>

<?php if(isset($_GET["generated"])): ?><div class="alert ok">Vouchers generated successfully.</div><?php endif; ?>
<?php if(isset($_GET["updated"])): ?><div class="alert ok">Voucher updated successfully.</div><?php endif; ?>
<?php if(isset($_GET["deleted"])): ?><div class="alert bad">Voucher deleted successfully.</div><?php endif; ?>

<div class="grid">
<div class="card stat"><h3>Total Vouchers</h3><div class="big"><?php echo $total; ?></div></div>
<div class="card stat"><h3>Unused</h3><div class="big"><?php echo $unused; ?></div></div>
<div class="card stat"><h3>Used</h3><div class="big"><?php echo $used; ?></div></div>
<div class="card stat"><h3>Expired</h3><div class="big"><?php echo $expired; ?></div></div>
</div>

<div class="card" id="generateArea">
<h2>Generate Vouchers</h2>
<form method="POST">
<div class="formgrid">
<input name="package_name" placeholder="Package e.g. 24 Hours" value="24 Hours" required>
<input name="speed_down" placeholder="Download e.g. 8M" value="8M" required>
<input name="speed_up" placeholder="Upload e.g. 2M" value="2M" required>
<input name="duration_hours" type="number" placeholder="Duration hours" value="24" required>
<input name="price" type="number" placeholder="Price" value="50" required>
<input name="qty" type="number" placeholder="Quantity" value="5" required>
</div>
<br>
<button class="green" name="generate">Generate & Save</button>
<a class="blue btn" href="print_vouchers.php" target="_blank">Print</a>
<a class="gray btn" href="expiry_admin.php">Expiry Engine</a>
</form>
</div>

<?php if($edit): ?>
<div class="card" id="generateArea">
<h2>Edit Voucher: <?php echo htmlspecialchars($edit["voucher_code"]); ?></h2>
<form method="POST">
<input type="hidden" name="id" value="<?php echo (int)$edit["id"]; ?>">
<div class="formgrid">
<input name="package_name" value="<?php echo htmlspecialchars($edit["package_name"]); ?>" required>
<input name="speed_down" value="<?php echo htmlspecialchars($edit["speed_down"]); ?>" required>
<input name="speed_up" value="<?php echo htmlspecialchars($edit["speed_up"]); ?>" required>
<input name="duration_hours" type="number" value="<?php echo htmlspecialchars($edit["duration_hours"]); ?>" required>
<input name="price" type="number" value="<?php echo htmlspecialchars($edit["price"]); ?>" required>
<select name="status">
<option value="unused" <?php if($edit["status"]==="unused") echo "selected"; ?>>unused</option>
<option value="used" <?php if($edit["status"]==="used") echo "selected"; ?>>used</option>
<option value="expired" <?php if($edit["status"]==="expired") echo "selected"; ?>>expired</option>
</select>
<input name="expires_at" placeholder="Expiry YYYY-MM-DD HH:MM:SS" value="<?php echo htmlspecialchars($edit["expires_at"] ?? ""); ?>">
</div>
<br>
<button class="green" name="update_voucher">Save Changes</button>
<a class="gray btn" href="smart_vouchers.php">Cancel</a>
</form>
</div>
<?php endif; ?>

<div class="card" id="generateArea">
<h2>Voucher List</h2>


<div class="top-controls">

<div class="filters">

<input id="searchBox" placeholder="Search code, package, IP, status...">
<select id="statusFilter">
<option value="">All Status</option>
<option value="unused">Unused</option>
<option value="used">Used</option>
<option value="expired">Expired</option>
</select>
<select id="onlineFilter">
<option value="">All Online</option>
<option value="online">Online</option>
<option value="offline">Offline</option>
</select>

</div>

<button id="toggleRowsBtn" class="toggle-btn">
Show All Vouchers
</button>

</div>

<div class="tablewrap">

<table id="voucherTable">
<tr>
<th>Code</th><th>Package</th><th>Speed</th><th>Duration</th><th>Price</th><th>Status</th><th>Online</th><th>Created</th><th>Used</th><th>Expires</th><th>Used By</th><th>Actions</th>
</tr>

<?php $rowCount=0; foreach($vouchers as $v): $rowCount++; ?>
<tr data-row="<?php echo $rowCount; ?>" data-voucher="<?php echo htmlspecialchars($v["voucher_code"]); ?>" data-status="<?php echo htmlspecialchars($v["status"]); ?>" data-online="offline">
<td class="voucher-code"><?php echo htmlspecialchars($v["voucher_code"]); ?></td>
<td><?php echo htmlspecialchars($v["package_name"]); ?></td>
<td><?php echo htmlspecialchars($v["speed_down"]."/".$v["speed_up"]); ?></td>
<td><?php echo (int)$v["duration_hours"]; ?> hrs</td>
<td>KES <?php echo number_format((float)$v["price"]); ?></td>
<td><span class="badge <?php echo $v["status"]; ?>"><?php echo strtoupper($v["status"]); ?></span></td>
<td><span class="badge offline online-status">OFFLINE</span></td>
<td><?php echo htmlspecialchars($v["created_at"] ?? "-"); ?></td>
<td><?php echo htmlspecialchars($v["used_at"] ?? "-"); ?></td>
<td><?php echo htmlspecialchars($v["expires_at"] ?? "-"); ?></td>
<td><?php echo htmlspecialchars($v["used_by"] ?? "-"); ?></td>
<td>
<div class="actions">
<a class="blue btn" href="smart_vouchers.php?view=<?php echo (int)$v["id"]; ?>">View</a>
<a class="gray btn" href="smart_vouchers.php?edit=<?php echo (int)$v["id"]; ?>">Edit</a>
<a class="orange btn" target="_blank" href="https://wa.me/?text=<?php echo urlencode('Hakim Networks Voucher: '.$v['voucher_code'].' Package: '.$v['package_name'].' Speed: '.$v['speed_down'].'/'.$v['speed_up']); ?>">WhatsApp</a>
<a class="red btn" onclick="return confirm('Delete this voucher?')" href="smart_vouchers.php?delete=<?php echo (int)$v["id"]; ?>">Delete</a>
</div>
</td>
</tr>
<?php endforeach; ?>

</table>
</div>
</div>

<?php if($view): ?>
<div class="modal">
<div class="modalbox">
<h2><?php echo htmlspecialchars($view["voucher_code"]); ?></h2>
<div class="detail">
<div><small>Package</small><h3><?php echo htmlspecialchars($view["package_name"]); ?></h3></div>
<div><small>Status</small><h3><?php echo htmlspecialchars($view["status"]); ?></h3></div>
<div><small>Speed</small><h3><?php echo htmlspecialchars($view["speed_down"]."/".$view["speed_up"]); ?></h3></div>
<div><small>Price</small><h3>KES <?php echo number_format((float)$view["price"]); ?></h3></div>
<div><small>Created</small><h3><?php echo htmlspecialchars($view["created_at"] ?? "-"); ?></h3></div>
<div><small>Expires</small><h3><?php echo htmlspecialchars($view["expires_at"] ?? "-"); ?></h3></div>
</div>
<br>
<a class="gray btn" href="smart_vouchers.php">Close</a>
<a class="blue btn" href="smart_vouchers.php?edit=<?php echo (int)$view["id"]; ?>">Edit</a>
</div>
</div>
<?php endif; ?>

</div>

<script>
function applyFilters(){
 const q=document.getElementById("searchBox").value.toLowerCase();
 const st=document.getElementById("statusFilter").value;
 const on=document.getElementById("onlineFilter").value;

 document.querySelectorAll("#voucherTable tr[data-voucher]").forEach(row=>{
   const text=row.innerText.toLowerCase();
   const status=row.dataset.status;
   const online=row.dataset.online;
   
const limitok = row.dataset.limitok !== "no";
row.style.display=(text.includes(q) && (!st || st===status) && (!on || on===online) && limitok) ? "" : "none";

 });
}
document.getElementById("searchBox").addEventListener("input",applyFilters);
document.getElementById("statusFilter").addEventListener("change",applyFilters);
document.getElementById("onlineFilter").addEventListener("change",applyFilters);

async function refreshVoucherOnline(){
 try{
   const r=await fetch("voucher_online_api.php?t="+Date.now(),{cache:"no-store"});
   const d=await r.json();
   const online=d.online || [];

   document.querySelectorAll("#voucherTable tr[data-voucher]").forEach(row=>{
     const code=row.dataset.voucher;
     const el=row.querySelector(".online-status");

     if(online.includes(code)){
       el.innerText="ONLINE";
       el.className="badge online online-status";
       row.dataset.online="online";
     }else{
       el.innerText="OFFLINE";
       el.className="badge offline online-status";
       row.dataset.online="offline";
     }
   });
   applyFilters();
 }catch(e){}
}
setTimeout(refreshVoucherOnline,1000);
setInterval(refreshVoucherOnline,15000);
</script>

<script>

let showAllVoucherRows = false;

function applyVoucherLimit(){

document.querySelectorAll("#voucherTable tr[data-row]").forEach(row=>{

const num = parseInt(row.dataset.row || "0");

if(showAllVoucherRows){
row.dataset.limitok = "yes";
}else{
row.dataset.limitok = num <= 10 ? "yes" : "no";
}

});

applyFilters();
}

document.getElementById("toggleRowsBtn").addEventListener("click", ()=>{

showAllVoucherRows = !showAllVoucherRows;

document.getElementById("toggleRowsBtn").innerText =
showAllVoucherRows
? "Show Latest 10"
: "Show All Vouchers";

applyVoucherLimit();

});

setTimeout(applyVoucherLimit,300);

</script>



\n
<script id="HN_VOUCHER_ONLINE_COMPACT_SYNC">
document.addEventListener("DOMContentLoaded",()=>{

  async function syncVoucherOnline(){
    try{
      const r = await fetch("voucher_online_live_api.php?_="+Date.now(), {cache:"no-store"});
      const d = await r.json();
      if(!d.success) return;

      document.querySelectorAll("table tbody tr").forEach(row=>{
        const codeCell = row.querySelector("td");
        if(!codeCell) return;

        const code = codeCell.innerText.replace(/\s+/g,"").trim();
        if(!(code in d.items)) return;

        const onlineCell = row.children[6];
        if(!onlineCell) return;

        onlineCell.innerHTML = d.items[code]
          ? '<span class="sv-online-pill">ONLINE</span>'
          : '<span class="sv-offline-pill">OFFLINE</span>';
      });

    }catch(e){}
  }

  syncVoucherOnline();
  setInterval(syncVoucherOnline,10000);
});
</script>
\n</body>
</html>

<style>
#voucherTable th:nth-child(3),
#voucherTable td:nth-child(3),
#voucherTable th:nth-child(4),
#voucherTable td:nth-child(4),
#voucherTable th:nth-child(5),
#voucherTable td:nth-child(5){
  width:80px;
}
#voucherTable th:nth-child(8),
#voucherTable td:nth-child(8),
#voucherTable th:nth-child(9),
#voucherTable td:nth-child(9),
#voucherTable th:nth-child(10),
#voucherTable td:nth-child(10){
  font-size:12px;
  color:#cbd5e1;
}
#voucherTable th:nth-child(12),
#voucherTable td:nth-child(12){
  width:190px;
}
</style>
