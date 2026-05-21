<?php
session_start();
require_once "config/database.php";

$companyId = (int)($_GET["company_id"] ?? ($_SESSION["tenant_company_id"] ?? 0));

if (!$companyId) {
    header("Location: saas_auth.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM companies WHERE id=? LIMIT 1");
$stmt->execute([$companyId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    die("Company not found.");
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = trim($_POST["phone"] ?? "");
    $reference = trim($_POST["reference"] ?? "");

    $pdo->prepare("
        INSERT INTO subscriptions(company_id, amount, status, payment_phone, payment_reference, created_at)
        VALUES(?, 1000, 'pending', ?, ?, NOW())
    ")->execute([$companyId, $phone, $reference]);

    $pdo->prepare("UPDATE companies SET status='pending' WHERE id=?")->execute([$companyId]);

    session_destroy();

    $msg = "Payment request submitted. Wait for super admin approval after payment confirmation.";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Pay Subscription</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;min-height:100vh;font-family:Arial;background:linear-gradient(135deg,#020617,#052e2b,#071827);color:white;display:flex;align-items:center;justify-content:center;padding:20px}
.card{width:100%;max-width:560px;background:rgba(15,23,42,.92);border:1px solid rgba(34,197,94,.2);border-radius:30px;padding:34px;box-shadow:0 30px 90px rgba(0,0,0,.45)}
.logo{width:64px;height:64px;border-radius:20px;background:#22c55e;color:#052e16;display:grid;place-items:center;font-size:32px;font-weight:900;margin-bottom:18px}
h1{color:#22c55e;margin:0 0 12px}
p{color:#cbd5e1;line-height:1.6}
.price{font-size:38px;font-weight:900;color:#22c55e;margin:20px 0}
.box{padding:14px;background:rgba(2,6,23,.7);border:1px solid rgba(148,163,184,.16);border-radius:16px;margin:10px 0}
input{width:100%;padding:15px;border-radius:15px;border:1px solid rgba(148,163,184,.2);background:#020617;color:white;margin:8px 0}
button{width:100%;padding:16px;border:0;border-radius:16px;background:linear-gradient(135deg,#22c55e,#86efac);color:#052e16;font-weight:900;margin-top:12px}
.success{background:#064e3b;color:#bbf7d0;padding:14px;border-radius:15px;margin-bottom:16px}
</style>
</head>
<body>
<div class="card">
<div class="logo">H</div>

<?php if($msg): ?>
<div class="success"><?php echo htmlspecialchars($msg); ?></div>
<a style="color:#22c55e;font-weight:900;" href="saas_auth.php">Back to Login</a>
<?php else: ?>

<h1>Activate Billing System</h1>
<p>Company: <b><?php echo htmlspecialchars($company["company_name"] ?? ""); ?></b></p>
<div class="price">KES 1,000 / Month</div>

<div class="box">
<b>Payment Details</b><br>
Pay KES 1,000 to your business till/paybill, then enter the payment reference below.
</div>

<form method="POST">
<input name="phone" placeholder="M-Pesa phone used to pay e.g. 0700000000" required>
<input name="reference" placeholder="M-Pesa code / receipt reference" required>
<button type="submit">Submit Payment for Approval</button>
</form>

<p style="font-size:13px;color:#94a3b8;">Access opens only after super admin confirms payment.</p>

<?php endif; ?>
</div>
</body>
</html>
