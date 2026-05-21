<?php
require_once __DIR__ . '/role_guard.php';
requireSuperAdmin();
require_once "auth.php";
requireRole(["super_admin"]);

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["create_user"])) {
        $stmt = $pdo->prepare("
            INSERT INTO users(full_name, username, password, role)
            VALUES(?,?,MD5(?),?)
        ");
        $stmt->execute([
            trim($_POST["full_name"]),
            trim($_POST["username"]),
            trim($_POST["password"]),
            trim($_POST["role"])
        ]);

        $message = "User created successfully.";
    }

    if (isset($_POST["delete_user"])) {
        $id = (int)$_POST["user_id"];

        if ($id == ($_SESSION["user_id"] ?? 0)) {
            $message = "You cannot delete your own account.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$id]);
            $message = "User deleted successfully.";
        }
    }

    if (isset($_POST["reset_password"])) {
        $id = (int)$_POST["user_id"];
        $newPassword = trim($_POST["new_password"]);

        $stmt = $pdo->prepare("UPDATE users SET password=MD5(?) WHERE id=?");
        $stmt->execute([$newPassword, $id]);

        $message = "Password reset successfully.";
    }

    if (isset($_POST["change_role"])) {
        $id = (int)$_POST["user_id"];
        $role = trim($_POST["role"]);

        $stmt = $pdo->prepare("UPDATE users SET role=? WHERE id=?");
        $stmt->execute([$role, $id]);

        $message = "User role updated successfully.";
    }
}
    $users = $pdo->query("
    SELECT id, full_name, username, role, isp_id, created_at
    FROM users
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>User Management</title>
<link rel="stylesheet" href="assets/pro-sidebar.css">
<style>
body{font-family:Arial;background:#f1f5f9;margin:0;color:#0f172a}
.main{margin-left:280px;padding:25px}
.card{background:white;padding:22px;border-radius:18px;margin-bottom:20px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
input,select{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:10px;margin-bottom:12px}
button{padding:12px 18px;border:0;border-radius:10px;background:#16a34a;color:white;font-weight:bold}
table{width:100%;border-collapse:collapse}
th{background:#020617;color:white;padding:12px;text-align:left}
td{padding:12px;border-bottom:1px solid #e5e7eb}
.success{background:#dcfce7;color:#166534;padding:12px;border-radius:10px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.badge{padding:6px 10px;border-radius:999px;background:#dcfce7;color:#166534;font-size:12px;font-weight:bold}
</style>
</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<p>Super Admin</p>
<a href="noc_final_clean.php">Dashboard</a>
<a href="noc.php">NOC Center</a>
<a href="routers.php">Routers</a>
<a href="clients.php">Clients</a>
<a href="pppoe.php">PPPoE</a>
<a href="payments.php">Payments</a>
<a href="analytics.php">Analytics</a>
<a href="health_check.php">Health Check</a>
<a href="backups.php">Backups</a>
<a class="active" href="users.php">Users</a>
<a href="logout.php">Logout</a>
</div>

<div class="main">
<h1>User Management</h1>
<p>Create admins and operators for your billing system.</p>

<?php if($message): ?>
<div class="success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="card">
<h2>Add New User</h2>

<form method="POST">
<input type="hidden" name="create_user" value="1">
<div class="grid">
<input name="full_name" placeholder="Full Name" required>
<input name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>

<select name="role" required>
<option value="admin">Admin</option>
<option value="operator">Operator</option>
<option value="super_admin">Super Admin</option>
</select>
</div>

<button type="submit">Create User</button>
</form>
</div>

<div class="card">
<h2>System Users</h2>

<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Username</th>
<th>Role</th>
<th>ISP ID</th>
<th>Created</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($users as $u): ?>
<tr>
<td><?php echo htmlspecialchars($u["id"]); ?></td>
<td><?php echo htmlspecialchars($u["full_name"]); ?></td>
<td><?php echo htmlspecialchars($u["username"]); ?></td>
<td><span class="badge"><?php echo htmlspecialchars($u["role"]); ?></span></td>
<td><?php echo htmlspecialchars($u["isp_id"] ?? "-"); ?></td>
<td><?php echo htmlspecialchars($u["created_at"]); ?></td>
<td>
<form method="POST" style="display:inline-block;">
    <input type="hidden" name="user_id" value="<?php echo $u["id"]; ?>">
    <select name="role" style="width:auto;padding:7px;">
        <option value="super_admin" <?php echo $u["role"]==="super_admin"?"selected":""; ?>>Super Admin</option>
        <option value="admin" <?php echo $u["role"]==="admin"?"selected":""; ?>>Admin</option>
        <option value="operator" <?php echo $u["role"]==="operator"?"selected":""; ?>>Operator</option>
    </select>
    <button name="change_role" value="1" style="background:#2563eb;">Update</button>
</form>

<form method="POST" style="display:inline-block;margin-left:6px;">
    <input type="hidden" name="user_id" value="<?php echo $u["id"]; ?>">
    <input type="text" name="new_password" placeholder="New password" required style="width:130px;padding:7px;">
    <button name="reset_password" value="1" style="background:#f59e0b;">Reset</button>
</form>

<form method="POST" style="display:inline-block;margin-left:6px;" onsubmit="return confirm('Delete this user?');">
    <input type="hidden" name="user_id" value="<?php echo $u["id"]; ?>">
    <button name="delete_user" value="1" style="background:#dc2626;">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</div>
</body>
</html>
