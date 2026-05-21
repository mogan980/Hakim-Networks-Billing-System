<?php
header("Content-Type: application/json");

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function td($v){ return "<td>".htmlspecialchars((string)$v)."</td>"; }

try{
    $module = $_GET["module"] ?? "";

    $router = $pdo->query("
        SELECT * FROM routers 
        WHERE status='active' 
        ORDER BY id DESC 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if(!$router){
        throw new Exception("No active router selected");
    }

    $client = new Client(new Config([
        "host"=>$router["router_ip"],
        "user"=>$router["router_username"],
        "pass"=>$router["router_password"],
        "port"=>(int)$router["api_port"],
        "timeout"=>5
    ]));

    $identity = $client->query(new Query("/system/identity/print"))->read()[0]["name"] ?? "MikroTik";

    $html = "";
    $count = 0;

    if($module === "hotspot_active"){
        $rows = $client->query(new Query("/ip/hotspot/active/print"))->read();
        $html = "<tr><th>User</th><th>IP Address</th><th>MAC</th><th>Uptime</th><th>Bytes In</th><th>Bytes Out</th><th>Actions</th></tr>";
        foreach($rows as $r){
            $user = $r["user"] ?? "";
$id = $r[".id"] ?? "";
$actions = "<button onclick=\"mtAction('kick_hotspot','$id','')\">Kick</button> <button onclick=\"mtAction('disable_hotspot_user','','$user')\">Suspend</button>";
$html .= "<tr>".td($user).td($r["address"] ?? "-").td($r["mac-address"] ?? "-").td($r["uptime"] ?? "-").td($r["bytes-in"] ?? "0").td($r["bytes-out"] ?? "0")."<td>$actions</td></tr>";
        }
        $count = count($rows);
    }

    elseif($module === "queues"){
        $rows = $client->query(new Query("/queue/simple/print"))->read();
        $html = "<tr><th>Name</th><th>Target</th><th>Max Limit</th><th>Disabled</th><th>Bytes</th><th>Actions</th></tr>";
        foreach($rows as $r){
            $id = $r[".id"] ?? "";
$disabled = $r["disabled"] ?? "false";
$toggle = ($disabled === "true") ? "enable_queue" : "disable_queue";
$toggleText = ($disabled === "true") ? "Enable" : "Disable";
$actions = "<button onclick=\"mtAction('$toggle','$id','')\">$toggleText</button> <button onclick=\"mtAction('delete_queue','$id','')\">Delete</button>";
$html .= "<tr>".td($r["name"] ?? "-").td($r["target"] ?? "-").td($r["max-limit"] ?? "-").td($disabled).td($r["bytes"] ?? "-")."<td>$actions</td></tr>";
        }
        $count = count($rows);
    }

    elseif($module === "health"){
        $res = $client->query(new Query("/system/resource/print"))->read()[0] ?? [];
        $html = "<tr><th>Metric</th><th>Value</th><th>Status</th></tr>";
        $items = [
            "CPU Load" => ($res["cpu-load"] ?? 0)."%",
            "Free Memory" => $res["free-memory"] ?? "-",
            "RouterOS Version" => $res["version"] ?? "-",
            "Uptime" => $res["uptime"] ?? "-"
        ];
        foreach($items as $k=>$v){
            $html .= "<tr>".td($k).td($v).td("OK")."</tr>";
        }
        $count = count($items);
    }

    elseif($module === "pppoe_active"){
        $rows = $client->query(new Query("/ppp/active/print"))->read();
        $html = "<tr><th>User</th><th>IP Address</th><th>Caller ID</th><th>Uptime</th><th>Service</th><th>Actions</th></tr>";
        foreach($rows as $r){
            $id = $r[".id"] ?? "";
$actions = "<button onclick=\"mtAction('disconnect_pppoe','$id','')\">Disconnect</button>";
$html .= "<tr>".td($r["name"] ?? "-").td($r["address"] ?? "-").td($r["caller-id"] ?? "-").td($r["uptime"] ?? "-").td($r["service"] ?? "-")."<td>$actions</td></tr>";
        }
        $count = count($rows);
    }

    elseif($module === "traffic"){
        $traffic = $client->query((new Query("/interface/monitor-traffic"))->equal("interface","bridge")->equal("once",""))->read()[0] ?? [];
        $rx = round(((float)($traffic["rx-bits-per-second"] ?? 0))/1000000,2)." Mbps";
        $tx = round(((float)($traffic["tx-bits-per-second"] ?? 0))/1000000,2)." Mbps";

        $html = "<tr><th>Router</th><th>Download RX</th><th>Upload TX</th><th>Updated</th></tr>";
        $html .= "<tr>".td($identity).td($rx).td($tx).td(date("H:i:s"))."</tr>";
        $count = 1;
    }

    elseif($module === "topology"){
        $leases = $client->query(new Query("/ip/dhcp-server/lease/print"))->read();
        $html = "<tr><th>Device</th><th>IP Address</th><th>MAC</th><th>Type</th><th>Status</th></tr>";
        foreach($leases as $r){
            $html .= "<tr>".td($r["host-name"] ?? "Unknown").td($r["address"] ?? "-").td($r["mac-address"] ?? "-").td("DHCP Device").td($r["status"] ?? "-")."</tr>";
        }
        $count = count($leases);
    }

    else{
        throw new Exception("Unknown module");
    }

    echo json_encode([
        "status"=>"online",
        "identity"=>$identity,
        "count"=>$count,
        "html"=>$html,
        "updated_at"=>date("H:i:s")
    ]);

}catch(Exception $e){
    echo json_encode([
        "status"=>"offline",
        "identity"=>"-",
        "count"=>0,
        "html"=>"<tr><td colspan='10'>".$e->getMessage()."</td></tr>",
        "updated_at"=>date("H:i:s")
    ]);
}
