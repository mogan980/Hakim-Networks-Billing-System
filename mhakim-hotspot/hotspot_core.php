<?php
$autoload1="/var/www/html/mhakim-billing-system/vendor/autoload.php";
$autoload2="/var/www/html/vendor/autoload.php";
if(file_exists($autoload1)){require_once $autoload1;}
elseif(file_exists($autoload2)){require_once $autoload2;}
else{die("Vendor autoload missing");}

require_once "/var/www/html/mhakim-billing-system/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function out_json($a){
    header("Content-Type: application/json");
    echo json_encode($a);
    exit;
}

function router_client(){
    global $pdo;
    $r=$pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if(!$r){throw new Exception("Router not configured");}

    return new Client(new Config([
        "host"=>$r["router_ip"],
        "user"=>$r["router_user"],
        "pass"=>$r["router_pass"],
        "port"=>(int)($r["router_port"] ?: 8728),
        "timeout"=>8,
        "attempts"=>1
    ]));
}

function clean_login($v){
    return preg_replace("/[^0-9A-Za-z_-]/","",(string)$v);
}

function hours_to_mikrotik($hours){
    $hours=(float)$hours;
    if($hours<=0){return "1h";}
    if($hours<24){return intval(ceil($hours))."h";}
    $days=floor($hours/24);
    $rem=$hours%24;
    return $days."d".($rem ? intval($rem)."h" : "");
}

function package_info($package_id=null){
    global $pdo;
    if($package_id){
        $st=$pdo->prepare("SELECT * FROM packages WHERE id=? LIMIT 1");
        $st->execute([$package_id]);
        $p=$st->fetch(PDO::FETCH_ASSOC);
        if($p){return $p;}
    }
    return [
        "speed_down"=>"8M",
        "speed_up"=>"2M",
        "duration_hours"=>1
    ];
}

function ensure_profile($api,$profile,$down="8M",$up="2M"){
    $found=$api->query((new Query("/ip/hotspot/user/profile/print"))->where("name",$profile))->read();
    if(!empty($found)){return $profile;}

    try{
        $api->query(
            (new Query("/ip/hotspot/user/profile/add"))
            ->equal("name",$profile)
            ->equal("rate-limit",$up."/".$down)
        )->read();
        return $profile;
    }catch(Exception $e){
        return "default";
    }
}

function activate_hotspot($username,$password,$package_id=null,$comment="Hakim Hotspot"){
    $api=router_client();

    $username=clean_login($username);
    $password=clean_login($password ?: substr($username,-4));

    if(!$username || !$password){
        throw new Exception("Invalid username/password");
    }

    $pkg=package_info($package_id);
    $down=$pkg["speed_down"] ?? "8M";
    $up=$pkg["speed_up"] ?? "2M";
    $limit=hours_to_mikrotik($pkg["duration_hours"] ?? 1);

    $profile=ensure_profile($api,"HAKIM-AUTO",$down,$up);

    $exists=$api->query(
        (new Query("/ip/hotspot/user/print"))
        ->where("name",$username)
    )->read();

    if(!empty($exists)){
        foreach($exists as $u){
            if(!empty($u[".id"])){
                $api->query(
                    (new Query("/ip/hotspot/user/set"))
                    ->equal(".id",$u[".id"])
                    ->equal("password",$password)
                    ->equal("profile",$profile)
                    ->equal("limit-uptime",$limit)
                    ->equal("disabled","no")
                    ->equal("comment",$comment)
                )->read();
            }
        }
    }else{
        $api->query(
            (new Query("/ip/hotspot/user/add"))
            ->equal("name",$username)
            ->equal("password",$password)
            ->equal("profile",$profile)
            ->equal("limit-uptime",$limit)
            ->equal("disabled","no")
            ->equal("comment",$comment)
        )->read();
    }

    return [
        "username"=>$username,
        "password"=>$password,
        "login_url"=>"http://192.168.88.1/login",
        "dst"=>"http://neverssl.com/"
    ];
}
