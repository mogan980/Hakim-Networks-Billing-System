<?php
session_start();
require_once "config/database.php";

$error = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("
        SELECT tenant_users.*, companies.status AS company_status
        FROM tenant_users
        LEFT JOIN companies ON companies.id = tenant_users.company_id
        WHERE tenant_users.email=?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user["password"])){
        $_SESSION["tenant_user_id"] = $user["id"];
        $_SESSION["tenant_company_id"] = $user["company_id"];
        $_SESSION["tenant_role"] = $user["role"];

        if($user["role"] !== "super_admin" && $user["company_status"] !== "active"){
            header("Location: subscription_payment.php?company_id=".$user["company_id"]);
            exit;
        }

        header("Location: noc_final_clean.php");
        exit;
    }else{
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login - Hakim Networks</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;font-family:Arial;background:linear-gradient(135deg,#020617,#052e2b,#071827);color:white;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:18px}
.card{width:100%;max-width:430px;background:rgba(15,23,42,.9);border:1px solid rgba(148,163,184,.15);border-radius:26px;padding:30px;box-shadow:0 30px 80px rgba(0,0,0,.45)}
h1{color:#22c55e;margin-top:0}
input{width:100%;padding:14px;border-radius:14px;border:1px solid #334155;background:#020617;color:white;margin:9px 0;box-sizing:border-box}
button{width:100%;padding:15px;border:0;border-radius:15px;background:#22c55e;color:#052e16;font-weight:900;margin-top:12px}
.error{background:#450a0a;color:#fecaca;padding:12px;border-radius:12px;margin-bottom:12px}
a{color:#86efac}
</style>
</head>
<body>
<div class="card">
<h1>Billing Login</h1>
<p>Access your Hakim Networks billing workspace.</p>

<?php if($error): ?>
<div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">
<input type="email" name="email" placeholder="Email Address" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>

<p>No account? <a href="register.php">Create account</a></p>
</div>
</body>
</html>
