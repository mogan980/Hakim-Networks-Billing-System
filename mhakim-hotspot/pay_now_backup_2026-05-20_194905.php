<?php
require_once __DIR__ . "/config/database.php";

$package_id = (int)($_POST["package_id"] ?? $_GET["package_id"] ?? 1);
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id=? LIMIT 1");
$stmt->execute([$package_id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$p){ die("Package not found"); }
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pay with M-Pesa</title>
<style>
body{font-family:Arial;background:#052e16;color:white;display:flex;align-items:center;justify-content:center;min-height:100vh}
.card{background:white;color:#020617;padding:28px;border-radius:24px;max-width:420px;width:100%;box-shadow:0 25px 60px rgba(0,0,0,.3)}
input,button{width:100%;padding:15px;margin-top:12px;border-radius:14px;border:1px solid #cbd5e1;box-sizing:border-box}
button{background:#16a34a;color:white;font-weight:900;border:0}
#msg{margin-top:14px;font-weight:900}
</style>
</head>
<body>
<div class="card">
<h2>Hakim Networks</h2>
<p><b><?php echo htmlspecialchars($p["name"]); ?></b> — KES <?php echo number_format($p["price"]); ?></p>

<input id="phone" placeholder="Enter M-Pesa number e.g. 0704467699">
<button onclick="pay()">Send STK Push</button>
<div id="msg"></div>
</div>

<script>
async function pay(){
  const msg=document.getElementById("msg");
  msg.innerText="Sending STK Push...";

  const fd=new FormData();
  fd.append("phone",document.getElementById("phone").value);
  fd.append("amount","<?php echo (int)$p["price"]; ?>");
  fd.append("package_id","<?php echo (int)$p["id"]; ?>");

  const r=await fetch("stk_push.php",{method:"POST",body:fd});
  const d=await r.json();

  if(d.ResponseCode==="0"){
    msg.innerText="STK sent. Enter PIN and wait...";
    poll(d.CheckoutRequestID);
  }else{
    msg.innerText=d.error || d.ResponseDescription || "STK failed";
  }
}

function poll(checkout){
  let count=0;
  const timer=setInterval(async()=>{
    count++;
    const r=await fetch("check_payment.php?checkout="+checkout);
    const d=await r.json();

    if(d.status==="paid"){
      clearInterval(timer);
      window.location.href="payment_success.php?u="+encodeURIComponent(d.username);
    }

    if(d.status==="failed"){
      clearInterval(timer);
      document.getElementById("msg").innerText="Payment failed or cancelled.";
    }

    if(count>40){
      clearInterval(timer);
      document.getElementById("msg").innerText="Still pending. Refresh after payment.";
    }
  },3000);
}
</script>
</body>
</html>
