<?php
require_once __DIR__ . "/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function normalizeSpeed($v){
    $v = strtoupper(trim((string)$v));
    if($v === "") return "1M";
    if(strpos($v,"M") === false && strpos($v,"K") === false) $v .= "M";
    return $v;
}

$r = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$client = new Client(new Config([
    "host"=>$r["router_ip"],
    "user"=>$r["router_username"],
    "pass"=>$r["router_password"],
    "port"=>(int)$r["api_port"],
    "timeout"=>10
]));

$vouchers = $pdo->query("
SELECT * FROM smart_vouchers
WHERE status='used'
AND expires_at IS NOT NULL
AND expires_at > NOW()
")->fetchAll(PDO::FETCH_ASSOC);

$count = 0;

foreach($vouchers as $v){
    $code = $v["voucher_code"];
    $down = normalizeSpeed($v["speed_down"] ?? "8M");
    $up = normalizeSpeed($v["speed_up"] ?? "2M");
    $rate = $up . "/" . $down;
    $profile = "VOUCHER-" . str_replace("M","",$down) . "-" . str_replace("M","",$up);

    $profiles = $client->query((new Query("/ip/hotspot/user/profile/print"))->where("name",$profile))->read();
    if(empty($profiles)){
        $client->query((new Query("/ip/hotspot/user/profile/add"))->equal("name",$profile)->equal("rate-limit",$rate)->equal("shared-users","1"))->read();
    }

    $users = $client->query((new Query("/ip/hotspot/user/print"))->where("name",$code))->read();
    if(empty($users)){
        $client->query((new Query("/ip/hotspot/user/add"))
            ->equal("name",$code)
            ->equal("password",$code)
            ->equal("profile",$profile)
            ->equal("comment","reconnect-voucher:".$code)
        )->read();
        $count++;
    }
}

$payments = $pdo->query("
SELECT * FROM hotspot_payments
WHERE status='paid'
AND username IS NOT NULL
ORDER BY id DESC
LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);

foreach($payments as $p){
    $user = $p["username"];
    $pass = $p["password"] ?: $user;

    $users = $client->query((new Query("/ip/hotspot/user/print"))->where("name",$user))->read();
    if(empty($users)){
        $client->query((new Query("/ip/hotspot/user/add"))
            ->equal("name",$user)
            ->equal("password",$pass)
            ->equal("profile","default")
            ->equal("comment","reconnect-stk:".$p["checkout_request_id"])
        )->read();
        $count++;
    }
}

echo "<h2 style='font-family:Arial;color:green'>Reconnect complete. Restored users: $count</h2>";
