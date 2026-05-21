<?php
require_once __DIR__ . "/config/database.php";

$package_id = (int)($_GET["package_id"] ?? 0);

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
body{margin:0;font-family:Arial;background:radial-gradient(circle at top,#16a34a44,#020617 60%);color:white;min-height:100vh;display:flex;align-items:center;justify-content:center}
.card{width:92%;max-width:430px;background:#0b1728;border:1px solid #22c55e66;border-radius:30px;padding:30px;box-shadow:0 30px 80px rgba(0,0,0,.4)}
.logo{width:70px;height:70px;background:#22c55e;color:#052e16;border-radius:22px;display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:900;margin-bottom:18px}
input,button{width:100%;padding:15px;border-radius:15px;border:1px solid #334155;margin-top:12px;box-sizing:border-box}
input{background:#020617;color:white}
button{background:#22c55e;color:#052e16;font-weight:900;border:0}
#msg{margin-top:16px;color:#86efac;font-weight:900}
</style>
</head>
<body>
<div class="card">
<div class="logo">H</div>
<h2>Hakim Networks</h2>
<p><b><?php echo htmlspecialchars($p["name"]); ?></b><br>KES <?php echo number_format((float)$p["price"]); ?></p>

<input id="phone" placeholder="Enter M-Pesa number e.g. 0704467699">
<button onclick="pay()">Send STK Push</button>
<div id="msg"></div>
</div>

<script>
async function pay(){
 const phone=document.getElementById("phone").value.trim();
 const msg=document.getElementById("msg");

 if(!phone){
   msg.innerText="Enter your M-Pesa number.";
   return;
 }

 msg.innerText="Sending STK Push...";

 const fd=new FormData();
 fd.append("phone",phone);
 fd.append("amount","<?php echo (int)$p["price"]; ?>");
 fd.append("package_id","<?php echo (int)$p["id"]; ?>");

 const r=await fetch("stk_push.php",{method:"POST",body:fd});
 const d=await r.json();

 if(d.ok===true || d.ResponseCode==="0"){
   msg.innerText="STK sent. Enter PIN and wait...";
   pollPayment(d.CheckoutRequestID);
 }else{
   msg.innerText=d.error || "STK failed.";
 }
}

function pollPayment(checkout){
 let count=0;
 const timer=setInterval(async()=>{
   count++;

   const r=await fetch("check_payment.php?checkout="+encodeURIComponent(checkout));
   const d=await r.json();

   if(d.status==="paid"){
     clearInterval(timer);
     window.location.href="payment_success.php?u="+encodeURIComponent(d.username)+"&p="+encodeURIComponent(d.password);
   }

   if(d.status==="failed"){
     clearInterval(timer);
     document.getElementById("msg").innerText="Payment failed or cancelled.";
   }

   if(count>60){
     clearInterval(timer);
     document.getElementById("msg").innerText="Still waiting for payment confirmation.";
   }
 },3000);
}
</script>
</body>
</html>
