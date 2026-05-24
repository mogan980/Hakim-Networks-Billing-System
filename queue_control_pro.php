<?php
date_default_timezone_set("Africa/Nairobi");
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Query;

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }

function mt(){
    $c=require __DIR__."/config/mikrotik.php";
    return new Client([
        "host"=>$c["host"],
        "user"=>$c["user"],
        "pass"=>$c["pass"],
        "port"=>$c["port"],
        "timeout"=>5
    ]);
}

$msg="";
$client=null;

try{ $client=mt(); }catch(Exception $e){ $msg=$e->getMessage(); }

function remove_queue($client,$id){
    $client->query((new Query("/queue/simple/remove"))->equal(".id",$id))->read();
}

function set_custom_limit($client,$id,$download,$upload){
    $download = preg_replace('/[^0-9]/','',$download);
    $upload = preg_replace('/[^0-9]/','',$upload);

    if(!$download || !$upload){
        throw new Exception("Invalid speed values");
    }

    // MikroTik format is upload/download
    $limit = $upload."M/".$download."M";

    $client->query(
        (new Query("/queue/simple/set"))
        ->equal(".id",$id)
        ->equal("max-limit",$limit)
    )->read();

    return $download."M / ".$upload."M";
}

if($client && $_SERVER["REQUEST_METHOD"]==="POST"){
    $id=$_POST["id"] ?? "";
    $action=$_POST["action"] ?? "";

    try{
        if($action==="delete"){ remove_queue($client,$id); $msg="Queue deleted."; }
        if($action==="disable"){ $client->query((new Query("/queue/simple/set"))->equal(".id",$id)->equal("disabled","yes"))->read(); $msg="Queue disabled."; }
        if($action==="enable"){ $client->query((new Query("/queue/simple/set"))->equal(".id",$id)->equal("disabled","no"))->read(); $msg="Queue enabled."; }
        if($action==="setlimit"){
            $download = $_POST["download"] ?? "6";
            $upload = $_POST["upload"] ?? "2";
            $shown = set_custom_limit($client,$id,$download,$upload);
            $msg="Queue updated to ".$shown;
        }
    }catch(Exception $e){ $msg=$e->getMessage(); }
}

/* Auto delete queues for expired clients */
if($client && isset($_GET["clean_expired"])){
    $expiredIps=[];

    foreach($pdo->query("SELECT used_by FROM smart_vouchers WHERE expires_at IS NOT NULL AND expires_at<=NOW()")->fetchAll(PDO::FETCH_ASSOC) as $r){
        if(!empty($r["used_by"])) $expiredIps[]=$r["used_by"];
    }

    foreach($pdo->query("SELECT client_ip FROM payments WHERE expires_at IS NOT NULL AND expires_at<=NOW()")->fetchAll(PDO::FETCH_ASSOC) as $r){
        if(!empty($r["client_ip"])) $expiredIps[]=$r["client_ip"];
    }

    $expiredIps=array_unique($expiredIps);
    $deleted=0;

    foreach($client->query(new Query("/queue/simple/print"))->read() as $q){
        $target=$q["target"] ?? "";
        foreach($expiredIps as $ip){
            if(str_contains($target,$ip) && isset($q[".id"])){
                remove_queue($client,$q[".id"]);
                $deleted++;
            }
        }
    }

    $msg="Expired queues cleaned: ".$deleted;
}

$identity="-";
$queues=[];
$onlineIps=[];

if($client){
    try{
        $id=$client->query(new Query("/system/identity/print"))->read();
        $identity=$id[0]["name"] ?? "MikroTik";

        foreach($client->query(new Query("/ip/hotspot/active/print"))->read() as $a){
            if(!empty($a["address"])) $onlineIps[$a["address"]]="active";
        }

        foreach($client->query(new Query("/ip/hotspot/host/print"))->read() as $h){
            if(!empty($h["address"]) && empty($onlineIps[$h["address"]])){
                $onlineIps[$h["address"]]="host";
            }
        }

        foreach($client->query(new Query("/ip/arp/print"))->read() as $a){
            if(!empty($a["address"]) && (($a["complete"] ?? "true") === "true") && empty($onlineIps[$a["address"]])){
                $onlineIps[$a["address"]]="arp";
            }
        }

        foreach($client->query(new Query("/ip/dhcp-server/lease/print"))->read() as $l){
            if(!empty($l["address"]) && (($l["status"] ?? "") === "bound") && empty($onlineIps[$l["address"]])){
                $onlineIps[$l["address"]]="dhcp";
            }
        }

        foreach($client->query(new Query("/ppp/active/print"))->read() as $a){
            if(!empty($a["address"])) $onlineIps[$a["address"]]=true;
        }

        $queues=$client->query(new Query("/queue/simple/print"))->read();
    }catch(Exception $e){ $msg=$e->getMessage(); }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Queue Control Pro</title>
<style>
body{margin:0;background:#eef3f8;font-family:Arial;color:#0f172a}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020617;padding:25px}
.sidebar h2{color:white}.sidebar a{display:block;color:white;text-decoration:none;padding:13px;margin:8px 0;border-radius:12px;background:#111827;font-weight:800}.sidebar a.active{background:#16a34a}
.main{margin-left:260px;padding:30px}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin:20px 0}
.card{background:white;border-radius:18px;padding:20px;box-shadow:0 10px 25px rgba(15,23,42,.06)}
.box{background:white;border-radius:22px;padding:22px;box-shadow:0 10px 28px rgba(15,23,42,.08)}
table{width:100%;border-collapse:collapse}th{background:#020617;color:white;padding:13px;text-align:left}td{padding:13px;border-bottom:1px solid #e5e7eb}
.btn{border:0;border-radius:10px;padding:8px 12px;font-weight:900;cursor:pointer;margin:2px}
.green{background:#22c55e;color:#052e16}.blue{background:#2563eb;color:white}.orange{background:#f97316;color:white}.red{background:#ef4444;color:white}.gray{background:#64748b;color:white}
.pill{padding:6px 10px;border-radius:999px;font-size:11px;font-weight:900}.on{background:#dcfce7;color:#166534}.off{background:#fee2e2;color:#991b1b}
.msg{background:#dcfce7;color:#166534;padding:12px;border-radius:12px;margin:15px 0;font-weight:800}
</style>
</head>
<body>
<div class="sidebar">
<h2>M.Hakim</h2>
<a href="noc_final_clean.php">📊 Dashboard</a>
<a href="queue_control_pro.php" class="active">⚡ Queue Control</a>
<a href="client_expiry_manager.php">⏱ Client Expiry</a>
<a href="smart_vouchers.php">💳 Smart Vouchers</a>
<a href="pppoe.php">🌐 PPPoE</a>
</div>

<div class="main">
<h1>⚡ Queue Control Pro</h1>
<p>Professional MikroTik queue manager with online status, custom speed control and expired queue cleanup.</p>

<?php if($msg): ?><div class="msg"><?=h($msg)?></div><?php endif; ?>

<div class="cards">
<div class="card"><small>Router Status</small><h2><?=$client?"Online":"Offline"?></h2></div>
<div class="card"><small>Router Identity</small><h2><?=h($identity)?></h2></div>
<div class="card"><small>Total Queues</small><h2><?=count($queues)?></h2></div>
<div class="card"><small>Speed Default</small><h2>Custom</h2></div>
</div>

<p>
<a class="btn green" href="?clean_expired=1">🧹 Auto Clean Expired Queues</a>
<button class="btn blue" onclick="location.reload()">↻ Refresh</button>
</p>

<div class="box">
<h2>Live Queues</h2>
<table>
<tr>
<th>Name</th><th>Target</th><th>Status</th><th>Online</th><th>Max Limit</th><th>Bytes</th><th>Actions</th>
</tr>

<?php foreach($queues as $q):
$target=$q["target"] ?? "-";
preg_match('/([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/',$target,$m);
$ip=$m[1] ?? "";
$online=$ip && isset($onlineIps[$ip]);
$onlineMode=$online ? $onlineIps[$ip] : "offline";
$disabled=($q["disabled"] ?? "false")==="true";
?>
<tr>
<td><b><?=h($q["name"] ?? "-")?></b></td>
<td><?=h($target)?></td>
<td><span class="pill <?=$disabled?'off':'on'?>"><?=$disabled?'DISABLED':'ACTIVE'?></span></td>
<td>
<?php if($onlineMode==="active"): ?>
<span class="pill on">ACTIVE LOGIN</span>
<?php elseif($onlineMode==="host"): ?>
<span class="pill on">CONNECTED HOST</span>
<?php elseif($onlineMode==="arp"): ?>
<span class="pill on">ARP ONLINE</span>
<?php elseif($onlineMode==="dhcp"): ?>
<span class="pill on">DHCP ONLINE</span>
<?php else: ?>
<span class="pill off">OFFLINE</span>
<?php endif; ?>
</td>
<td>
<?php
$limit = $q["max-limit"] ?? "-";

if(str_contains($limit,"/")){
    [$up,$down] = explode("/",$limit);

    $upM = round(((int)$up)/1000000);
    $downM = round(((int)$down)/1000000);

    echo $downM . "M / " . $upM . "M";
}else{
    echo h($limit);
}
?>
</td>
<td><?=h($q["bytes"] ?? "0/0")?></td>
<td>
<form method="post" style="display:inline-flex;gap:5px;align-items:center">
<input type="hidden" name="id" value="<?=h($q[".id"] ?? "")?>">
<input type="hidden" name="action" value="setlimit">
<input name="download" type="number" min="1" value="6" title="Download Mbps" style="width:55px;padding:7px;border-radius:8px;border:1px solid #cbd5e1">
<span style="font-weight:900">D</span>
<input name="upload" type="number" min="1" value="2" title="Upload Mbps" style="width:55px;padding:7px;border-radius:8px;border:1px solid #cbd5e1">
<span style="font-weight:900">U</span>
<button class="btn green">Apply</button>
</form>

<form method="post" style="display:inline">
<input type="hidden" name="id" value="<?=h($q[".id"] ?? "")?>">
<input type="hidden" name="action" value="<?=$disabled?'enable':'disable'?>">
<button class="btn orange"><?=$disabled?'Enable':'Disable'?></button>
</form>

<form method="post" style="display:inline" onsubmit="return confirm('Delete this queue?')">
<input type="hidden" name="id" value="<?=h($q[".id"] ?? "")?>">
<input type="hidden" name="action" value="delete">
<button class="btn red">Delete</button>
</form>

<a class="btn blue" href="client_expiry_manager.php?search=<?=urlencode($ip)?>">View</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>

<script id="HN_QUEUE_AUTO_REFRESH">
setTimeout(function(){
    window.location.reload();
}, 5000);
</script>
\n</body>
</html>
