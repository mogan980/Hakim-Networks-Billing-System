<?php
date_default_timezone_set("Africa/Nairobi");
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Query;

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }

$msg="";
$client=null;
$active=[];
$secrets=[];

try{
    $cfg=require __DIR__ . "/config/mikrotik.php";
    $client=new Client([
        "host"=>$cfg["host"],
        "user"=>$cfg["user"],
        "pass"=>$cfg["pass"],
        "port"=>$cfg["port"],
        "timeout"=>5
    ]);

    if($_SERVER["REQUEST_METHOD"]==="POST" && ($_POST["action"] ?? "")==="disconnect"){
        $id=$_POST["id"] ?? "";
        if($id){
            $client->query((new Query("/ppp/active/remove"))->equal(".id",$id))->read();
            $msg="PPPoE user disconnected.";
        }
    }

    $active=$client->query(new Query("/ppp/active/print"))->read();
    $secrets=$client->query(new Query("/ppp/secret/print"))->read();

}catch(Exception $e){
    $msg=$e->getMessage();
}

$totalSecrets=count($secrets);
$totalActive=count($active);
$totalOffline=max(0,$totalSecrets-$totalActive);
?>
<!DOCTYPE html>
<html>
<head>
<title>PPPoE Live Monitor</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{margin:0;background:#07131f;color:#e5e7eb;font-family:Arial}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020617;padding:24px 16px}
.sidebar h2{color:#22c55e;margin:0 0 5px}.sidebar p{color:#94a3b8;font-size:12px;margin:0 0 25px}
.sidebar a{display:block;color:white;text-decoration:none;background:#111827;margin:9px 0;padding:13px;border-radius:13px;font-weight:800}
.sidebar a.active{background:#16a34a}
.main{margin-left:260px;padding:28px}
.top{display:flex;justify-content:space-between;align-items:center}
.badge{background:#064e3b;color:#86efac;border-radius:999px;padding:10px 16px;font-weight:900}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:22px 0}
.card{background:#0f1b2d;border:1px solid #1e293b;border-radius:20px;padding:20px}
.card small{color:#94a3b8;font-weight:800}.card b{display:block;font-size:30px;color:#22c55e;margin-top:10px}
.box{background:#0f1b2d;border:1px solid #1e293b;border-radius:22px;padding:20px;overflow:auto}
table{width:100%;border-collapse:collapse}
th{background:#020617;padding:13px;text-align:left}
td{padding:13px;border-bottom:1px solid #1e293b}
.btn{border:0;border-radius:10px;padding:9px 13px;font-weight:900;cursor:pointer;text-decoration:none;background:#2563eb;color:white}
.red{background:#ef4444}
.msg{background:#052e16;color:#86efac;padding:12px;border-radius:12px;margin:15px 0}
.search{background:#020617;color:white;border:1px solid #334155;border-radius:12px;padding:12px;width:320px;margin-bottom:14px}
@media(max-width:900px){.sidebar{position:relative;width:auto}.main{margin-left:0}.cards{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<p>Hakim Networks ISP</p>
<a href="noc_final_clean.php">📊 Dashboard</a>
<a href="pppoe.php">🌐 PPPoE Manager</a>
<a class="active" href="pppoe_live_monitor.php">📡 PPPoE Monitor</a>
<a href="queue_control_pro.php">⚡ Queue Control</a>
<a href="client_expiry_manager.php">⏱ Client Expiry</a>
<a href="health_check.php">💙 Health Check</a>
</div>

<div class="main">
<div class="top">
<div>
<h1>📡 PPPoE Live Monitor</h1>
<p>Live PPPoE sessions directly from MikroTik.</p>
</div>
<span class="badge">● <?=$client?"MikroTik Connected":"Offline"?></span>
</div>

<?php if($msg): ?><div class="msg"><?=h($msg)?></div><?php endif; ?>

<div class="cards">
<div class="card"><small>Active PPPoE Users</small><b><?=$totalActive?></b></div>
<div class="card"><small>Total PPPoE Secrets</small><b><?=$totalSecrets?></b></div>
<div class="card"><small>Offline PPPoE Users</small><b><?=$totalOffline?></b></div>
<div class="card"><small>Last Update</small><b><?=date("H:i:s")?></b></div>
</div>

<div class="box">
<h2>Live Active PPPoE Sessions</h2>
<input class="search" id="searchBox" placeholder="Search username, IP, service, caller ID...">

<table id="pppoeTable">
<tr>
<th>Username</th>
<th>Address</th>
<th>Caller ID</th>
<th>Service</th>
<th>Uptime</th>
<th>Encoding</th>
<th>Actions</th>
</tr>

<?php if(!$active): ?>
<tr><td colspan="7" style="color:#94a3b8;text-align:center;padding:25px">No PPPoE users online right now.</td></tr>
<?php endif; ?>

<?php foreach($active as $u): ?>
<tr>
<td><b style="color:#5eead4"><?=h($u["name"] ?? "-")?></b></td>
<td><?=h($u["address"] ?? "-")?></td>
<td><?=h($u["caller-id"] ?? "-")?></td>
<td><?=h($u["service"] ?? "-")?></td>
<td><?=h($u["uptime"] ?? "-")?></td>
<td><?=h($u["encoding"] ?? "-")?></td>
<td>
<form method="post" onsubmit="return confirm('Disconnect this PPPoE user?')" style="display:inline">
<input type="hidden" name="action" value="disconnect">
<input type="hidden" name="id" value="<?=h($u[".id"] ?? "")?>">
<button class="btn red">Disconnect</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>

<script>
document.getElementById("searchBox").addEventListener("input",function(){
  const q=this.value.toLowerCase();
  document.querySelectorAll("#pppoeTable tr").forEach((tr,i)=>{
    if(i===0)return;
    tr.style.display=tr.innerText.toLowerCase().includes(q)?"":"none";
  });
});

setTimeout(()=>location.reload(),15000);
</script>

</body>
</html>
