<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Redeem Voucher</title>
<link rel="stylesheet" href="/mhakim-hotspot/assets/hakim-ui.css">
</head>
<body>
<div class="center">
<div class="panel">
  <div class="brand" style="justify-content:center"><div class="logo">H</div><div>Hakim<br><span class="green">Networks</span></div></div>
  <h2>Redeem Voucher</h2>
  <p>Enter your Hakim Networks voucher code and connect instantly.</p>

  <form id="voucherForm" class="card">
    <input type="text" id="voucher_code" name="voucher_code" placeholder="Enter voucher code e.g. MH-XXXXXXXX" required>
    <button class="btn" style="width:100%;margin-top:14px" type="submit" id="activateBtn">Activate Voucher</button>
  </form>

  <div id="status" class="green" style="margin-top:16px;font-weight:900"></div>
</div>
</div>

<script>
document.getElementById("voucherForm").addEventListener("submit", async function(e){
 e.preventDefault();
 const btn=document.getElementById("activateBtn");
 const status=document.getElementById("status");
 const code=document.getElementById("voucher_code").value.trim();
 btn.disabled=true; btn.innerText="Activating...";
 status.innerText="Checking voucher and activating internet...";

 const r=await fetch("redeem_smart_voucher.php",{
   method:"POST",
   headers:{"Content-Type":"application/x-www-form-urlencoded"},
   body:"voucher_code="+encodeURIComponent(code)
 });
 const text=await r.text();
 document.open(); document.write(text); document.close();
});
</script>
</body>
</html>
