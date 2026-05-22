<?php
require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $company = trim($_POST["company_name"] ?? "");
    $owner = trim($_POST["owner_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!$company || !$owner || !$phone || !$email || !$password) {
        $message = "All fields are required.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO companies(company_name, owner_name, phone, email, status)
                VALUES(?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$company, $owner, $phone, $email]);

            $companyId = $pdo->lastInsertId();

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $user = $pdo->prepare("
                INSERT INTO tenant_users(company_id, full_name, email, phone, password, role, status)
                VALUES(?, ?, ?, ?, ?, 'tenant_admin', 'pending')
            ");
            $user->execute([$companyId, $owner, $email, $phone, $hash]);

            $sub = $pdo->prepare("
                INSERT INTO subscriptions(company_id, amount, status)
                VALUES(?, 1000, 'pending')
            ");
            $sub->execute([$companyId]);

            $pdo->commit();

            header("Location: subscription_payment.php?company_id=" . urlencode($companyId));
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $message = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Create Account - Hakim Networks</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;font-family:Arial;background:linear-gradient(135deg,#020617,#052e2b,#064e3b);color:white;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:18px}
.card{width:100%;max-width:480px;background:rgba(15,23,42,.85);border:1px solid rgba(148,163,184,.2);border-radius:24px;padding:28px;box-shadow:0 25px 70px rgba(0,0,0,.4)}
h1{margin-top:0;color:#22c55e}
input{width:100%;padding:14px;border-radius:13px;border:1px solid #334155;background:#020617;color:white;margin:9px 0;box-sizing:border-box}
button{width:100%;padding:14px;border:0;border-radius:14px;background:#22c55e;color:#052e16;font-weight:900;margin-top:12px}
.error{background:#450a0a;color:#fecaca;padding:12px;border-radius:12px;margin-bottom:12px}
a{color:#86efac}
</style>
</head>
<body>
<div class="card">
<h1>Create Billing Account</h1>
<p>Register your ISP/hotspot business. Monthly access: <b>KES 1,000</b></p>

<?php if($message): ?>
<div class="error"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<form method="POST">
<input name="company_name" placeholder="Business / Company Name" required>
<input name="owner_name" placeholder="Owner Full Name" required>
<input name="phone" placeholder="Phone e.g. 0704467699" required>
<input type="email" name="email" placeholder="Email Address" required>
<input type="password" name="password" placeholder="Create Password" required>
<button type="submit">Create Account & Pay KES 1,000</button>
</form>

<p>Already registered? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
