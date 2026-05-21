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
body{margin:0;font-family:Arial;background:linear-gradient(135deg,#020617,#064e3b);color:white;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center}
.card{max-width:420px;background:rgba(255,255,255,.1);padding:35px;border-radius:26px;box-shadow:0 25px 70px rgba(0,0,0,.35)}
.loader{width:60px;height:60px;border:6px solid #ffffff33;border-top-color:#22c55e;border-radius:50%;animation:spin 1s linear infinite;margin:25px auto}
@keyframes spin{to{transform:rotate(360deg)}}
button{background:#22c55e;color:#052e16;border:0;padding:14px 24px;border-radius:14px;font-weight:900}
</style>
</head>
<body>
<div class="card">
<h1>Payment Successful</h1>
<div class="loader"></div>
<p>Connecting you to Hakim Networks automatically...</p>

<form id="loginForm" method="POST" action="http://192.168.88.1/login">
<input type="hidden" name="username" value="<?php echo htmlspecialchars($u); ?>">
<input type="hidden" name="password" value="<?php echo htmlspecialchars($p); ?>">
<input type="hidden" name="dst" value="http://1.1.1.1">
<input type="hidden" name="popup" value="true">
<button type="submit">Connect Now</button>
</form>
</div>

<script>
setTimeout(()=>document.getElementById("loginForm").submit(),1200);
</script>
</body>
</html>
