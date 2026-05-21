<?php
$type = $_GET["type"] ?? "voucher";
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hakim Networks Connected</title>
<style>
body{margin:0;font-family:Arial;background:radial-gradient(circle at top,#16a34a44,#020617 60%);color:white;min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center}
.card{width:92%;max-width:430px;background:#0b1728;border:1px solid #22c55e66;border-radius:30px;padding:35px;box-shadow:0 30px 80px rgba(0,0,0,.45)}
.logo{width:76px;height:76px;background:#22c55e;color:#052e16;border-radius:24px;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:900;margin:0 auto 18px}
.loader{width:58px;height:58px;border:6px solid #ffffff24;border-top-color:#22c55e;border-radius:50%;animation:spin 1s linear infinite;margin:20px auto}
@keyframes spin{to{transform:rotate(360deg)}}
a{display:inline-block;background:#22c55e;color:#052e16;text-decoration:none;padding:14px 24px;border-radius:16px;font-weight:900;margin-top:15px}
p{color:#cbd5e1;line-height:1.6}
</style>
</head>
<body>
<div class="card">
<div class="logo">H</div>
<h1>Internet Activated</h1>
<div class="loader"></div>
<p>Your <?php echo htmlspecialchars($type); ?> access is active. Reconnecting your device...</p>
<a href="http://neverssl.com">Open Internet</a>
</div>
<script>
setTimeout(()=>{ window.location.href="http://neverssl.com"; },3000);
</script>
</body>
</html>
