<?php
$u = $_GET["u"] ?? "";
$p = $_GET["p"] ?? "";
$code = $_GET["code"] ?? "";

if(!$u || !$p){
    header("Location: index.php?error=Missing+voucher+login");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Voucher Activated</title>
<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Arial,sans-serif;
    background:radial-gradient(circle at top,#16a34a33,#020617 45%),linear-gradient(135deg,#020617,#052e2b);
    color:white;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:18px;
}
.card{
    width:100%;
    max-width:440px;
    background:rgba(15,23,42,.94);
    border:1px solid rgba(255,255,255,.12);
    border-radius:28px;
    padding:32px 24px;
    text-align:center;
    box-shadow:0 30px 80px rgba(0,0,0,.45);
}
.icon{
    width:86px;
    height:86px;
    border-radius:50%;
    background:#dcfce7;
    color:#047857;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:42px;
    margin:0 auto 18px;
}
h1{
    margin:0;
    color:#22c55e;
    font-size:30px;
}
p{
    color:#cbd5e1;
    line-height:1.5;
}
.box{
    background:#020617;
    border:1px solid rgba(255,255,255,.08);
    border-radius:16px;
    padding:13px;
    margin:10px 0;
    color:#e2e8f0;
    word-break:break-all;
    text-align:left;
}
.box b{
    color:#22c55e;
}
.btn{
    margin-top:16px;
    background:#22c55e;
    color:white;
    border:none;
    border-radius:16px;
    padding:16px 18px;
    font-weight:900;
    width:100%;
    font-size:16px;
    cursor:pointer;
}
.btn:hover{background:#16a34a}
.back{
    display:block;
    margin-top:12px;
    background:#334155;
    color:white;
    text-decoration:none;
    border-radius:16px;
    padding:15px;
    font-weight:900;
}
.note{
    margin-top:14px;
    font-size:13px;
    color:#94a3b8;
}
</style>
</head>
<body>

<div class="card">
    <div class="icon">✓</div>
    <h1>Voucher Activated</h1>
    <p>Your voucher is valid. Click below to connect this device to the internet.</p>

    <div class="box"><b>Username:</b> <?php echo htmlspecialchars($u); ?></div>
    <div class="box"><b>Password:</b> <?php echo htmlspecialchars($p); ?></div>

    <button class="btn" onclick="connectNow()">Connect Internet</button>
    <a class="back" href="index.php">Back to Packages</a>

    <div class="note">
        If it returns to packages, reconnect Wi-Fi and press Connect again.
    </div>
</div>

<form id="loginForm" method="post" action="http://192.168.88.1/login" style="display:none;">
    <input type="hidden" name="username" value="<?php echo htmlspecialchars($u); ?>">
    <input type="hidden" name="password" value="<?php echo htmlspecialchars($p); ?>">
    <input type="hidden" name="dst" value="http://neverssl.com/">
</form>

<script>
function connectNow(){
    document.querySelector(".btn").innerText = "Connecting...";
    document.getElementById("loginForm").submit();
}
</script>

</body>
</html>
