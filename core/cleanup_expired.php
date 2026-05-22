<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use RouterOS\Client;
use RouterOS\Query;

$logFile = "/tmp/hakim_cleanup.log";

function logMsg($m){
    global $logFile;
    file_put_contents($logFile,"[".date("Y-m-d H:i:s")."] ".$m.PHP_EOL,FILE_APPEND);
}

$config = require __DIR__ . '/../config/mikrotik.php';

$hosts = [
    $config["host"] ?? "192.168.88.1",
    "192.168.88.1",
    "10.10.10.1"
];

$API = null;

foreach(array_unique($hosts) as $host){

    try{

        $API = new Client([
            "host" => $host,
            "user" => $config["user"],
            "pass" => $config["pass"],
            "port" => $config["port"],
            "timeout" => 4
        ]);

        logMsg("Connected to MikroTik: ".$host);
        break;

    }catch(Exception $e){

        logMsg("Host failed ".$host." => ".$e->getMessage());
    }
}

if(!$API){
    logMsg("Expiry engine aborted: MikroTik unreachable");
    exit;
}

try{

    $stmt = $pdo->prepare("
        SELECT * FROM smart_vouchers
        WHERE status='used'
        AND expires_at IS NOT NULL
        AND expires_at <= NOW()
    ");

    $stmt->execute();

    $expired = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($expired as $v){

        $ip = trim($v["ip_address"] ?? "");
        $voucher = trim($v["voucher_code"] ?? "");

        if(!$ip){
            continue;
        }

        try{

            /* remove queue */
            $queues = $API->query(
                (new Query('/queue/simple/print'))
                ->where('target',$ip."/32")
            )->read();

            foreach($queues as $q){

                if(isset($q['.id'])){
                    $API->query(
                        (new Query('/queue/simple/remove'))
                        ->equal('.id',$q['.id'])
                    )->read();

                    logMsg("Removed queue for ".$ip);
                }
            }

            /* hotspot active disconnect */
            $active = $API->query(
                (new Query('/ip/hotspot/active/print'))
                ->where('address',$ip)
            )->read();

            foreach($active as $a){

                if(isset($a['.id'])){
                    $API->query(
                        (new Query('/ip/hotspot/active/remove'))
                        ->equal('.id',$a['.id'])
                    )->read();

                    logMsg("Disconnected hotspot user ".$ip);
                }
            }

            /* archive expired */
            $upd = $pdo->prepare("
                UPDATE smart_vouchers
                SET status='expired'
                WHERE id=?
            ");

            $upd->execute([$v["id"]]);

            logMsg("Expired voucher archived: ".$voucher);

        }catch(Exception $e){

            logMsg("Voucher cleanup failed ".$voucher." => ".$e->getMessage());
        }
    }

    logMsg("Expiry cleanup completed");

}catch(Exception $e){

    logMsg("Engine fatal error => ".$e->getMessage());
}
