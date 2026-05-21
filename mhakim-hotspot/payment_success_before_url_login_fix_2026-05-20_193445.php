<?php
$u = $_GET["u"] ?? "";
$p = $_GET["p"] ?? $u;
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hakim Networks - Connecting</title>
<style>
body{margin:0;font-family:Arial;background:radial-gradient(circle at top,#16a34a33,#020617 55%);color:white;min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center}
.card{width:92%;max-width:430px;background:rgba(15,23,42,.92);border:1px solid rgba(34,197,94,.35);padding:36px;border-radius:30px;box-shadow:0 30px 80px rgba(0,0,0,.45)}
.logo{width:76px;height:76px;border-radius:24px;background:#22c55e;color:#052e16;display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:900;margin:0 auto 18px}
.loader{width:62px;height:62px;border:6px solid #ffffff24;border-top-color:#22c55e;border-radius:50%;animation:spin 1s linear infinite;margin:22px auto}
@keyframes spin{to{transform:rotate(360deg)}}
h1{margin:0;font-size:28px}
p{color:#cbd5e1;line-height:1.6}
button{background:#22c55e;color:#052e16;border:0;padding:14px 24px;border-radius:16px;font-weight:900;margin-top:12px}
.creds{background:#020617;border:1px solid #1f2937;padding:14px;border-radius:18px;margin-top:18px;font-size:13px;color:#cbd5e1}
</style>
</head>
<body>
<div class="card">
<div class="logo">H</div>
<h1>Payment Successful</h1>
<div class="loader"></div>
<p>Hakim Networks is reconnecting your internet automatically...</p>

<form id="loginForm" method="POST" action="http://192.168.88.1/login">
<input type="hidden" name="username" value="<?php echo htmlspecialchars($u); ?>">
<input type="hidden" name="password" value="<?php echo htmlspecialchars($p); ?>">
<input type="hidden" name="dst" value="http://1.1.1.1">
<input type="hidden" name="popup" value="true">
<button type="submit">Connect Internet</button>
</form>

<div class="creds">
If automatic login fails, tap Connect Internet.<br>
User: <?php echo htmlspecialchars($u); ?>
</div>
</div>

<script>
setTimeout(()=>document.getElementById("loginForm").submit(),1500);
</script>
</body>
</html>
