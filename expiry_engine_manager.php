<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Query;

$pdo->exec("SET time_zone = '+03:00'");

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }

function has_col($pdo,$table,$col){
    try{
        $s=$pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $s->execute([$col]);
        return (bool)$s->fetch();
    }catch(Exception $e){ return false; }
}

function mt_client(){
    $cfg = require __DIR__ . "/config/mikrotik.php";
    foreach(array_unique([$cfg["host"] ?? "192.168.88.1","192.168.88.1","10.10.10.1"]) as $host){
        try{
            return new Client([
                "host"=>$host,
                "user"=>$cfg["user"],
                "pass"=>$cfg["pass"],
                "port"=>$cfg["port"],
                "timeout"=>5
            ]);
        }catch(Exception $e){}
    }
    return null;
}

function disconnect_ip($client,$ip){
    if(!$client || !$ip) return false;
    $done=false;

    try{
        $active=$client->query((new Query("/ip/hotspot/active/print"))->where("address",$ip))->read();
        foreach($active as $a){
            if(isset($a[".id"])){
                $client->query((new Query("/ip/hotspot/active/remove"))->equal(".id",$a[".id"]))->read();
                $done=true;
            }
        }
    }catch(Exception $e){}

    try{
        foreach($client->query(new Query("/queue/simple/print"))->read() as $q){
            if(isset($q["target"],$q[".id"]) && str_contains($q["target"],$ip)){
                $client->query((new Query("/queue/simple/remove"))->equal(".id",$q[".id"]))->read();
                $done=true;
            }
        }
    }catch(Exception $e){}

    return $done;
}

$client = mt_client();
$message = "";

if($_SERVER["REQUEST_METHOD"]==="POST"){
    $source=$_POST["source"] ?? "";
    $id=(int)($_POST["id"] ?? 0);
    $action=$_POST["action"] ?? "";
    $hours=max(1,(int)($_POST["hours"] ?? 1));

    if($source==="voucher"){
        $s=$pdo->prepare("SELECT * FROM smart_vouchers WHERE id=?");
        $s->execute([$id]);
        $r=$s->fetch(PDO::FETCH_ASSOC);

        if($r){
            $ip=trim($r["used_by"] ?? "");

            if($action==="disconnect"){
                disconnect_ip($client,$ip);
                $message="Voucher client disconnected.";
            }

            if($action==="clean"){
                disconnect_ip($client,$ip);
                $pdo->prepare("UPDATE smart_vouchers SET status='expired' WHERE id=?")->execute([$id]);
                $message="Expired voucher cleaned.";
            }

            if($action==="extend"){
                $pdo->prepare("
                    UPDATE smart_vouchers
                    SET expires_at=DATE_ADD(GREATEST(COALESCE(expires_at,NOW()),NOW()), INTERVAL ? HOUR),
                        status='used'
                    WHERE id=?
                ")->execute([$hours,$id]);
                $message="Voucher extended by {$hours} hour(s).";
            }

            if($action==="delete"){
                disconnect_ip($client,$ip);
                $pdo->prepare("DELETE FROM smart_vouchers WHERE id=?")->execute([$id]);
                $message="Voucher deleted.";
            }
        }
    }

    if($source==="stk"){
        $s=$pdo->prepare("SELECT * FROM payments WHERE id=?");
        $s->execute([$id]);
        $r=$s->fetch(PDO::FETCH_ASSOC);

        if($r){
            $ip=trim($r["client_ip"] ?? "");

            if($action==="disconnect"){
                disconnect_ip($client,$ip);
                $message="STK client disconnected.";
            }

            if($action==="clean"){
                disconnect_ip($client,$ip);
                $pdo->prepare("UPDATE payments SET status='expired' WHERE id=?")->execute([$id]);
                $message="Expired STK client cleaned.";
            }

            if($action==="extend"){
                if(has_col($pdo,"payments","expires_at")){
                    $pdo->prepare("
                        UPDATE payments
                        SET expires_at=DATE_ADD(GREATEST(COALESCE(expires_at,NOW()),NOW()), INTERVAL ? HOUR),
                            status='paid'
                        WHERE id=?
                    ")->execute([$hours,$id]);
                    $message="STK client extended by {$hours} hour(s).";
                }else{
                    $message="Cannot extend STK: payments.expires_at column missing.";
                }
            }

            if($action==="delete"){
                disconnect_ip($client,$ip);
                $pdo->prepare("DELETE FROM payments WHERE id=?")->execute([$id]);
                $message="STK record deleted.";
            }
        }
    }
}

if(isset($_GET["auto_clean"])){
    $cleaned=0;

    foreach($pdo->query("SELECT * FROM smart_vouchers WHERE status='used' AND expires_at IS NOT NULL AND expires_at<=NOW()")->fetchAll(PDO::FETCH_ASSOC) as $r){
        $ip=trim($r["used_by"] ?? "");
        if(disconnect_ip($client,$ip)) $cleaned++;
        $pdo->prepare("UPDATE smart_vouchers SET status='expired' WHERE id=?")->execute([$r["id"]]);
    }

    if(has_col($pdo,"payments","expires_at")){
        foreach($pdo->query("SELECT * FROM payments WHERE status='paid' AND expires_at IS NOT NULL AND expires_at<=NOW()")->fetchAll(PDO::FETCH_ASSOC) as $r){
            $ip=trim($r["client_ip"] ?? "");
            if(disconnect_ip($client,$ip)) $cleaned++;
            $pdo->prepare("UPDATE payments SET status='expired' WHERE id=?")->execute([$r["id"]]);
        }
    }

    $message="Auto clean complete. Removed {$cleaned} expired online client(s).";
}

$onlineIps=[];
if($client){
    try{
        foreach($client->query(new Query("/ip/hotspot/active/print"))->read() as $a){
            if(!empty($a["address"])) $onlineIps[$a["address"]]=true;
        }
    }catch(Exception $e){}
}

$limit=$_GET["limit"] ?? "20";
$sourceFilter=$_GET["source"] ?? "all";
$sqlLimit = $limit==="all" ? "" : " LIMIT ".(int)$limit;

$items=[];

if($sourceFilter==="all" || $sourceFilter==="voucher"){
    foreach($pdo->query("SELECT * FROM smart_vouchers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC) as $r){
        $ip=trim($r["used_by"] ?? "");
        $expires=$r["expires_at"] ?? null;
        $expired=$expires && strtotime($expires)<=time();
        $items[]=[
            "source"=>"voucher",
            "id"=>$r["id"],
            "code"=>$r["voucher_code"] ?? "-",
            "status"=>$expired ? "expired" : ($r["status"] ?? "-"),
            "ip"=>$ip,
            "online"=>$ip && isset($onlineIps[$ip]),
            "used"=>$r["used_at"] ?? "-",
            "expires"=>$expires ?: "-",
            "remaining"=>$expires ? (strtotime($expires)>time() ? floor((strtotime($expires)-time())/3600)."h ".floor(((strtotime($expires)-time())%3600)/60)."m" : "Expired") : "-"
        ];
    }
}

if($sourceFilter==="all" || $sourceFilter==="stk"){
    $hasExpires=has_col($pdo,"payments","expires_at");
    $hasPackage=has_col($pdo,"payments","package_id") && has_col($pdo,"packages","duration_hours");

    $expiresSql = $hasExpires
        ? "p.expires_at"
        : ($hasPackage ? "DATE_ADD(p.created_at, INTERVAL COALESCE(pk.duration_hours,0) HOUR)" : "NULL");

    $join = $hasPackage ? "LEFT JOIN packages pk ON pk.id=p.package_id" : "";

    $rows=$pdo->query("
        SELECT p.*, $expiresSql AS calculated_expires
        FROM payments p
        $join
        WHERE p.status IN ('paid','expired')
        AND p.method IN ('mpesa','stk','M-PESA','cash')
        AND p.client_ip IS NOT NULL
        AND p.client_ip!=''
        ORDER BY p.id DESC
        $sqlLimit
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach($rows as $r){
        $ip=trim($r["client_ip"] ?? "");
        $expires=$r["calculated_expires"] ?? null;
        $expired=$expires && strtotime($expires)<=time();

        $items[]=[
            "source"=>"stk",
            "id"=>$r["id"],
            "code"=>$r["phone"] ?? ("STK-".$r["id"]),
            "status"=>$expired ? "expired" : ($r["status"] ?? "-"),
            "ip"=>$ip,
            "online"=>$ip && isset($onlineIps[$ip]),
            "used"=>$r["created_at"] ?? "-",
            "expires"=>$expires ?: "-",
            "remaining"=>$expires ? (strtotime($expires)>time() ? floor((strtotime($expires)-time())/3600)."h ".floor(((strtotime($expires)-time())%3600)/60)."m" : "Expired") : "No expiry"
        ];
    }
}

usort($items,function($a,$b){
    if($a["status"]==="expired" && $a["online"]) return -1;
    if($b["status"]==="expired" && $b["online"]) return 1;
    return strcmp($b["used"],$a["used"]);
});

if($limit!=="all") $items=array_slice($items,0,(int)$limit);

$expiredOnline=count(array_filter($items,fn($x)=>$x["status"]==="expired" && $x["online"]));
$active=count(array_filter($items,fn($x)=>$x["status"]!=="expired"));
$expired=count(array_filter($items,fn($x)=>$x["status"]==="expired"));
?>
<!DOCTYPE html>
<html>
<head>
<title>Expiry Engine Manager</title>
<style>
body{margin:0;background:#07131f;color:#e5e7eb;font-family:Arial}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020617;padding:24px 16px}
.sidebar h2{color:#22c55e}.sidebar a{display:block;color:#e5e7eb;text-decoration:none;background:#111827;margin:10px 0;padding:13px;border-radius:12px;font-weight:800}.sidebar a.active{background:#16a34a}
.main{margin-left:260px;padding:28px}.top{display:flex;justify-content:space-between}.badge{background:#064e3b;color:#86efac;border-radius:999px;padding:10px 16px;font-weight:900}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.card{background:#0f1b2d;border:1px solid #1e293b;border-radius:20px;padding:22px}.card b{font-size:38px}
.red{color:#ef4444}.blue{color:#38bdf8}.green{color:#22c55e}
.btn{border:0;border-radius:11px;padding:10px 13px;font-weight:900;cursor:pointer;text-decoration:none;display:inline-block}.g{background:#22c55e;color:#052e16}.b{background:#2563eb;color:white}.o{background:#f97316;color:white}.r{background:#ef4444;color:white}.gray{background:#475569;color:white}
.filters{display:flex;gap:12px;margin:20px 0}.filters select{background:#020617;color:white;border:1px solid #334155;border-radius:12px;padding:11px}
.box{background:#0f1b2d;border:1px solid #1e293b;border-radius:20px;padding:18px;overflow:auto}table{width:100%;border-collapse:collapse;font-size:13px}th{background:#020617;padding:12px;text-align:left}td{padding:12px;border-bottom:1px solid #1e293b}.pill{border-radius:999px;padding:6px 10px;font-weight:900;font-size:11px}.on{background:#dcfce7;color:#166534}.off,.ex{background:#fee2e2;color:#991b1b}.ok{background:#dcfce7;color:#166534}input{width:55px;background:#020617;color:white;border:1px solid #334155;border-radius:9px;padding:8px}
.msg{background:#052e16;color:#86efac;padding:12px;border-radius:12px;margin:15px 0}
</style>
</head>
<body>
<div class="sidebar">
<h2>M.Hakim</h2>
<a href="noc_final_clean.php">📊 Dashboard</a>
<a href="smart_vouchers.php">💳 Smart Vouchers</a>
<a class="active" href="client_expiry_manager.php">⏱ Expiry Engine</a>
<a href="stk_live_monitor.php">💰 STK Monitor</a>
<a href="payments.php">💵 Payments</a>
</div>

<div class="main">
<div class="top">
<div><h1>Auto Expiry Engine</h1><p>Time now: <?=date("Y-m-d H:i:s")?> Africa/Nairobi</p></div>
<span class="badge">● <?=$client?"MikroTik Online":"MikroTik Offline"?></span>
</div>

<?php if($message): ?><div class="msg"><?=h($message)?></div><?php endif; ?>

<div class="cards">
<div class="card"><span>Expired Still Online</span><b class="red"><?=$expiredOnline?></b><p>Expired clients still on WiFi.</p></div>
<div class="card"><span>Total Expired</span><b class="blue"><?=$expired?></b><p>Expired voucher/STK clients.</p></div>
<div class="card"><span>Currently Active</span><b class="green"><?=$active?></b><p>Valid clients not yet expired.</p></div>
</div>

<div style="margin:20px 0">
<a class="btn g" href="?auto_clean=1">⚡ Auto Clean Expired Online Users</a>
<a class="btn b" href="smart_vouchers.php">← Back to Smart Vouchers</a>
</div>

<form class="filters" method="get">
<select name="source" onchange="this.form.submit()">
<option value="all" <?=$sourceFilter==="all"?"selected":""?>>All Clients</option>
<option value="voucher" <?=$sourceFilter==="voucher"?"selected":""?>>Voucher Clients</option>
<option value="stk" <?=$sourceFilter==="stk"?"selected":""?>>STK Clients</option>
</select>

<select name="limit" onchange="this.form.submit()">
<option value="10" <?=$limit==="10"?"selected":""?>>Show 10</option>
<option value="20" <?=$limit==="20"?"selected":""?>>Show 20</option>
<option value="all" <?=$limit==="all"?"selected":""?>>Show All</option>
</select>
</form>

<div class="box">
<h2>Voucher + STK Expiry Control</h2>
<table>
<tr><th>Source</th><th>Client/Code</th><th>Status</th><th>Online</th><th>IP</th><th>Used</th><th>Expires</th><th>Remaining</th><th>Actions</th></tr>
<?php foreach($items as $x): ?>
<tr>
<td><?=strtoupper(h($x["source"]))?></td>
<td><b style="color:#5eead4"><?=h($x["code"])?></b></td>
<td><span class="pill <?=$x["status"]==="expired"?"ex":"ok"?>"><?=strtoupper(h($x["status"]))?></span></td>
<td><span class="pill <?=$x["online"]?"on":"off"?>"><?=$x["online"]?"ONLINE":"OFFLINE"?></span></td>
<td><?=h($x["ip"] ?: "-")?></td>
<td><?=h($x["used"])?></td>
<td><?=h($x["expires"])?></td>
<td><?=h($x["remaining"])?></td>
<td>
<form method="post" style="display:inline"><input type="hidden" name="source" value="<?=h($x["source"])?>"><input type="hidden" name="id" value="<?=$x["id"]?>"><input type="hidden" name="action" value="disconnect"><button class="btn o">Disconnect</button></form>
<form method="post" style="display:inline"><input type="hidden" name="source" value="<?=h($x["source"])?>"><input type="hidden" name="id" value="<?=$x["id"]?>"><input type="hidden" name="action" value="clean"><button class="btn gray">Clean</button></form>
<form method="post" style="display:inline"><input type="hidden" name="source" value="<?=h($x["source"])?>"><input type="hidden" name="id" value="<?=$x["id"]?>"><input type="hidden" name="action" value="extend"><input name="hours" type="number" value="1" min="1"><button class="btn g">Extend</button></form>
<form method="post" style="display:inline" onsubmit="return confirm('Delete this record?')"><input type="hidden" name="source" value="<?=h($x["source"])?>"><input type="hidden" name="id" value="<?=$x["id"]?>"><input type="hidden" name="action" value="delete"><button class="btn r">Delete</button></form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
</body>
</html>
