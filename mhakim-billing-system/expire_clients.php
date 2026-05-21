<?php
require_once "/var/www/html/mhakim-billing-system/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

date_default_timezone_set("Africa/Nairobi");

$logFile = "/var/www/html/mhakim-billing-system/expire_clients.log";

function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $msg . PHP_EOL, FILE_APPEND);
}

try {
    $expired = $pdo->query("
        SELECT *
        FROM clients
        WHERE status='active'
        AND expires_at IS NOT NULL
        AND expires_at <= NOW()
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (!$expired) {
        logMsg("No expired clients found.");
        exit;
    }

    $settings = $pdo->query("
        SELECT *
        FROM mikrotik_settings
        ORDER BY id DESC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        throw new Exception("No MikroTik settings found.");
    }

    $api = new Client(new Config([
        "host" => $settings["router_ip"],
        "user" => $settings["router_username"],
        "pass" => $settings["router_password"],
        "port" => (int)$settings["api_port"],
    ]));

    foreach ($expired as $client) {
        $username = $client["username"];

        if (!$username) {
            continue;
        }

        $foundUser = $api->query(
            (new Query("/ip/hotspot/user/print"))
                ->where("name", $username)
        )->read();

        if (!empty($foundUser)) {
            $api->query(
                (new Query("/ip/hotspot/user/set"))
                    ->equal(".id", $foundUser[0][".id"])
                    ->equal("disabled", "yes")
            )->read();
        }

        // Remove active hotspot session
$activeSessions = $api->query(
    (new Query("/ip/hotspot/active/print"))
        ->where("user", $username)
)->read();

foreach ($activeSessions as $session) {
    if (!empty($session[".id"])) {
        $api->query(
            (new Query("/ip/hotspot/active/remove"))
                ->equal(".id", $session[".id"])
        )->read();
    }
}

// Remove hotspot cookies so phone cannot auto-login again
$cookies = $api->query(
    (new Query("/ip/hotspot/cookie/print"))
        ->where("user", $username)
)->read();

foreach ($cookies as $cookie) {
    if (!empty($cookie[".id"])) {
        $api->query(
            (new Query("/ip/hotspot/cookie/remove"))
                ->equal(".id", $cookie[".id"])
        )->read();
    }
}

// Disable simple queue for expired client
$queueName = "MH-" . $username;

$queues = $api->query(
    (new Query("/queue/simple/print"))
        ->where("name", $queueName)
)->read();

foreach ($queues as $queue) {
    if (!empty($queue[".id"])) {
        $api->query(
            (new Query("/queue/simple/set"))
                ->equal(".id", $queue[".id"])
                ->equal("disabled", "yes")
        )->read();
    }
}

        $stmt = $pdo->prepare("
            UPDATE clients
            SET status='expired'
            WHERE id=?
        ");
        $stmt->execute([$client["id"]]);

        logMsg("Expired client disabled: " . $username);
if (($client["connection_type"] ?? "hotspot") === "pppoe") {
    $pppSecrets = $api->query(
        (new Query("/ppp/secret/print"))
            ->where("name", $username)
    )->read();

    foreach ($pppSecrets as $secret) {
        if (!empty($secret[".id"])) {
            $api->query(
                (new Query("/ppp/secret/set"))
                    ->equal(".id", $secret[".id"])
                    ->equal("disabled", "yes")
            )->read();
        }
    }

    $pppActive = $api->query(
        (new Query("/ppp/active/print"))
            ->where("name", $username)
    )->read();

    foreach ($pppActive as $session) {
        if (!empty($session[".id"])) {
            $api->query(
                (new Query("/ppp/active/remove"))
                    ->equal(".id", $session[".id"])
            )->read();
        }
    }
}
    }

} catch (Exception $e) {
    logMsg("ERROR: " . $e->getMessage());
}
