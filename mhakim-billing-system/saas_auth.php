<?php
ob_start();
session_start();
require_once "config/database.php";

date_default_timezone_set("Africa/Nairobi");

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mode = $_POST["mode"] ?? "";

    if ($mode === "login") {

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        $stmt = $pdo->prepare("SELECT * FROM tenant_users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user["password"])) {
            $error = "Invalid email or password.";
        } else {

            session_regenerate_id(true);

            $_SESSION["tenant_user_id"] = $user["id"];
            $_SESSION["tenant_role"] = $user["role"];
            $_SESSION["tenant_company_id"] = $user["company_id"];
            $_SESSION["tenant_name"] = $user["full_name"];

            // Supports old dashboard auth too
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["name"] = $user["full_name"];

            if ($user["role"] === "super_admin") {
                header("Location: /mhakim-billing-system/noc_final_clean.php");
                exit;
            }

            $sub = $pdo->prepare("
                SELECT id FROM subscriptions
                WHERE company_id=?
                AND status IN ('paid','active')
                AND expires_at > NOW()
                LIMIT 1
            ");
            $sub->execute([$user["company_id"]]);

            if ($sub->fetch()) {
                header("Location: /mhakim-billing-system/tenant_noc_final_clean.php");
                exit;
            }

            header("Location: /mhakim-billing-system/subscription_payment.php?company_id=" . urlencode($user["company_id"]));
            exit;
        }
    }

    if ($mode === "register") {

        $company = trim($_POST["company"] ?? "");
        $name = trim($_POST["name"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if (!$company || !$name || !$phone || !$email || !$password) {
            $error = "All fields are required.";
        } else {

            $check = $pdo->prepare("SELECT id FROM tenant_users WHERE email=? LIMIT 1");
            $check->execute([$email]);

            if ($check->fetch()) {
                $error = "Account already exists. Please login.";
            } else {

                $pdo->beginTransaction();

                $pdo->prepare("
                    INSERT INTO companies(company_name, owner_name, phone, email, status, created_at)
                    VALUES(?, ?, ?, ?, 'pending', NOW())
                ")->execute([$company, $name, $phone, $email]);

                $companyId = $pdo->lastInsertId();

                $pdo->prepare("
                    INSERT INTO tenant_users(company_id, full_name, email, phone, password, role, status, created_at)
                    VALUES(?, ?, ?, ?, ?, 'tenant_admin', 'active', NOW())
                ")->execute([
                    $companyId,
                    $name,
                    $email,
                    $phone,
                    password_hash($password, PASSWORD_DEFAULT)
                ]);

                $pdo->prepare("
                    INSERT INTO subscriptions(company_id, amount, status, created_at)
                    VALUES(?, 1000, 'pending', NOW())
                ")->execute([$companyId]);

                $pdo->commit();

                header("Location: /mhakim-billing-system/subscription_payment.php?company_id=" . urlencode($companyId));
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Hakim Networks Billing</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;font-family:Arial,sans-serif;background:linear-gradient(135deg,#020617,#052e2b,#071827);color:white;display:flex;align-items:center;justify-content:center;padding:20px}
.wrap{width:100%;max-width:1080px;display:grid;grid-template-columns:1.1fr .9fr;gap:24px}
.left,.right{background:rgba(15,23,42,.9);border:1px solid rgba(148,163,184,.18);border-radius:30px;padding:34px;box-shadow:0 30px 90px rgba(0,0,0,.45)}
.logo{width:64px;height:64px;border-radius:20px;background:linear-gradient(135deg,#22c55e,#86efac);color:#052e16;display:grid;place-items:center;font-size:32px;font-weight:900;margin-bottom:18px}
h1{font-size:38px;margin:0;color:#22c55e}
p{color:#cbd5e1;line-height:1.6}
.features{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:22px}
.feature{background:rgba(2,6,23,.6);border:1px solid rgba(148,163,184,.12);border-radius:18px;padding:16px;color:#cbd5e1}
.feature b{color:#22c55e}
.price{margin-top:20px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);border-radius:18px;padding:16px;text-align:center;color:#22c55e;font-size:28px;font-weight:900}
.tabs{display:flex;gap:10px;background:rgba(2,6,23,.55);padding:7px;border-radius:18px;margin-bottom:18px}
.tab{flex:1;text-align:center;padding:12px;border-radius:14px;cursor:pointer;font-weight:900}
.tab.active{background:#22c55e;color:#052e16}
.form{display:none}.form.active{display:block}
input{width:100%;padding:15px;border-radius:15px;border:1px solid rgba(148,163,184,.18);background:#020617;color:white;margin-bottom:12px}
button{width:100%;padding:15px;border:0;border-radius:15px;background:linear-gradient(135deg,#22c55e,#86efac);color:#052e16;font-weight:900;cursor:pointer}
.error{background:#450a0a;color:#fecaca;padding:12px;border-radius:14px;margin-bottom:14px}
@media(max-width:900px){.wrap{grid-template-columns:1fr}.features{grid-template-columns:1fr}h1{font-size:30px}}
</style>

<style>
/* ISP Professional Polish */
body{
    background:
        radial-gradient(circle at 15% 15%,rgba(34,197,94,.22),transparent 30%),
        radial-gradient(circle at 85% 80%,rgba(56,189,248,.18),transparent 32%),
        linear-gradient(135deg,#020617,#042f2e,#071827) !important;
}

.wrap{
    max-width:1120px !important;
    gap:26px !important;
}

.left,.right{
    background:rgba(15,23,42,.88) !important;
    border:1px solid rgba(34,197,94,.16) !important;
    box-shadow:0 35px 100px rgba(0,0,0,.45) !important;
    backdrop-filter:blur(20px) !important;
}

.logo{
    box-shadow:0 0 0 8px rgba(34,197,94,.08),0 22px 55px rgba(34,197,94,.32) !important;
}

h1{
    letter-spacing:-1.2px !important;
    line-height:1.05 !important;
}

.left p{
    font-size:15.5px !important;
    color:#dbeafe !important;
}

.feature{
    min-height:86px !important;
    transition:.25s ease !important;
}

.feature:hover{
    transform:translateY(-4px) !important;
    border-color:rgba(34,197,94,.35) !important;
    background:rgba(2,6,23,.78) !important;
}

.price{
    box-shadow:inset 0 0 30px rgba(34,197,94,.08) !important;
}

.right{
    display:flex !important;
    flex-direction:column !important;
    justify-content:center !important;
}

.tabs{
    box-shadow:inset 0 0 20px rgba(0,0,0,.25) !important;
}

input{
    height:46px !important;
}

button{
    height:48px !important;
    transition:.25s ease !important;
    box-shadow:0 18px 40px rgba(34,197,94,.2) !important;
}

button:hover{
    transform:translateY(-2px) !important;
    filter:brightness(1.06) !important;
}

.right:after{
    content:"Secure ISP Billing • MikroTik Ready • M-Pesa Enabled";
    display:block;
    text-align:center;
    margin-top:18px;
    color:#94a3b8;
    font-size:12px;
}

.left:after{
    content:"Built for hotspot resellers, PPPoE operators and small ISPs.";
    display:block;
    margin-top:18px;
    padding:14px 16px;
    border-radius:16px;
    background:rgba(2,6,23,.45);
    border:1px solid rgba(148,163,184,.12);
    color:#94a3b8;
    font-size:13px;
}
</style>

</head>
<body>

<div class="wrap">
    <div class="left">
        <div class="logo">H</div>
        <h1>Hakim Networks ISP Billing</h1>
        <p>Manage hotspot clients, vouchers, M-Pesa payments, MikroTik routers, analytics and expiry automation from one platform.</p>

        <div class="features">
            <div class="feature"><b>⚡ M-Pesa</b><br>Auto activation after payment.</div>
            <div class="feature"><b>🎟 Vouchers</b><br>Create and redeem hotspot vouchers.</div>
            <div class="feature"><b>📡 MikroTik</b><br>Router and hotspot automation.</div>
            <div class="feature"><b>📊 Dashboard</b><br>Revenue and client analytics.</div>
        </div>

        <div class="price">KES 1,000 / Month</div>
    </div>

    <div class="right">
        <?php if($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="showTab('login', this)">Login</div>
            <div class="tab" onclick="showTab('register', this)">Register</div>
        </div>

        <form method="POST" id="login" class="form active">
            <input type="hidden" name="mode" value="login">
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login to Dashboard</button>
        </form>

        <form method="POST" id="register" class="form">
            <input type="hidden" name="mode" value="register">
            <input type="text" name="company" placeholder="Company name" required>
            <input type="text" name="name" placeholder="Full name" required>
            <input type="text" name="phone" placeholder="Phone number" required>
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create Account & Pay</button>
        </form>
    </div>
</div>

<script>
function showTab(id, el){
    document.querySelectorAll(".form").forEach(f=>f.classList.remove("active"));
    document.querySelectorAll(".tab").forEach(t=>t.classList.remove("active"));
    document.getElementById(id).classList.add("active");
    el.classList.add("active");
}
</script>

</body>
</html>
