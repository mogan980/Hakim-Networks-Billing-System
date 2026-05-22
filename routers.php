<?php
require_once __DIR__ . "/config/database.php";

function syncDefaultRouter($pdo){
    $router = $pdo->query("SELECT * FROM routers WHERE status='active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if($router){
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS mikrotik_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                router_ip VARCHAR(50),
                router_username VARCHAR(100),
                router_password VARCHAR(255),
                api_port INT
            )
        ");

        $check = $pdo->query("SELECT id FROM mikrotik_settings LIMIT 1")->fetch();

        if($check){
            $stmt = $pdo->prepare("
                UPDATE mikrotik_settings 
                SET router_ip=?, router_username=?, router_password=?, api_port=?
                WHERE id=?
            ");
            $stmt->execute([
                $router["router_ip"],
                $router["router_username"],
                $router["router_password"],
                $router["api_port"],
                $check["id"]
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO mikrotik_settings 
                (router_ip, router_username, router_password, api_port)
                VALUES (?,?,?,?)
            ");
            $stmt->execute([
                $router["router_ip"],
                $router["router_username"],
                $router["router_password"],
                $router["api_port"]
            ]);
        }
    }
}

if(isset($_POST["add_router"])){
    $stmt = $pdo->prepare("
        INSERT INTO routers
        (router_name, router_ip, router_username, router_password, api_port, location, status)
        VALUES (?,?,?,?,?,?, 'active')
    ");
    $stmt->execute([
        $_POST["router_name"],
        $_POST["router_ip"],
        $_POST["router_username"],
        $_POST["router_password"],
        $_POST["api_port"],
        $_POST["location"]
    ]);

    syncDefaultRouter($pdo);
    header("Location: routers.php");
    exit;
}

if(isset($_POST["update_router"])){
    $stmt = $pdo->prepare("
        UPDATE routers SET
        router_name=?,
        router_ip=?,
        router_username=?,
        router_password=?,
        api_port=?,
        location=?,
        status=?
        WHERE id=?
    ");
    $stmt->execute([
        $_POST["router_name"],
        $_POST["router_ip"],
        $_POST["router_username"],
        $_POST["router_password"],
        $_POST["api_port"],
        $_POST["location"],
        $_POST["status"],
        $_POST["id"]
    ]);

    syncDefaultRouter($pdo);
    header("Location: routers.php");
    exit;
}

if(isset($_GET["delete"])){
    $stmt = $pdo->prepare("DELETE FROM routers WHERE id=?");
    $stmt->execute([$_GET["delete"]]);

    syncDefaultRouter($pdo);
    header("Location: routers.php");
    exit;
}

if(isset($_GET["set_active"])){
    $pdo->prepare("UPDATE routers SET status='active' WHERE id=?")->execute([$_GET["set_active"]]);
    syncDefaultRouter($pdo);
    header("Location: routers.php");
    exit;
}

$editRouter = null;
if(isset($_GET["edit"])){
    $stmt = $pdo->prepare("SELECT * FROM routers WHERE id=?");
    $stmt->execute([$_GET["edit"]]);
    $editRouter = $stmt->fetch(PDO::FETCH_ASSOC);
}

$routers = $pdo->query("SELECT * FROM routers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Routers Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;font-family:Arial;background:#eaf0f5;color:#020617}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020b1a;color:white;padding:20px}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;border-radius:10px;margin:6px 0}
.sidebar a.active,.sidebar a:hover{background:#064e3b}
.main{margin-left:260px;padding:35px}
.card{background:white;border-radius:18px;padding:22px;margin-bottom:24px}
input,select{padding:13px;border:1px solid #cbd5e1;border-radius:10px;width:100%;box-sizing:border-box}
.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
button,.btn{border:0;padding:11px 15px;border-radius:10px;font-weight:bold;text-decoration:none;display:inline-block}
.green{background:#16a34a;color:white}
.blue{background:#2563eb;color:white}
.red{background:#dc2626;color:white}
.gray{background:#64748b;color:white}
table{width:100%;border-collapse:collapse;background:white}
th{background:#020617;color:white;text-align:left;padding:13px}
td{padding:13px;border-bottom:1px solid #e5e7eb}
.badge{padding:6px 10px;border-radius:20px;font-weight:bold;font-size:12px}
.active-b{background:#dcfce7;color:#166534}
.offline-b{background:#fee2e2;color:#991b1b}
</style>
</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<a href="dashboard.php">Dashboard</a>
<a href="noc_final_clean.php">Live NOC</a>
<a href="noc.php">NOC Center</a>
<a class="active" href="routers.php">Routers</a>
<a href="clients.php">Clients</a>
<a href="packages.php">Packages</a>
<a href="vouchers.php">Vouchers</a>
<a href="payments.php">Payments</a>
</div>

<div class="main">
<h1>Routers Management</h1>
<p>Manage MikroTik routers/sites from one billing system.</p>

<div class="card">
<h2><?php echo $editRouter ? "Edit Router" : "Add Router"; ?></h2>

<form method="POST">
<?php if($editRouter): ?>
<input type="hidden" name="id" value="<?php echo $editRouter["id"]; ?>">
<?php endif; ?>

<div class="grid">
<input name="router_name" placeholder="Router Name" required value="<?php echo htmlspecialchars($editRouter["router_name"] ?? ""); ?>">
<input name="router_ip" placeholder="Router IP e.g. 192.168.88.1" required value="<?php echo htmlspecialchars($editRouter["router_ip"] ?? "192.168.88.1"); ?>">
<input name="router_username" placeholder="API Username" required value="<?php echo htmlspecialchars($editRouter["router_username"] ?? "mhakimapi"); ?>">
<input name="router_password" placeholder="API Password" required value="<?php echo htmlspecialchars($editRouter["router_password"] ?? "12345678"); ?>">
<input name="api_port" placeholder="API Port" required value="<?php echo htmlspecialchars($editRouter["api_port"] ?? "8728"); ?>">
<input name="location" placeholder="Location e.g. Main Site" value="<?php echo htmlspecialchars($editRouter["location"] ?? ""); ?>">

<?php if($editRouter): ?>
<select name="status">
<option value="active" <?php if($editRouter["status"]=="active") echo "selected"; ?>>active</option>
<option value="inactive" <?php if($editRouter["status"]=="inactive") echo "selected"; ?>>inactive</option>
</select>
<?php endif; ?>
</div>

<br>

<?php if($editRouter): ?>
<button class="blue" name="update_router">Update Router</button>
<a class="gray btn" href="routers.php">Cancel</a>
<?php else: ?>
<button class="green" name="add_router">Add Router</button>
<?php endif; ?>
</form>
</div>

<div class="card">
<h2>Registered Routers</h2>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>IP</th>
<th>Username</th>
<th>Port</th>
<th>Location</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php foreach($routers as $r): ?>
<tr>
<td><?php echo $r["id"]; ?></td>
<td><?php echo htmlspecialchars($r["router_name"]); ?></td>
<td><?php echo htmlspecialchars($r["router_ip"]); ?></td>
<td><?php echo htmlspecialchars($r["router_username"]); ?></td>
<td><?php echo htmlspecialchars($r["api_port"]); ?></td>
<td><?php echo htmlspecialchars($r["location"]); ?></td>
<td>
<span class="badge <?php echo $r["status"]=="active" ? "active-b" : "offline-b"; ?>">
<?php echo htmlspecialchars($r["status"]); ?>
</span>
</td>
<td>
<a class="blue btn" href="?edit=<?php echo $r["id"]; ?>">Edit</a>
<a class="green btn" href="?set_active=<?php echo $r["id"]; ?>">Set Active</a>
<a class="red btn" onclick="return confirm('Delete router?')" href="?delete=<?php echo $r["id"]; ?>">Delete</a>
</td>
</tr>
<?php endforeach; ?>

</table>
</div>

</div>
<script src="mikrotik_live_sync.js"></script>
</body>
</html>
