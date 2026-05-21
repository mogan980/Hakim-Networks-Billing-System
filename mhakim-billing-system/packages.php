<?php
require_once __DIR__ . "/config/database.php";

if(isset($_GET["delete"])){
    $stmt=$pdo->prepare("DELETE FROM packages WHERE id=?");
    $stmt->execute([(int)$_GET["delete"]]);
    header("Location: packages.php?deleted=1");
    exit;
}

if(isset($_POST["save_package"])){
    $id=(int)($_POST["id"] ?? 0);

    if($id>0){
        $stmt=$pdo->prepare("UPDATE packages SET name=?, duration_hours=?, speed_down=?, speed_up=?, price=?, status=? WHERE id=?");
        $stmt->execute([$_POST["name"],$_POST["duration_hours"],$_POST["speed_down"],$_POST["speed_up"],$_POST["price"],$_POST["status"],$id]);
    }else{
        $stmt=$pdo->prepare("INSERT INTO packages(name,duration_hours,speed_down,speed_up,price,status) VALUES(?,?,?,?,?,?)");
        $stmt->execute([$_POST["name"],$_POST["duration_hours"],$_POST["speed_down"],$_POST["speed_up"],$_POST["price"],$_POST["status"]]);
    }

    header("Location: packages.php?saved=1");
    exit;
}

$edit=null;
if(isset($_GET["edit"])){
    $stmt=$pdo->prepare("SELECT * FROM packages WHERE id=?");
    $stmt->execute([(int)$_GET["edit"]]);
    $edit=$stmt->fetch(PDO::FETCH_ASSOC);
}

$view=null;
if(isset($_GET["view"])){
    $stmt=$pdo->prepare("SELECT * FROM packages WHERE id=?");
    $stmt->execute([(int)$_GET["view"]]);
    $view=$stmt->fetch(PDO::FETCH_ASSOC);
}

$packages=$pdo->query("SELECT * FROM packages ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$total=count($packages);
$active=count(array_filter($packages,fn($p)=>$p["status"]==="active"));
$avgPrice=$total ? array_sum(array_column($packages,"price"))/$total : 0;
?>
<!DOCTYPE html>
<html>
<head>
<title>Packages</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial;background:#071018;color:#e2e8f0}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:240px;background:#020617;padding:24px}
.sidebar h2{color:#22c55e;margin:0}
.sidebar small{color:#94a3b8}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;border-radius:14px;margin:9px 0;font-weight:800}
.sidebar a.active,.sidebar a:hover{background:#16a34a;color:#052e16}
.main{margin-left:260px;padding:32px}
.top{display:flex;justify-content:space-between;align-items:center;position:relative;z-index:5}
.btn{border:0;padding:11px 15px;border-radius:12px;font-weight:900;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;position:relative;z-index:20;pointer-events:auto}
.green{background:#16a34a;color:white;transition:.25s}.green:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(34,197,94,.25)}.blue{background:#2563eb;color:white}.red{background:#dc2626;color:white}.gray{background:#64748b;color:white}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:24px 0}
.card{background:#0b1728;border:1px solid #1f2937;border-radius:22px;padding:22px;box-shadow:0 18px 45px rgba(0,0,0,.22)}
.big{font-size:34px;color:#22c55e;font-weight:900}
input,select{width:100%;padding:13px;border-radius:12px;border:1px solid #334155;background:#020617;color:white}
.filters{display:grid;grid-template-columns:2fr 1fr;gap:12px;margin:16px 0}
table{width:100%;border-collapse:collapse}
th{background:#020617;text-align:left;padding:14px}
td{padding:14px;border-bottom:1px solid #1f2937}
.badge{padding:7px 12px;border-radius:999px;font-weight:900;font-size:12px;background:#dcfce7;color:#166534}
.alert{padding:14px;border-radius:14px;margin:15px 0;font-weight:900}
.ok{background:#dcfce7;color:#166534}.bad{background:#fee2e2;color:#991b1b}
.formgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.modal{position:fixed;inset:0;background:rgba(2,6,23,.8);display:flex;align-items:center;justify-content:center;padding:20px}
.modalbox{background:#0b1728;border:1px solid #1f2937;border-radius:24px;padding:24px;max-width:520px;width:100%}
@media(max-width:900px){.sidebar{display:none}.main{margin-left:0}.stats,.formgrid,.filters{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<small>ISP Billing System</small><br><br>
<a href="noc_final_clean.php">📡 Live NOC</a>
<a href="dashboard.php">📊 Dashboard</a>
<a class="active" href="packages.php">📦 Packages</a>
<a href="smart_vouchers.php">🎟 Vouchers</a>
<a href="payments.php">💳 Payments</a>
<a href="routers.php">🛰 Routers</a>
</div>

<div class="main">
<div class="top">
<div>
<h1>Packages</h1>
<p>Manage hotspot packages, speed limits, prices and duration.</p>
</div>
<a class="btn green" href="packages.php?add=1">+ Add Package</a>
</div>

<?php if(isset($_GET["saved"])): ?><div class="alert ok" id="alert">Package saved successfully.</div><?php endif; ?>
<?php if(isset($_GET["deleted"])): ?><div class="alert bad" id="alert">Package deleted successfully.</div><?php endif; ?>

<div class="stats">
<div class="card"><small>Total Packages</small><div class="big"><?php echo $total; ?></div></div>
<div class="card"><small>Active Packages</small><div class="big"><?php echo $active; ?></div></div>
<div class="card"><small>Average Price</small><div class="big">Ksh <?php echo number_format($avgPrice); ?></div></div>
<div class="card"><small>Sync Status</small><div class="big">Live</div></div>
</div>

<?php if(isset($_GET["add"]) || $edit): ?>
<div class="card">
<h2><?php echo $edit ? "Edit Package" : "Add Package"; ?></h2>
<form method="POST">
<input type="hidden" name="id" value="<?php echo htmlspecialchars($edit["id"] ?? ""); ?>">
<div class="formgrid">
<input name="name" placeholder="Package name" value="<?php echo htmlspecialchars($edit["name"] ?? ""); ?>" required>
<input name="duration_hours" type="number" step="0.01" placeholder="Duration hours" value="<?php echo htmlspecialchars($edit["duration_hours"] ?? "1"); ?>" required>
<input name="price" type="number" step="0.01" placeholder="Price" value="<?php echo htmlspecialchars($edit["price"] ?? "10"); ?>" required>
<input name="speed_down" placeholder="Download e.g. 8M" value="<?php echo htmlspecialchars($edit["speed_down"] ?? "8M"); ?>" required>
<input name="speed_up" placeholder="Upload e.g. 2M" value="<?php echo htmlspecialchars($edit["speed_up"] ?? "2M"); ?>" required>
<select name="status">
<option value="active" <?php if(($edit["status"] ?? "")==="active") echo "selected"; ?>>active</option>
<option value="inactive" <?php if(($edit["status"] ?? "")==="inactive") echo "selected"; ?>>inactive</option>
</select>
</div><br>
<button class="btn green" name="save_package">Save Package</button>
<a class="btn gray" href="packages.php">Cancel</a>
</form>
</div>
<?php endif; ?>

<div class="card">
<h2>Package List</h2>
<div class="filters">
<input id="search" placeholder="Search packages...">
<select id="status">
<option value="">All Status</option>
<option value="active">Active</option>
<option value="inactive">Inactive</option>
</select>
</div>

<table id="tbl">
<tr><th>ID</th><th>Package</th><th>Duration</th><th>Download</th><th>Upload</th><th>Price</th><th>Status</th><th>Actions</th></tr>
<?php foreach($packages as $p): ?>
<tr data-status="<?php echo htmlspecialchars($p["status"]); ?>">
<td><?php echo (int)$p["id"]; ?></td>
<td><b><?php echo htmlspecialchars($p["name"]); ?></b></td>
<td><?php echo htmlspecialchars($p["duration_hours"]); ?> hrs</td>
<td><?php echo htmlspecialchars($p["speed_down"]); ?></td>
<td><?php echo htmlspecialchars($p["speed_up"]); ?></td>
<td><b>Ksh <?php echo number_format((float)$p["price"]); ?></b></td>
<td><span class="badge"><?php echo htmlspecialchars($p["status"]); ?></span></td>
<td>
<a class="btn gray" href="packages.php?view=<?php echo (int)$p["id"]; ?>">View</a>
<a class="btn blue" href="packages.php?edit=<?php echo (int)$p["id"]; ?>">Edit</a>
<a class="btn red" onclick="return confirm('Delete package?')" href="packages.php?delete=<?php echo (int)$p["id"]; ?>">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php if($view): ?>
<div class="modal">
<div class="modalbox">
<h2><?php echo htmlspecialchars($view["name"]); ?></h2>
<p><b>Price:</b> Ksh <?php echo number_format((float)$view["price"]); ?></p>
<p><b>Speed:</b> <?php echo htmlspecialchars($view["speed_down"]."/".$view["speed_up"]); ?></p>
<p><b>Duration:</b> <?php echo htmlspecialchars($view["duration_hours"]); ?> hrs</p>
<p><b>Status:</b> <?php echo htmlspecialchars($view["status"]); ?></p>
<a class="btn blue" href="packages.php?edit=<?php echo (int)$view["id"]; ?>">Edit</a>
<a class="btn gray" href="packages.php">Close</a>
</div>
</div>
<?php endif; ?>

</div>

<script>
setTimeout(()=>{let a=document.getElementById("alert"); if(a)a.style.display="none";},3000);

function filter(){
 const q=document.getElementById("search").value.toLowerCase();
 const s=document.getElementById("status").value;
 document.querySelectorAll("#tbl tr[data-status]").forEach(r=>{
  r.style.display=(r.innerText.toLowerCase().includes(q) && (!s || r.dataset.status===s)) ? "" : "none";
 });
}
document.getElementById("search").addEventListener("input",filter);
document.getElementById("status").addEventListener("change",filter);
</script>

</body>
</html>
