<?php
require_once "config/database.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user["password"] === md5($password)) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        header("Location: noc_final_clean.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login - M.Hakim</title>
<style>
body{margin:0;font-family:Arial;background:linear-gradient(135deg,#020617,#052e2b);height:100vh;display:flex;align-items:center;justify-content:center}
.login-box{width:360px;background:white;padding:28px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.35)}
h1{margin-top:0;color:#064e3b}
input{width:100%;padding:13px;margin:10px 0;border:1px solid #cbd5e1;border-radius:12px}
button{width:100%;padding:13px;background:#16a34a;color:white;border:0;border-radius:12px;font-weight:bold}
.error{background:#fee2e2;color:#991b1b;padding:10px;border-radius:10px}
</style>
</head>
<body>
<div class="login-box">
<h1>M.Hakim Login</h1>
<p>Admin access only</p>

<?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<form method="POST">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
</div>
</body>
</html>
