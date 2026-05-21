<?php
require_once __DIR__ . "/config/database.php";

$pdo->exec("
CREATE TABLE IF NOT EXISTS smart_vouchers (
 id INT AUTO_INCREMENT PRIMARY KEY,
 voucher_code VARCHAR(50) UNIQUE NOT NULL,
 package_name VARCHAR(100),
 speed_down VARCHAR(30) DEFAULT '8M',
 speed_up VARCHAR(30) DEFAULT '2M',
 duration_hours INT DEFAULT 24,
 price DECIMAL(10,2) DEFAULT 0,
 status ENUM('unused','used','expired') DEFAULT 'unused',
 used_by VARCHAR(100) NULL,
 used_at DATETIME NULL,
 expires_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

function makeCode(){
    return "MH-" . strtoupper(substr(bin2hex(random_bytes(4)),0,8));
}

if(isset($_POST["generate"])){
    $qty = max(1, (int)($_POST["qty"] ?? 1));

    for($i=0; $i<$qty; $i++){
        do {
            $code = makeCode();
            $check = $pdo->prepare("SELECT id FROM smart_vouchers WHERE voucher_code=?");
            $check->execute([$code]);
        } while($check->fetch());

        $stmt = $pdo->prepare("
            INSERT INTO smart_vouchers
            (voucher_code, package_name, speed_down, speed_up, duration_hours, price, status)
            VALUES (?,?,?,?,?,?,'unused')
        ");
        $stmt->execute([
            $code,
            $_POST["package_name"],
            $_POST["speed_down"],
            $_POST["speed_up"],
            (int)$_POST["duration_hours"],
            (float)$_POST["price"]
        ]);
    }

    header("Location: smart_vouchers.php?generated=1");
    exit;
}

if(isset($_GET["delete"])){
    $stmt = $pdo->prepare("DELETE FROM smart_vouchers WHERE id=?");
    $stmt->execute([$_GET["delete"]]);
    header("Location: smart_vouchers.php");
    exit;
}

if(isset($_GET["expire"])){
    $stmt = $pdo->prepare("UPDATE smart_vouchers SET status='expired' WHERE id=?");
    $stmt->execute([$_GET["expire"]]);
    header("Location: smart_vouchers.php");
    exit;
}

$vouchers = $pdo->query("SELECT * FROM smart_vouchers ORDER BY created_at DESC, id DESC LIMIT 300")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Smart Vouchers</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;font-family:Arial;background:#eaf0f5;color:#020617}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020b1a;color:white;padding:20px}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;border-radius:10px;margin:6px 0;font-weight:800}
.sidebar a:hover,.active{background:#064e3b}
.main{margin-left:260px;padding:35px}
.card{background:white;border-radius:22px;padding:24px;margin-bottom:24px;box-shadow:0 18px 45px rgba(15,23,42,.09)}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
input{padding:13px;border:1px solid #cbd5e1;border-radius:12px;width:100%;box-sizing:border-box}
button,.btn{border:0;padding:11px 15px;border-radius:12px;font-weight:900;text-decoration:none;display:inline-block}
.green{background:#16a34a;color:white}.red{background:#dc2626;color:white}.blue{background:#2563eb;color:white}.gray{background:#64748b;color:white}
table{width:100%;border-collapse:collapse}
th{background:#020617;color:white;text-align:left;padding:13px}
td{padding:13px;border-bottom:1px solid #e5e7eb}
.badge{padding:6px 12px;border-radius:999px;font-weight:900;font-size:12px}
.unused{background:#dcfce7;color:#166534}.used{background:#dbeafe;color:#1d4ed8}.expired{background:#fee2e2;color:#991b1b}
.voucher-code{font-weight:900;color:#064e3b}
@media(max-width:900px){.main{margin-left:0}.sidebar{display:none}.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="sidebar">
<h2>M.Hakim</h2>
<a href="noc_final_clean.php">Live NOC</a>
<a href="modules.php">Modules</a>
<a class="active" href="smart_vouchers.php">Smart Vouchers</a>
<a href="hotspot_manager.php">Hotspot</a>
<a href="queue_control.php">Queues</a>
<a href="payments.php">Payments</a>
</div>

<div class="main">
<h1>Smart Voucher System</h1>
<p>Generate secure MikroTik hotspot vouchers.</p>

<?php if(isset($_GET["generated"])): ?>
<div class="card" style="color:#16a34a;font-weight:900;">Vouchers generated and saved successfully.</div>
<?php endif; ?>

<div class="card">
<h2>Generate Vouchers</h2>
<form method="POST">
<div class="grid">
<input name="package_name" placeholder="Package Name" value="24 Hours" required>
<input name="speed_down" placeholder="Download Speed e.g. 8M" value="8M" required>
<input name="speed_up" placeholder="Upload Speed e.g. 2M" value="2M" required>
<input name="duration_hours" type="number" placeholder="Duration Hours" value="24" required>
<input name="price" type="number" placeholder="Price KES" value="50" required>
<input name="qty" type="number" placeholder="Quantity" value="5" required>
</div>
<br>
<button class="green" name="generate">Generate Vouchers</button>
<a class="blue btn" href="print_vouchers.php" target="_blank">Print Vouchers</a>
</form>
</div>

<div class="card">
<h2>Voucher List</h2>
<table>
<tr><th>Code</th><th>Package</th><th>Speed</th><th>Duration</th><th>Price</th><th>Status</th><th>Actions</th></tr>
<?php foreach($vouchers as $v): ?>
<tr>
<td class="voucher-code"><?php echo htmlspecialchars($v["voucher_code"]); ?></td>
<td><?php echo htmlspecialchars($v["package_name"]); ?></td>
<td><?php echo htmlspecialchars($v["speed_down"]."/".$v["speed_up"]); ?></td>
<td><?php echo (int)$v["duration_hours"]; ?> hrs</td>
<td>KES <?php echo number_format((float)$v["price"]); ?></td>
<td><span class="badge <?php echo $v["status"]; ?>"><?php echo strtoupper($v["status"]); ?></span></td>
<td>
<a class="gray btn" target="_blank" href="single_voucher.php?id=<?php echo $v["id"]; ?>">View</a>
<a class="blue btn" href="?expire=<?php echo $v["id"]; ?>">Expire</a>
<a class="red btn" onclick="return confirm('Delete voucher?')" href="?delete=<?php echo $v["id"]; ?>">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
</body>
</html>
