<?php
require_once __DIR__ . "/config/database.php";

$packages = $pdo->query("
SELECT * FROM hotspot_packages 
WHERE status='active' 
ORDER BY price ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Hakim Networks Payment</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:Arial;background:#052e16;color:white;padding:25px}
.card{max-width:430px;margin:auto;background:white;color:#020617;padding:25px;border-radius:24px}
input,select,button{width:100%;padding:14px;margin-top:12px;border-radius:12px;border:1px solid #cbd5e1;box-sizing:border-box}
button{background:#16a34a;color:white;font-weight:900;border:0}
#msg{margin-top:15px;font-weight:900}
</style>
</head>
<body>
<div class="card">
<h2>Hakim Networks</h2>
<p>Choose package and pay via M-Pesa STK Push.</p>

<select id="package_id">
<?php foreach($packages as $p): ?>
<option value="<?php echo $p["id"]; ?>" data-price="<?php echo $p["price"]; ?>">
<?php echo htmlspecialchars($p["name"]); ?> - KES <?php echo number_format($p["price"]); ?>
</option>
<?php endforeach; ?>
</select>

<input id="phone" placeholder="Enter M-Pesa number e.g. 0704467699">

<button onclick="payNow()">Pay & Connect</button>

<div id="msg"></div>
</div>

<script>
async function payNow(){
  const pkg = document.getElementById("package_id");
  const package_id = pkg.value;
  const amount = pkg.options[pkg.selectedIndex].dataset.price;
  const phone = document.getElementById("phone").value;
  const msg = document.getElementById("msg");

  msg.innerText = "Sending STK Push...";

  const fd = new FormData();
  fd.append("phone", phone);
  fd.append("amount", amount);
  fd.append("package_id", package_id);

  const r = await fetch("stk_push.php", {method:"POST", body:fd});
  const text = await r.text();

  try{
    const d = JSON.parse(text);
    if(d.ResponseCode === "0"){
      msg.innerText = "STK sent. Enter M-Pesa PIN, then wait...";
      if(d.CheckoutRequestID){
        checkPayment(d.CheckoutRequestID);
      }
    }else{
      msg.innerText = d.error || d.ResponseDescription || text;
    }
  }catch(e){
    msg.innerText = text;
  }
}

async function checkPayment(checkout){
  const msg = document.getElementById("msg");
  let tries = 0;

  const timer = setInterval(async()=>{
    tries++;

    const r = await fetch("check_payment.php?checkout=" + encodeURIComponent(checkout));
    const d = await r.json();

    if(d.status === "paid"){
      clearInterval(timer);
      msg.innerText = "Payment received. Connecting...";
      window.location.href = "payment_success.php?u=" + encodeURIComponent(d.username);
    }

    if(d.status === "failed"){
      clearInterval(timer);
      msg.innerText = "Payment failed or cancelled.";
    }

    if(tries > 30){
      clearInterval(timer);
      msg.innerText = "Payment still pending. Try refreshing.";
    }
  }, 3000);
}
</script>
</body>
</html>
