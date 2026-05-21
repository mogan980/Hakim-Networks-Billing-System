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
<title>Complete Payment</title>
<link rel="stylesheet" href="assets/hakim-ui.css">
</head>
<body>
<div class="center">
<div class="panel">
  <div class="brand" style="justify-content:center"><div class="logo">H</div><div>Hakim<br><span class="green">Networks</span></div></div>
  <h2>Complete Your Payment</h2>
  <p>Enter your M-PESA number to receive STK Push</p>

  <div class="card" style="text-align:left">
    <div class="row"><span>Package</span><b><?= htmlspecialchars($p["name"]) ?></b></div>
    <div class="row"><span>Amount</span><b>KES <?= number_format((float)$p["price"],2) ?></b></div>
    <label style="display:block;margin-top:15px">Phone Number</label>
    <input id="phone" placeholder="0704919887">
    <p style="color:#94a3b8;font-size:13px">You will receive an STK Push on this number</p>
    <button class="btn" style="width:100%" id="payBtn" onclick="pay()">Send STK Push</button>
  </div>

  <div id="msg" style="margin-top:16px;color:#86efac;font-weight:900"></div>
</div>
</div>

<script>
async function pay(){
 const phone=document.getElementById("phone").value.trim();
 const msg=document.getElementById("msg");
 const btn=document.getElementById("payBtn");
 if(!phone){msg.innerText="Enter your M-PESA number.";return;}
 btn.disabled=true; btn.innerText="Sending...";
 msg.innerText="Sending STK Push...";

 const fd=new FormData();
 fd.append("phone",phone);
 fd.append("amount","<?= (int)$p["price"] ?>");
 fd.append("package_id","<?= (int)$p["id"] ?>");

 try{
   const r=await fetch("stk_push.php",{method:"POST",body:fd});
   const d=await r.json();

   if(d.ok===true || d.ResponseCode==="0"){
     location.href="stk_waiting.php?checkout_id="+encodeURIComponent(d.CheckoutRequestID)+"&phone="+encodeURIComponent(phone)+"&package=<?= urlencode($p["name"]) ?>&amount=<?= urlencode($p["price"]) ?>";
   }else{
     btn.disabled=false; btn.innerText="Send STK Push";
     msg.innerText=d.error || "STK failed.";
   }
 }catch(e){
   btn.disabled=false; btn.innerText="Send STK Push";
   msg.innerText="Connection error. Try again.";
 }
}
</script>
</body>
</html>
