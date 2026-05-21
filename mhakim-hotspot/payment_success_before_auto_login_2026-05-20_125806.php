<?php
$u = $_GET["u"] ?? "";
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connected</title>
<style>
body{font-family:Arial;background:linear-gradient(135deg,#020617,#064e3b);color:white;text-align:center;padding:40px}
.card{max-width:420px;margin:70px auto;background:rgba(255,255,255,.1);padding:30px;border-radius:24px}
a{display:inline-block;background:#22c55e;color:#052e16;text-decoration:none;padding:14px 24px;border-radius:14px;font-weight:900}
</style>
</head>
<body>
<div class="card">
<h1>Payment Successful</h1>
<p>Your internet account has been activated.</p>
<p><b><?php echo htmlspecialchars($u); ?></b></p>
<a href="http://192.168.88.1/login?username=<?php echo urlencode($u); ?>&password=<?php echo urlencode($u); ?>&dst=http://1.1.1.1">Connect Internet</a>
</div>
</body>
</html>
