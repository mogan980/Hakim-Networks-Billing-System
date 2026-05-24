<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/database.php";

use RouterOS\Client;
use RouterOS\Query;

$pdo->exec("SET time_zone = '+03:00'");
$log="/tmp/hakim_hotspot_session_restore.log";

function logx($m){
    global $log;
    file_put_contents($log,"[".date("Y-m-d H:i:s")."] ".$m.PHP_EOL,FILE_APPEND);
}

function mt(){
    $cfg=require __DIR__."/../config/mikrotik.php";
    return new Client([
        "host"=>$cfg["host"],
        "user"=>$cfg["user"],
        "pass"=>$cfg["pass"],
        "port"=>$cfg["port"],
        "timeout"=>5
    ]);
}

function hotspot_user_id($mt,$username){
    $rows=$mt->query(
        (new Query("/ip/hotspot/user/print"))
        ->where("name",$username)
    )->read();

    return $rows[0][".id"] ?? null;
}

function upsert_hotspot_user($mt,$username,$password,$comment){
    $id=hotspot_user_id($mt,$username);

    if($id){
        $mt->query(
            (new Query("/ip/hotspot/user/set"))
            ->equal(".id",$id)
            ->equal("password",$password)
            ->equal("disabled","no")
            ->equal("comment",$comment)
        )->read();
        return "updated";
    }

    $mt->query(
        (new Query("/ip/hotspot/user/add"))
        ->equal("name",$username)
        ->equal("password",$password)
        ->equal("profile","default")
        ->equal("disabled","no")
        ->equal("comment",$comment)
    )->read();

    return "created";
}

function queue_exists($mt,$ip){
    foreach($mt->query(new Query("/queue/simple/print"))->read() as $q){
        if(str_contains($q["target"] ?? "",$ip)){
            return true;
        }
    }
    return false;
}

function restore_queue($mt,$name,$ip,$down="6M",$up="2M",$comment=""){
    if(queue_exists($mt,$ip)){
        return false;
    }

    $mt->query(
        (new Query("/queue/simple/add"))
        ->equal("name",$name)
        ->equal("target",$ip."/32")
        ->equal("max-limit",$up."/".$down)
        ->equal("comment",$comment)
    )->read();

    return true;
}

try{
    $mt=mt();
    $count=0;

    $rows=$pdo->query("
        SELECT id, voucher_code, used_by, expires_at
        FROM smart_vouchers
        WHERE status='used'
        AND used_by IS NOT NULL
        AND used_by!=''
        AND expires_at IS NOT NULL
        AND expires_at > NOW()
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach($rows as $r){
        $ip=trim($r["used_by"]);
        $code=trim($r["voucher_code"]);

        if(!$ip || !$code){
            continue;
        }

        upsert_hotspot_user(
            $mt,
            $code,
            $code,
            "Voucher active until ".$r["expires_at"]." IP ".$ip
        );

        restore_queue(
            $mt,
            "HN-VOUCHER-".$ip,
            $ip,
            "6M",
            "2M",
            "Voucher ".$code." expires ".$r["expires_at"]
        );

        $count++;
        logx("Voucher active-mode restored: {$code} {$ip}");
    }

    echo "Voucher hotspot active-mode restore complete: {$count}\n";
    logx("Voucher hotspot active-mode restore complete: {$count}");

}catch(Exception $e){
    echo "FAILED: ".$e->getMessage()."\n";
    logx("FAILED: ".$e->getMessage());
}
