<?php
require_once __DIR__ . "/config/database.php";
session_start();

$message = "";

$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $admin_name = $_POST["admin_name"] ?? "";
    $admin_email = $_POST["admin_email"] ?? "";
    $company_name = $_POST["company_name"] ?? "Hakim Networks";
    $new_password = $_POST["new_password"] ?? "";
    $logo_path = $_POST["existing_logo"] ?? "";

    if (!empty($_FILES["company_logo"]["name"])) {
        $allowed = ["image/png", "image/jpeg", "image/jpg", "image/webp"];
        if (in_array($_FILES["company_logo"]["type"], $allowed)) {
            $ext = pathinfo($_FILES["company_logo"]["name"], PATHINFO_EXTENSION);
            $filename = "company_logo_" . time() . "." . $ext;
            $target = $uploadDir . $filename;

            if (move_uploaded_file($_FILES["company_logo"]["tmp_name"], $target)) {
                $logo_path = "uploads/" . $filename;
            }
        }
    }


    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            company_name VARCHAR(150),
            admin_name VARCHAR(150),
            admin_email VARCHAR(150),
            password_hash VARCHAR(255) NULL,
        logo_path VARCHAR(255) NULL,
            logo_path VARCHAR(255) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    $exists = $pdo->query("SELECT COUNT(*) FROM admin_settings")->fetchColumn();

    if ($new_password !== "") {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
    } else {
        $old = $pdo->query("SELECT password_hash FROM admin_settings ORDER BY id DESC LIMIT 1")->fetchColumn();
        $hash = $old ?: null;
    }

    if ($exists) {
        $stmt = $pdo->prepare("UPDATE admin_settings SET company_name=?, admin_name=?, admin_email=?, password_hash=?, logo_path=? WHERE id=1");
        $stmt->execute([$company_name, $admin_name, $admin_email, $hash, $logo_path]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO admin_settings (id, company_name, admin_name, admin_email, password_hash, logo_path) VALUES (1,?,?,?,?,?)");
        $stmt->execute([$company_name, $admin_name, $admin_email, $hash, $logo_path]);
    }

    $message = "Settings saved successfully.";
    $settings = $pdo->query("SELECT * FROM admin_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
}

$pdo->exec("
    CREATE TABLE IF NOT EXISTS admin_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        company_name VARCHAR(150),
        admin_name VARCHAR(150),
        admin_email VARCHAR(150),
        password_hash VARCHAR(255) NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

$settings = $pdo->query("SELECT * FROM admin_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    $settings = [
        "company_name" => "Hakim Networks",
        "admin_name" => "M. Hakim",
        "admin_email" => "admin@hakimnetworks.local"
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Settings - Hakim Networks</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:linear-gradient(135deg,#020617,#064e3b);color:#0f172a}
.layout{display:flex;min-height:100vh}
.sidebar{width:250px;background:#020617;color:white;position:fixed;top:0;bottom:0;left:0;padding:20px;overflow:auto}
.logo{font-size:26px;font-weight:900}.sub{font-size:13px;color:#94a3b8;margin-bottom:25px}
.nav a{display:block;color:white;text-decoration:none;background:#111827;margin:8px 0;padding:13px;border-radius:13px;font-weight:800}
.nav a:hover,.nav a.active{background:#16a34a}
.main{margin-left:250px;width:calc(100% - 250px);padding:30px}
.card{background:white;border-radius:24px;padding:28px;box-shadow:0 18px 45px rgba(0,0,0,.18);max-width:900px;margin:auto}
h1{margin-top:0;font-size:32px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.field{margin-bottom:18px}
label{display:block;font-weight:900;margin-bottom:8px;color:#334155}
input{width:100%;padding:14px;border:1px solid #cbd5e1;border-radius:14px;font-size:15px}
.btns{display:flex;gap:12px;flex-wrap:wrap;margin-top:20px}
button,.btn{border:none;background:#16a34a;color:white;padding:13px 18px;border-radius:14px;font-weight:900;text-decoration:none;cursor:pointer}
.logout{background:#dc2626}
.back{background:#2563eb}
.notice{background:#dcfce7;color:#047857;padding:12px;border-radius:14px;margin-bottom:18px;font-weight:800}
.section-title{margin-top:25px;padding-top:20px;border-top:1px solid #e5e7eb}
@media(max-width:850px){.sidebar{position:relative;width:100%;height:auto}.main{margin-left:0;width:100%;padding:15px}.layout{display:block}.grid{grid-template-columns:1fr}}

.logo-box{
    display:grid;
    grid-template-columns:130px 1fr;
    gap:18px;
    align-items:center;
    background:linear-gradient(135deg,#f8fafc,#ecfdf5);
    border:1px solid #dbeafe;
    border-radius:20px;
    padding:18px;
    margin-bottom:20px;
}
.logo-preview{
    width:120px;
    height:120px;
    border-radius:22px;
    background:#020617;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    box-shadow:0 12px 30px rgba(0,0,0,.15);
}
.logo-preview img{
    width:100%;
    height:100%;
    object-fit:cover;
}
.logo-preview span{
    font-size:45px;
    color:white;
}
small{
    color:#64748b;
    font-weight:700;
}
@media(max-width:650px){
    .logo-box{grid-template-columns:1fr}
}

</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <div class="logo">📊 M.Hakim</div>
    <div class="sub">Admin Control Panel</div>
    <div class="nav">
        <a href="noc_final_clean.php">📡 Live NOC</a>
        <a href="noc_final_clean.php">📊 Dashboard</a>
        <a href="clients.php">👥 Clients</a>
        <a href="payments.php">💳 Payments</a>
        <a href="reports.php">📈 Reports</a>
        <a class="active" href="settings.php">⚙️ Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</aside>

<main class="main">
<div class="card">
    <h1>⚙️ Admin Settings</h1>
    <p>Manage your admin profile, company details, email, password and logout access.</p>

    <?php if($message): ?>
        <div class="notice"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <h2 class="section-title">👤 Admin Profile</h2>

        <div class="grid">
            <div class="field">
                <label>Admin Name</label>
                <input type="text" name="admin_name" value="<?php echo htmlspecialchars($settings["admin_name"] ?? ""); ?>" required>
            </div>

            <div class="field">
                <label>Admin Email</label>
                <input type="email" name="admin_email" value="<?php echo htmlspecialchars($settings["admin_email"] ?? ""); ?>" required>
            </div>
        </div>

        <h2 class="section-title">🏢 Company Details</h2>

        <div class="logo-box">
            <div class="logo-preview">
                <?php if(!empty($settings["logo_path"])): ?>
                    <img src="<?php echo htmlspecialchars($settings["logo_path"]); ?>" alt="Company Logo">
                <?php else: ?>
                    <span>📡</span>
                <?php endif; ?>
            </div>

            <div>
                <label>Company Logo</label>
                <input type="file" name="company_logo" accept="image/png,image/jpeg,image/jpg,image/webp">
                <input type="hidden" name="existing_logo" value="<?php echo htmlspecialchars($settings["logo_path"] ?? ""); ?>">
                <small>Upload PNG, JPG, JPEG or WEBP logo.</small>
            </div>
        </div>


        <div class="field">
            <label>Company / Brand Name</label>
            <input type="text" name="company_name" value="<?php echo htmlspecialchars($settings["company_name"] ?? "Hakim Networks"); ?>" required>
        </div>

        <h2 class="section-title">🔐 Password Settings</h2>

        <div class="field">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Leave blank if you do not want to change password">
        </div>

        <div class="btns">
            <button type="submit">💾 Save Settings</button>
            <a class="btn back" href="noc_final_clean.php">📡 Back to Live NOC</a>
            <a class="btn logout" href="logout.php">🚪 Logout</a>
        </div>
    </form>
</div>
</main>
</div>
</body>
</html>
