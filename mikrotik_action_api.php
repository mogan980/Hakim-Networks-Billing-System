<?php
header("Content-Type: application/json");

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

try{
    $action = $_POST["action"] ?? "";
    $id = $_POST["id"] ?? "";
    $name = $_POST["name"] ?? "";

    $router = $pdo->query("
        SELECT * FROM routers 
        WHERE status='active' 
        ORDER BY id DESC 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if(!$router) throw new Exception("No active router selected");

    $client = new Client(new Config([
        "host"=>$router["router_ip"],
        "user"=>$router["router_username"],
        "pass"=>$router["router_password"],
        "port"=>(int)$router["api_port"],
        "timeout"=>5
    ]));

    if($action === "kick_hotspot"){
        if(!$id) throw new Exception("Missing hotspot active ID");
        $client->query((new Query("/ip/hotspot/active/remove"))->equal(".id",$id))->read();
        echo json_encode(["ok"=>true,"message"=>"Hotspot user kicked"]);
        exit;
    }

    if($action === "disable_hotspot_user"){
        if(!$name) throw new Exception("Missing username");
        $users = $client->query((new Query("/ip/hotspot/user/print"))->where("name",$name))->read();

        foreach($users as $u){
            if(isset($u[".id"])){
                $client->query((new Query("/ip/hotspot/user/set"))->equal(".id",$u[".id"])->equal("disabled","yes"))->read();
            }
        }

        echo json_encode(["ok"=>true,"message"=>"Hotspot user disabled"]);
        exit;
    }

    if($action === "enable_hotspot_user"){
        if(!$name) throw new Exception("Missing username");
        $users = $client->query((new Query("/ip/hotspot/user/print"))->where("name",$name))->read();

        foreach($users as $u){
            if(isset($u[".id"])){
                $client->query((new Query("/ip/hotspot/user/set"))->equal(".id",$u[".id"])->equal("disabled","no"))->read();
            }
        }

        echo json_encode(["ok"=>true,"message"=>"Hotspot user enabled"]);
        exit;
    }

    if($action === "delete_queue"){
        if(!$id) throw new Exception("Missing queue ID");
        $client->query((new Query("/queue/simple/remove"))->equal(".id",$id))->read();
        echo json_encode(["ok"=>true,"message"=>"Queue deleted"]);
        exit;
    }

    if($action === "disable_queue"){
        if(!$id) throw new Exception("Missing queue ID");
        $client->query((new Query("/queue/simple/set"))->equal(".id",$id)->equal("disabled","yes"))->read();
        echo json_encode(["ok"=>true,"message"=>"Queue disabled"]);
        exit;
    }

    if($action === "enable_queue"){
        if(!$id) throw new Exception("Missing queue ID");
        $client->query((new Query("/queue/simple/set"))->equal(".id",$id)->equal("disabled","no"))->read();
        echo json_encode(["ok"=>true,"message"=>"Queue enabled"]);
        exit;
    }

    if($action === "disconnect_pppoe"){
        if(!$id) throw new Exception("Missing PPPoE ID");
        $client->query((new Query("/ppp/active/remove"))->equal(".id",$id))->read();
        echo json_encode(["ok"=>true,"message"=>"PPPoE user disconnected"]);
        exit;
    }

    throw new Exception("Unknown action");

}catch(Exception $e){
    echo json_encode(["ok"=>false,"message"=>$e->getMessage()]);
}
