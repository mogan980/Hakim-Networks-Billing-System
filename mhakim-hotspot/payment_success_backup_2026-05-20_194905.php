<?php
$u = $_GET["u"] ?? "";
$p = $_GET["p"] ?? $u;

$loginUrl = "http://192.168.88.1/login?username=" . urlencode($u) .
            "&password=" . urlencode($p) .
            "&dst=" . urlencode("http://neverssl.com") .
            "&popup=true";
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hakim Networks - Connecting</title>
<style>
body{margin:0;font-family:Arial;background:radial-gradient(circle at top,#16a34a33,#020617 55%);color:white;min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center}
.card{width:92%;max-width:430px;background:#0b1728;border:1px solid rgba(34,197,94,.4);padding:35px;border-radius:30px;box-shadow:0 30px 80px rgba(0,0,0,.45)}
.logo{width:76px;height:76px;border-radius:24px;background:#22c55e;color:#052e16;display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:900;margin:0 auto 18px}
.loader{width:62px;height:62px;border:6px solid #ffffff24;border-top-color:#22c55e;border-radius:50%;animation:spin 1s linear infinite;margin:22px auto}
@keyframes spin{to{transform:rotate(360deg)}}
a{display:inline-block;background:#22c55e;color:#052e16;text-decoration:none;padding:14px 24px;border-radius:16px;font-weight:900;margin-top:12px}
p{color:#cbd5e1;line-height:1.6}
</style>
</head>
<body>
<div class="card">
<div class="logo">H</div>
<h1>Payment Successful</h1>
<div class="loader"></div>
<p>Connecting your device to Hakim Networks...</p>
<a id="connectBtn" href="<?php echo htmlspecialchars($loginUrl); ?>">Connect Internet</a>
</div>

<script>
setTimeout(()=>{
  window.location.href = <?php echo json_encode($loginUrl); ?>;
},1200);
</script>
</body>
</html>
