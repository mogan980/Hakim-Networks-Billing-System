<?php
$username = $_GET["u"] ?? "";
$password = $_GET["p"] ?? "";
$loginUrl = "http://192.168.88.1/login";
?>
<!DOCTYPE html>
<html>
<head>
<title>Connecting...</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;font-family:Arial;background:linear-gradient(135deg,#020617,#064e3b);color:white;text-align:center;padding:30px}
.card{max-width:430px;margin:70px auto;background:rgba(255,255,255,.1);padding:35px;border-radius:26px;box-shadow:0 25px 60px rgba(0,0,0,.3)}
.loader{width:60px;height:60px;border:6px solid #ffffff33;border-top-color:#22c55e;border-radius:50%;animation:spin 1s linear infinite;margin:25px auto}
@keyframes spin{to{transform:rotate(360deg)}}
button{background:#22c55e;color:#052e16;border:0;padding:14px 22px;border-radius:14px;font-weight:900}
</style>
</head>
<body>
<div class="card">
<h1>Voucher Activated</h1>
<div class="loader"></div>
<p>Connecting you to Hakim Networks...</p>

<form id="loginForm" method="post" action="<?php echo htmlspecialchars($loginUrl); ?>">
<input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
<input type="hidden" name="password" value="<?php echo htmlspecialchars($password); ?>">
<input type="hidden" name="dst" value="http://neverssl.com">
<button type="submit">Connect Now</button>
</form>
</div>

<script>
setTimeout(()=>document.getElementById("loginForm").submit(),1800);
</script>
</body>
</html>
