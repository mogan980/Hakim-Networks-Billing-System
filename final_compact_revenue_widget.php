<?php
date_default_timezone_set("Africa/Nairobi");
require_once __DIR__ . "/config/database.php";

function frw_one($pdo,$sql){
    try{
        $v=$pdo->query($sql)->fetchColumn();
        return is_numeric($v) ? (float)$v : 0;
    }catch(Exception $e){
        return 0;
    }
}

function frw_count($pdo,$table){
    try{
        return (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    }catch(Exception $e){
        return 0;
    }
}

function frw_money($n){
    return "Ksh ".number_format((float)$n,0);
}

$today = frw_one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()");
$week  = frw_one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$month = frw_one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
$total = frw_one($pdo,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'");

$clients  = frw_count($pdo,"clients");
$packages = frw_count($pdo,"packages");
$vouchers = frw_count($pdo,"smart_vouchers");
?>

<style>
.final-revenue-widget{
    background:#fff;
    border-radius:24px;
    padding:18px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}

.frw-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:16px;
}

.frw-head h2{
    margin:0;
    font-size:20px;
    font-weight:900;
    color:#0f172a;
}

.frw-head p{
    margin:4px 0 0;
    color:#64748b;
    font-size:13px;
}

.frw-toggle{
    border:none;
    background:#f1f5f9;
    border-radius:12px;
    padding:8px 14px;
    cursor:pointer;
    font-weight:800;
}

.frw-cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
}

.frw-card{
    border-radius:18px;
    padding:12px;
    min-height:82px;transition:.25s ease;
}

.frw-card small{
    display:block;
    color:#475569;
    font-size:12px;
    font-weight:800;
    margin-bottom:8px;
}

.frw-card b{
    display:block;
    font-size:16px;
    margin-bottom:6px;
    color:#0f172a;
}

.frw-card:hover{
        transform:translateY(-3px);
        box-shadow:0 10px 24px rgba(15,23,42,.10);
    }

.frw-card span{
    color:#64748b;
    font-size:12px;
}

.green{background:#dcfce7;}
.blue{background:#dbeafe;}
.yellow{background:#fef3c7;}
.purple{background:#ede9fe;}

.frw-stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
    margin-top:12px;
}

.frw-stats a{
    text-decoration:none;
    border:1px solid #e2e8f0;
    border-radius:14px;
    padding:12px 14px;
    display:flex;
    justify-content:space-between;
    font-weight:800;
    color:#0f172a;
}

.frw-stats b{
    color:#16a34a;
}

.money-hidden .frw-card b{
    filter:blur(8px);
}

@media(max-width:900px){
    .frw-cards{
        grid-template-columns:1fr 1fr;
    }

    .frw-stats{
        grid-template-columns:1fr;
    }
}
</style>

<section class="final-revenue-widget" id="finalRevenueWidget">

    <div class="frw-head">
        <div>
            <h2>💰 Revenue Performance</h2>
            <p>Live payment summary from your billing system.</p>
        </div>

        <button class="frw-toggle" onclick="toggleRevenueMoney()">
            👁 Hide
        </button>
    </div>

    <div class="frw-cards">

        <div class="frw-card green">
            <small>Today Revenue</small>
            <b><?=frw_money($today)?></b>
            <span>Collected today</span>
        </div>

        <div class="frw-card blue">
            <small>This Week</small>
            <b><?=frw_money($week)?></b>
            <span>Current week total</span>
        </div>

        <div class="frw-card yellow">
            <small>This Month</small>
            <b><?=frw_money($month)?></b>
            <span>Monthly revenue</span>
        </div>

        <div class="frw-card purple">
            <small>Total Revenue</small>
            <b><?=frw_money($total)?></b>
            <span>All-time collections</span>
        </div>

    </div>

    <div class="frw-stats">
        <a href="clients.php">Total Clients <b><?=$clients?></b></a>
        <a href="packages.php">Packages <b><?=$packages?></b></a>
        <a href="smart_vouchers.php">Vouchers <b><?=$vouchers?></b></a>
    </div>

</section>

<script>
function toggleRevenueMoney(){
    document.getElementById("finalRevenueWidget")
    .classList.toggle("money-hidden");
}
</script>
