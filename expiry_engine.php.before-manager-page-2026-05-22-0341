<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;

use RouterOS\Query;

$log = __DIR__ . "/expiry_engine.log";

function logx($msg){
    global $log;
    file_put_contents($log, "[".date("Y-m-d H:i:s")."] ".$msg.PHP_EOL, FILE_APPEND);
}

$expired = $pdo->query("
    SELECT *
    FROM smart_vouchers
    WHERE status='used'
    AND expires_at IS NOT NULL
    AND expires_at <= NOW()
")->fetchAll(PDO::FETCH_ASSOC);

$count = 0;

try{
    $r = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $mt = require __DIR__ . "/config/mikrotik.php";
$client = new Client([
    "host" => $mt["host"],
    "user" => $mt["user"],
    "pass" => $mt["pass"],
    "port" => $mt["port"],
    "timeout" => 5
]);

    foreach($expired as $v){
        $code = $v["voucher_code"];

        $users = $client->query(
            (new Query("/ip/hotspot/user/print"))->where("name",$code)
        )->read();

        foreach($users as $u){
            if(isset($u[".id"])){
                $client->query(
                    (new Query("/ip/hotspot/user/remove"))->equal(".id",$u[".id"])
                )->read();
            }
        }

        $active = $client->query(
            (new Query("/ip/hotspot/active/print"))->where("user",$code)
        )->read();

        foreach($active as $a){
            if(isset($a[".id"])){
                $client->query(
                    (new Query("/ip/hotspot/active/remove"))->equal(".id",$a[".id"])
                )->read();
            }
        }

        $pdo->prepare("UPDATE smart_vouchers SET status='expired', updated_at=NOW() WHERE id=?")
            ->execute([$v["id"]]);

        logx("Expired and disconnected voucher: ".$code);
        $count++;
    }

    echo "<div style="font-family:Arial;padding:30px">
<h2 style="color:green">Expiry Engine Complete ✅</h2>
<p>Expired vouchers disconnected: <b>$expiredCount</b></p>
<p>Redirecting back...</p>
</div>
<script>
setTimeout(()=>{ window.location.href="smart_vouchers.php"; },1500);
</script>";

}catch(Exception $e){
    logx("ERROR: ".$e->getMessage());
    echo "<h2 style='font-family:Arial;color:red'>Expiry Engine Error</h2>";
    echo "<p>".htmlspecialchars($e->getMessage())."</p>";
}
