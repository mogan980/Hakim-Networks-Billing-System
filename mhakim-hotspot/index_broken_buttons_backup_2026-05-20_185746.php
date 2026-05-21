<?php
require_once __DIR__ . "/config/database.php";

$packages = $pdo->query("
SELECT
 id,
 name,
 duration_hours,
 speed_down,
 speed_up,
 price,
 status
FROM packages
WHERE status='active'
ORDER BY price ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hakim Networks Hotspot</title>
<style>
*{box-sizing:border-box}
body{
 margin:0;
 font-family:Arial,Helvetica,sans-serif;
 background:radial-gradient(circle at left,#0f7a3b,#052e2b 45%,#020617);
 color:white;
 min-height:100vh;
}
.wrap{max-width:1200px;margin:auto;padding:28px}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}
.brand{display:flex;gap:14px;align-items:center}
.logo{background:#4ade80;color:#052e16;width:52px;height:52px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:25px}
.status{background:#071827;border:1px solid #14532d;padding:12px 18px;border-radius:999px;color:#bbf7d0}
.hero{text-align:center;margin:25px 0}
.hero h1{font-size:34px;margin:0 0 10px}
.hero p{color:#d1fae5;line-height:1.6}
.features{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;max-width:760px;margin:22px auto}
.feature{background:#0b1728;border:1px solid #1f3b4d;border-radius:18px;padding:18px;text-align:center}
.feature b{color:#22c55e;font-size:18px}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:25px}
.card{
 background:#0b1728;
 border:1px solid rgba(74,222,128,.22);
 border-radius:22px;
 padding:20px;
 box-shadow:0 20px 50px rgba(0,0,0,.25);
}
.card h3{margin:0 0 14px;font-size:20px}
.price{font-size:32px;font-weight:900;color:#22c55e;margin-bottom:12px}
.tags{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.tag{background:#020617;border:1px solid #1e293b;padding:7px 11px;border-radius:999px;font-size:12px;color:#cbd5e1}
.btn{
 width:100%;
 border:0;
 background:linear-gradient(135deg,#22c55e,#86efac);
 color:#052e16;
 padding:14px;
 border-radius:14px;
 font-weight:900;
 cursor:pointer;
 font-size:15px;
}
.voucher{
 margin-top:35px;
 background:rgba(255,255,255,.08);
 border:1px solid rgba(255,255,255,.12);
 border-radius:24px;
 padding:22px;
 max-width:520px;
}
input{
 width:100%;
 padding:15px;
 border-radius:14px;
 border:1px solid #334155;
 background:#020617;
 color:white;
 margin-top:10px;
}
.modal{
 display:none;
 position:fixed;
 inset:0;
 background:rgba(2,6,23,.82);
 z-index:9999;
 align-items:center;
 justify-content:center;
 padding:20px;
}
.modalBox{
 background:white;
 color:#020617;
 max-width:430px;
 width:100%;
 border-radius:24px;
 padding:26px;
 box-shadow:0 25px 80px rgba(0,0,0,.4);
}
.modalBox input{background:#f8fafc;color:#020617;border:1px solid #cbd5e1}
.close{float:right;cursor:pointer;font-weight:900;color:#dc2626}
#msg{margin-top:14px;font-weight:900;color:#064e3b}
.support{position:fixed;right:22px;bottom:22px;background:#22c55e;color:#052e16;padding:14px 20px;border-radius:999px;text-decoration:none;font-weight:900}
@media(max-width:1000px){.grid{grid-template-columns:repeat(2,1fr)}.features{grid-template-columns:1fr}}
@media(max-width:650px){.grid{grid-template-columns:1fr}.top{align-items:flex-start;gap:12px;flex-direction:column}.hero h1{font-size:28px}}

.pay-link{
display:block!important;
width:100%!important;
padding:15px!important;
border-radius:16px!important;
background:linear-gradient(135deg,#22c55e,#86efac)!important;
color:#052e16!important;
font-weight:900!important;
text-align:center!important;
text-decoration:none!important;
position:relative!important;
z-index:99999!important;
pointer-events:auto!important;
}
.card,.package-card,.btn,a,button{
pointer-events:auto!important;
}

.pay-btn{
display:block!important;
width:100%!important;
padding:15px!important;
border-radius:16px!important;
background:linear-gradient(135deg,#22c55e,#86efac)!important;
color:#052e16!important;
font-weight:900!important;
text-align:center!important;
border:0!important;
cursor:pointer!important;
position:relative!important;
z-index:999999!important;
pointer-events:auto!important;
}
.card,.btn,button{
pointer-events:auto!important;
}
</style>
</head>
<body>

<div class="wrap">
 <div class="top">
  <div class="brand">
   <div class="logo">H</div>
   <div>
    <h2 style="margin:0">Hakim Networks</h2>
    <small>Fast • Secure • Reliable Internet</small>
   </div>
  </div>
  <div class="status">● Live Hotspot</div>
 </div>

 <div class="hero">
  <h1>Buy Internet Access Instantly</h1>
  <p>Select a package, pay via M-Pesa STK Push, or redeem a voucher.</p>
 </div>

 <div class="features">
  <div class="feature"><b>Auto</b><br>M-Pesa activation</div>
  <div class="feature"><b>Secure</b><br>Voucher login</div>
  <div class="feature"><b>Fast</b><br>MikroTik hotspot</div>
 </div>

 <div class="grid">
 <?php foreach($packages as $p): ?>
  <div class="card">
   <h3><?php echo htmlspecialchars($p["name"]); ?></h3>
   <div class="price">KES <?php echo number_format((float)$p["price"]); ?></div>
   <div class="tags">
    <span class="tag">⚡ <?php echo htmlspecialchars($p["speed_down"]."/".$p["speed_up"]); ?></span>
    <span class="tag">⏱ <?php echo htmlspecialchars($p["duration_hours"]); ?> hrs</span>
   </div>
   <button class="btn"
    onclick="openPay('<?php echo (int)$p["id"]; ?>','<?php echo htmlspecialchars($p["name"]); ?>','<?php echo (int)$p["price"]; ?>')">
    Pay with M-Pesa STK
   </button>
  </div>
 <?php endforeach; ?>
 </div>

 <div class="voucher">
  <h2>Redeem Voucher</h2>
  <p style="color:#d1fae5">Enter your voucher code to activate internet.</p>
  <form method="POST" action="redeem_voucher.php">
   <input name="voucher_code" placeholder="Enter voucher code e.g. MH-XXXXXXX" required>
   <button class="btn" style="margin-top:12px">Activate Voucher</button>
  </form>
 </div>
</div>

<a class="support" href="https://wa.me/254704467699">💬 WhatsApp Support</a>

<div class="modal" id="payModal">
 <div class="modalBox">
  <span class="close" onclick="closePay()">X</span>
  <h2>Pay with M-Pesa STK</h2>
  <p id="packageText"></p>

  <input id="phone" placeholder="Enter M-Pesa number e.g. 0704467699">
  <button class="btn" onclick="sendSTK()" style="margin-top:12px">Send STK Push</button>

  <div id="msg"></div>
 </div>
</div>

<script>
let selectedPackage = null;
let selectedAmount = null;

function openPay(id,name,amount){
 selectedPackage = id;
 selectedAmount = amount;
 document.getElementById("packageText").innerHTML = "<b>"+name+"</b> — KES "+amount;
 document.getElementById("msg").innerText = "";
 document.getElementById("phone").value = "";
 document.getElementById("payModal").style.display = "flex";
}

function closePay(){
 document.getElementById("payModal").style.display = "none";
}

async function sendSTK(){
 const phone = document.getElementById("phone").value.trim();
 const msg = document.getElementById("msg");

 if(!phone){
  msg.innerText = "Enter your M-Pesa phone number.";
  return;
 }

 msg.innerText = "Sending STK Push...";

 const fd = new FormData();
 fd.append("phone", phone);
 fd.append("amount", selectedAmount);
 fd.append("package_id", selectedPackage);

 const r = await fetch("stk_push.php", {method:"POST", body:fd});
 const d = await r.json();

 if(d.ResponseCode === "0"){
  msg.innerText = "STK sent. Enter M-Pesa PIN and wait...";
  pollPayment(d.CheckoutRequestID);
 }else{
  msg.innerText = d.error || d.ResponseDescription || "STK failed.";
 }
}

function pollPayment(checkout){
 let tries = 0;
 const msg = document.getElementById("msg");

 const timer = setInterval(async()=>{
  tries++;
  const r = await fetch("check_payment.php?checkout="+encodeURIComponent(checkout));
  const d = await r.json();

  if(d.status === "paid"){
   clearInterval(timer);
   msg.innerText = "Payment received. Connecting...";
   window.location.href = "payment_success.php?u="+encodeURIComponent(d.username)+"+"&p="+encodeURIComponent(d.password)p="+encodeURIComponent(d.password);
  }

  if(d.status === "failed"){
   clearInterval(timer);
   msg.innerText = "Payment failed or cancelled.";
  }

  if(tries > 40){
   clearInterval(timer);
   msg.innerText = "Payment still pending. Refresh or contact support.";
  }
 },3000);
}
</script>


<script>
document.addEventListener("click", function(e){
  const btn = e.target.closest(".pay-btn");
  if(btn){
    e.preventDefault();
    e.stopPropagation();
    const id = btn.getAttribute("data-package");
    window.location.href = "pay_now.php?package_id=" + encodeURIComponent(id);
  }
});
</script>
</body>
</html>
