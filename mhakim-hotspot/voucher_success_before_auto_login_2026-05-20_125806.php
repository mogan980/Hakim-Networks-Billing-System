<?php
$u = $_GET["u"] ?? "";
$p = $_GET["p"] ?? $u;
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connecting...</title>
<style>
body{font-family:Arial;background:#052e16;color:white;text-align:center;padding:40px}
.card{max-width:420px;margin:60px auto;background:#ffffff12;padding:30px;border-radius:24px}
button{background:#22c55e;border:0;padding:14px 24px;border-radius:14px;font-weight:900}
</style>
</head>
<body>
<div class="card">
<h1>Hakim Networks</h1>
<p>Voucher accepted. Logging you in...</p>

<form id="f" method="post" action="http://192.168.88.1/login">
<input type="hidden" name="username" value="<?php echo htmlspecialchars($u); ?>">
<input type="hidden" name="password" value="<?php echo htmlspecialchars($p); ?>">
<input type="hidden" name="dst" value="http://neverssl.com/">
<button>Connect Now</button>
</form>
</div>

<script>
setTimeout(()=>document.getElementById("f").submit(),1500);
</script>
</body>
</html>
