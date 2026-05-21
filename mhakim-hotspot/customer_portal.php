<?php
require_once __DIR__ . "/config/database.php";
$user = $_GET["user"] ?? "";
$data = null;

if($user){
    $stmt = $pdo->prepare("
        SELECT voucher_code AS username, package_name, status, used_at, expires_at
        FROM smart_vouchers
        WHERE voucher_code=?
        LIMIT 1
    ");
    $stmt->execute([$user]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hakim Customer Portal</title>
<style>
body{font-family:Arial;background:#052e16;color:white;display:flex;align-items:center;justify-content:center;min-height:100vh}
.card{background:white;color:#020617;border-radius:24px;padding:28px;max-width:430px;width:100%}
input,button{width:100%;padding:14px;border-radius:12px;border:1px solid #cbd5e1;margin-top:10px}
button{background:#16a34a;color:white;font-weight:900;border:0}
</style>
</head>
<body>
<div class="card">
<h2>Hakim Networks Portal</h2>
<form>
<input name="user" placeholder="Enter voucher / phone" value="<?php echo htmlspecialchars($user); ?>">
<button>Check Status</button>
</form>
<?php if($data): ?>
<hr>
<p><b>User:</b> <?php echo htmlspecialchars($data["username"]); ?></p>
<p><b>Package:</b> <?php echo htmlspecialchars($data["package_name"]); ?></p>
<p><b>Status:</b> <?php echo htmlspecialchars($data["status"]); ?></p>
<p><b>Used:</b> <?php echo htmlspecialchars($data["used_at"] ?? "-"); ?></p>
<p><b>Expires:</b> <?php echo htmlspecialchars($data["expires_at"] ?? "-"); ?></p>
<?php endif; ?>
</div>
</body>
</html>
