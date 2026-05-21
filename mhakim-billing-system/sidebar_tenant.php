<?php
$userName = $_SESSION["tenant_name"] ?? "Client";
?>

<div class="sidebar">

    <div class="brand">
        <div class="logo">H</div>

        <h2>Hakim Networks</h2>

        <p><?php echo htmlspecialchars($userName); ?></p>
    </div>

    <div class="nav">

        <a href="noc_final_clean.php">📊 Dashboard</a>

        <a href="clients.php">👥 Clients</a>

        <a href="packages.php">📦 Packages</a>

        <a href="vouchers.php">🎟️ Vouchers</a>

        <a href="payments.php">💳 Payments</a>

        <a href="analytics.php">📈 Analytics</a>

        <a href="router_wizard.php">🧙 Router Wizard</a>

        <a href="health_check.php">🛰️ Health Check</a>

        <a href="logout.php">🚪 Logout</a>

    </div>
</div>

<style>
.sidebar{
    width:250px;
    height:100vh;
    position:fixed;
    left:0;
    top:0;
    overflow:auto;
    background:
        linear-gradient(180deg,#020617,#071827,#052e2b);
    border-right:1px solid rgba(34,197,94,.14);
    padding:22px;
}

.brand{
    margin-bottom:24px;
}

.logo{
    width:58px;
    height:58px;
    border-radius:18px;
    background:linear-gradient(135deg,#22c55e,#86efac);
    color:#052e16;
    display:grid;
    place-items:center;
    font-size:28px;
    font-weight:900;
    margin-bottom:14px;
}

.brand h2{
    color:#fff;
    margin:0;
}

.brand p{
    color:#94a3b8;
    font-size:13px;
}

.nav{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.nav a{
    text-decoration:none;
    color:#fff;
    padding:14px;
    border-radius:14px;
    background:rgba(2,6,23,.38);
    transition:.25s;
    font-weight:600;
}

.nav a:hover{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    color:#052e16;
    transform:translateX(3px);
}
</style>
