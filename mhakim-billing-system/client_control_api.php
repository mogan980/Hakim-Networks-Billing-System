<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

header("Content-Type: application/json");

function respond($ok, $message){
    echo json_encode(["success"=>$ok, "message"=>$message]);
    exit;
}

$action = $_POST["action"] ?? "";
$user = $_POST["user"] ?? "";
$ip = $_POST["ip"] ?? "";

try {
    $router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$router) respond(false, "Router not configured");

    $client = new Client(new Config([
        "host" => $router["router_ip"],
        "user" => $router["router_user"],
        "pass" => $router["router_pass"],
        "port" => (int)$router["router_port"],
        "timeout" => 5,
        "attempts" => 1
    ]));

    if ($action === "kick_hotspot") {
        if (!$user) respond(false, "Missing user");

        $active = $client->query(
            (new Query("/ip/hotspot/active/print"))
            ->where("user", $user)
        )->read();

        foreach ($active as $a) {
            if (!empty($a[".id"])) {
                $client->query(
                    (new Query("/ip/hotspot/active/remove"))
                    ->equal(".id", $a[".id"])
                )->read();
            }
        }

        $stmt = $pdo->prepare("INSERT INTO client_session_actions (client_user, client_ip, action, result) VALUES (?,?,?,?)");
        $stmt->execute([$user, $ip, "kick_hotspot", "Hotspot user disconnected"]);

        respond(true, "Hotspot user disconnected");
    }

    if ($action === "disable_queue") {
        if (!$user) respond(false, "Missing queue/user");

        $queues = $client->query(
            (new Query("/queue/simple/print"))
            ->where("name", $user)
        )->read();

        foreach ($queues as $q) {
            if (!empty($q[".id"])) {
                $client->query(
                    (new Query("/queue/simple/set"))
                    ->equal(".id", $q[".id"])
                    ->equal("disabled", "yes")
                )->read();
            }
        }

        $stmt = $pdo->prepare("INSERT INTO client_session_actions (client_user, client_ip, action, result) VALUES (?,?,?,?)");
        $stmt->execute([$user, $ip, "pause_queue", "Queue disabled"]);

        respond(true, "Client queue paused");
    }

    if ($action === "enable_queue") {
        if (!$user) respond(false, "Missing queue/user");

        $queues = $client->query(
            (new Query("/queue/simple/print"))
            ->where("name", $user)
        )->read();

        foreach ($queues as $q) {
            if (!empty($q[".id"])) {
                $client->query(
                    (new Query("/queue/simple/set"))
                    ->equal(".id", $q[".id"])
                    ->equal("disabled", "no")
                )->read();
            }
        }

        $stmt = $pdo->prepare("INSERT INTO client_session_actions (client_user, client_ip, action, result) VALUES (?,?,?,?)");
        $stmt->execute([$user, $ip, "resume_queue", "Queue enabled"]);

        respond(true, "Client queue resumed");
    }

    respond(false, "Invalid action");

} catch (Exception $e) {
    respond(false, $e->getMessage());
}
