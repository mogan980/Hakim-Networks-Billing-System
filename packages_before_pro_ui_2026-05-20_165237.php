<?php
require_once __DIR__ . "/config/database.php";

$pdo->exec("
CREATE TABLE IF NOT EXISTS packages (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 duration_hours DECIMAL(10,2) NOT NULL DEFAULT 1,
 speed_down VARCHAR(30) NOT NULL DEFAULT '8M',
 speed_up VARCHAR(30) NOT NULL DEFAULT '2M',
 price DECIMAL(10,2) NOT NULL DEFAULT 0,
 status ENUM('active','inactive') DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

if(isset($_POST["save_package"])){
    $id = (int)($_POST["id"] ?? 0);

    if($id > 0){
        $stmt = $pdo->prepare("
            UPDATE packages
            SET name=?, duration_hours=?, speed_down=?, speed_up=?, price=?, status=?
            WHERE id=?
        ");
        $stmt->execute([
            $_POST["name"],
            $_POST["duration_hours"],
            $_POST["speed_down"],
            $_POST["speed_up"],
            $_POST["price"],
            $_POST["status"],
            $id
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO packages
            (name,duration_hours,speed_down,speed_up,price,status)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->execute([
            $_POST["name"],
            $_POST["duration_hours"],
            $_POST["speed_down"],
            $_POST["speed_up"],
            $_POST["price"],
            $_POST["status"]
        ]);
    }

    header("Location: packages.php?saved=1");
    exit;
}

if(isset($_GET["delete"])){
    $id = (int)$_GET["delete"];
    $stmt = $pdo->prepare("DELETE FROM packages WHERE id=?");
    $stmt->execute([$id]);
    header("Location: packages.php?deleted=1");
    exit;
}

$edit = null;
if(isset($_GET["edit"])){
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id=?");
    $stmt->execute([(int)$_GET["edit"]]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$view = null;
if(isset($_GET["view"])){
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id=?");
    $stmt->execute([(int)$_GET["view"]]);
    $view = $stmt->fetch(PDO::FETCH_ASSOC);
}

$packages = $pdo->query("SELECT * FROM packages ORDER BY price ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Packages - Hakim Networks</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#eef3f7;color:#020617}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:240px;background:#020b1a;color:white;padding:22px}
.sidebar h2{margin:0 0 4px}
.sidebar small{color:#9ca3af}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;border-radius:12px;margin:7px 0;font-weight:800}
.sidebar a:hover,.sidebar a.active{background:#064e3b}
.main{margin-left:260px;padding:35px}
.top{display:flex;justify-content:space-between;align-items:center;gap:15px}
.card{background:white;border-radius:24px;padding:24px;margin-top:22px;box-shadow:0 18px 45px rgba(15,23,42,.08);border:1px solid #e5e7eb}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
input,select{width:100%;padding:14px;border:1px solid #cbd5e1;border-radius:14px}
button,.btn{border:0;padding:11px 15px;border-radius:12px;font-weight:900;text-decoration:none;display:inline-block;cursor:pointer}
.green{background:#16a34a;color:white}.blue{background:#2563eb;color:white}.red{background:#dc2626;color:white}.gray{background:#64748b;color:white}.orange{background:#f97316;color:white}
table{width:100%;border-collapse:collapse;margin-top:10px}
th{background:#020617;color:white;text-align:left;padding:14px}
td{padding:14px;border-bottom:1px solid #e5e7eb}
.badge{padding:7px 13px;border-radius:999px;font-weight:900;font-size:12px}
.active-b{background:#dcfce7;color:#166534}.inactive-b{background:#fee2e2;color:#991b1b}
.alert{padding:14px;border-radius:14px;margin-top:18px;font-weight:900}
.ok{background:#dcfce7;color:#166534}.bad{background:#fee2e2;color:#991b1b}
.modal{position:fixed;inset:0;background:rgba(2,6,23,.7);display:flex;align-items:center;justify-content:center;padding:20px;z-index:9999}
.modalbox{background:white;border-radius:24px;padding:26px;max-width:520px;width:100%;box-shadow:0 30px 80px rgba(0,0,0,.35)}
.detail{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.detail div{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px}
@media(max-width:900px){.sidebar{display:none}.main{margin-left:0}.grid,.detail{grid-template-columns:1fr}.top{flex-direction:column;align-items:flex-start}}
</style>
</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<small>ISP Billing System</small>
<br><br>
<a href="noc_final_clean.php">📡 Live NOC</a>
<a href="dashboard.php">📊 Dashboard</a>
<a class="active" href="packages.php">📦 Packages</a>
<a href="smart_vouchers.php">🎟 Vouchers</a>
<a href="payments.php">💳 Payments</a>
<a href="routers.php">🛰 Routers</a>
<a href="modules.php">🧩 Modules</a>
</div>

<div class="main">
<div class="top">
 <div>
  <h1>Packages</h1>
  <p>Manage hotspot packages, speed limits, prices, and duration. These sync directly to the hotspot page.</p>
 </div>
 <a class="green btn" href="packages.php?add=1">+ Add Package</a>
</div>

<?php if(isset($_GET["saved"])): ?><div class="alert ok">Package saved successfully.</div><?php endif; ?>
<?php if(isset($_GET["deleted"])): ?><div class="alert bad">Package deleted successfully.</div><?php endif; ?>

<?php if(isset($_GET["add"]) || $edit): ?>
<div class="card">
<h2><?php echo $edit ? "Edit Package" : "Add New Package"; ?></h2>
<form method="POST">
<input type="hidden" name="id" value="<?php echo htmlspecialchars($edit["id"] ?? ""); ?>">
<div class="grid">
<input name="name" placeholder="Package name e.g. 24 Hours" value="<?php echo htmlspecialchars($edit["name"] ?? ""); ?>" required>
<input name="duration_hours" type="number" step="0.01" placeholder="Duration hours" value="<?php echo htmlspecialchars($edit["duration_hours"] ?? "24"); ?>" required>
<input name="price" type="number" step="0.01" placeholder="Price" value="<?php echo htmlspecialchars($edit["price"] ?? "50"); ?>" required>
<input name="speed_down" placeholder="Download speed e.g. 8M" value="<?php echo htmlspecialchars($edit["speed_down"] ?? "8M"); ?>" required>
<input name="speed_up" placeholder="Upload speed e.g. 2M" value="<?php echo htmlspecialchars($edit["speed_up"] ?? "2M"); ?>" required>
<select name="status">
<option value="active" <?php if(($edit["status"] ?? "")==="active") echo "selected"; ?>>active</option>
<option value="inactive" <?php if(($edit["status"] ?? "")==="inactive") echo "selected"; ?>>inactive</option>
</select>
</div>
<br>
<button class="green" name="save_package">Save Package</button>
<a class="gray btn" href="packages.php">Cancel</a>
</form>
</div>
<?php endif; ?>

<div class="card">
<h2>Package List</h2>
<table>
<tr>
<th>ID</th>
<th>Package</th>
<th>Duration</th>
<th>Download</th>
<th>Upload</th>
<th>Price</th>
<th>Status</th>
<th>Actions</th>
</tr>
<?php foreach($packages as $p): ?>
<tr>
<td><?php echo (int)$p["id"]; ?></td>
<td><b><?php echo htmlspecialchars($p["name"]); ?></b></td>
<td><?php echo htmlspecialchars($p["duration_hours"]); ?> hrs</td>
<td><?php echo htmlspecialchars($p["speed_down"]); ?></td>
<td><?php echo htmlspecialchars($p["speed_up"]); ?></td>
<td><b>Ksh <?php echo number_format((float)$p["price"]); ?></b></td>
<td>
<span class="badge <?php echo $p["status"]==="active" ? "active-b" : "inactive-b"; ?>">
<?php echo htmlspecialchars($p["status"]); ?>
</span>
</td>
<td>
<a class="gray btn" href="packages.php?view=<?php echo (int)$p["id"]; ?>">View</a>
<a class="blue btn" href="packages.php?edit=<?php echo (int)$p["id"]; ?>">Edit</a>
<a class="red btn" onclick="return confirm('Delete this package? It will disappear from hotspot page.')" href="packages.php?delete=<?php echo (int)$p["id"]; ?>">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php if($view): ?>
<div class="modal">
 <div class="modalbox">
  <h2><?php echo htmlspecialchars($view["name"]); ?></h2>
  <div class="detail">
   <div><small>Price</small><h3>Ksh <?php echo number_format((float)$view["price"]); ?></h3></div>
   <div><small>Duration</small><h3><?php echo htmlspecialchars($view["duration_hours"]); ?> hrs</h3></div>
   <div><small>Download</small><h3><?php echo htmlspecialchars($view["speed_down"]); ?></h3></div>
   <div><small>Upload</small><h3><?php echo htmlspecialchars($view["speed_up"]); ?></h3></div>
   <div><small>Status</small><h3><?php echo htmlspecialchars($view["status"]); ?></h3></div>
   <div><small>ID</small><h3><?php echo (int)$view["id"]; ?></h3></div>
  </div>
  <br>
  <a class="blue btn" href="packages.php?edit=<?php echo (int)$view["id"]; ?>">Edit Package</a>
  <a class="gray btn" href="packages.php">Close</a>
 </div>
</div>
<?php endif; ?>

</div>
</body>
</html>
