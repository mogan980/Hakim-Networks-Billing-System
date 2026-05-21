<?php
$checkout = $_GET["checkout_id"] ?? "";
$phone = $_GET["phone"] ?? "";
$package = $_GET["package"] ?? "Internet Package";
$amount = $_GET["amount"] ?? "0";
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Waiting for Payment</title>
<link rel="stylesheet" href="assets/hakim-ui.css">
</head>
<body>
<div class="center">
<div class="panel">
  <div class="brand" style="justify-content:center"><div class="logo">H</div><div>Hakim<br><span class="green">Networks</span></div></div>

  <div class="loader"></div>
  <h2>Waiting for Payment Confirmation</h2>
  <p>Please confirm payment on your phone. This may take a few seconds.</p>

  <div class="card">
    <div>Checking payment status... <span id="count">0</span>/60</div>
    <div class="progress"><div class="bar" id="bar"></div></div>
  </div>

  <div class="card">
    <div class="row"><span>Package</span><b><?= htmlspecialchars($package) ?></b></div>
    <div class="row"><span>Amount</span><b>KES <?= htmlspecialchars($amount) ?></b></div>
    <div class="row"><span>Phone</span><b><?= htmlspecialchars($phone) ?></b></div>
  </div>

  <div class="notice">Do not close or refresh this page.</div>
</div>
</div>

<script>
const checkout = "<?= htmlspecialchars($checkout) ?>";
let count = 0;

const timer = setInterval(async()=>{
 count++;
 document.getElementById("count").innerText=count;
 document.getElementById("bar").style.width=Math.min((count/60)*100,100)+"%";

 try{
   const r = await fetch("check_payment.php?checkout="+encodeURIComponent(checkout)+"&t="+Date.now());
   const d = await r.json();

   if(d.status==="paid"){
     clearInterval(timer);
     window.location.replace("payment_success.php?checkout_id="+encodeURIComponent(checkout));
     return;
   }

   if(d.status==="failed"){
     clearInterval(timer);
     alert("Payment failed or cancelled.");
     location.href="index.php";
   }
 }catch(e){}

 if(count>=60){
   clearInterval(timer);
 }
},3000);
</script>
</body>
</html>
