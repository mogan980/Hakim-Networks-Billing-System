<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Query;

$pdo->exec("SET time_zone = '+03:00'");

$clientIp = $_GET["ip"] ?? $_POST["client_ip"] ?? ($_SERVER["REMOTE_ADDR"] ?? "");
$clientIp = trim($clientIp);

if(!$clientIp){
    die("Missing client IP");
}

$stmt = $pdo->prepare("
    SELECT *
    FROM payments
    WHERE client_ip=?
    AND status='paid'
    AND expires_at IS NOT NULL
    AND expires_at > NOW()
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute([$clientIp]);
$pay = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$pay){
    header("Location: index.php?error=no_active_payment");
    exit;
}

try{
    $cfg = require __DIR__ . "/config/mikrotik.php";

    $mt = new Client([
        "host"=>$cfg["host"],
        "user"=>$cfg["user"],
        "pass"=>$cfg["pass"],
        "port"=>$cfg["port"],
        "timeout"=>5
    ]);

    $queueExists = false;
    foreach($mt->query(new Query("/queue/simple/print"))->read() as $q){
        if(str_contains($q["target"] ?? "", $clientIp)){
            $queueExists = true;
            break;
        }
    }

    if(!$queueExists){
        $mt->query(
            (new Query("/queue/simple/add"))
            ->equal("name","HN-STK-".$clientIp)
            ->equal("target",$clientIp."/32")
            ->equal("max-limit","2M/6M")
            ->equal("comment","STK active until ".$pay["expires_at"])
        )->read();
    }

    $bindingExists = false;
    foreach($mt->query(new Query("/ip/hotspot/ip-binding/print"))->read() as $b){
        if(($b["address"] ?? "") === $clientIp){
            $bindingExists = true;
            break;
        }
    }

    if(!$bindingExists){
        $mt->query(
            (new Query("/ip/hotspot/ip-binding/add"))
            ->equal("address",$clientIp)
            ->equal("type","bypassed")
            ->equal("comment","STK paid expires ".$pay["expires_at"])
        )->read();
    }

    header("Location: http://neverssl.com/");
    exit;

}catch(Exception $e){
    die("Activation failed: ".$e->getMessage());
}
