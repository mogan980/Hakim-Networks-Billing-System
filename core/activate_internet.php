<?php
require_once __DIR__ . "/../vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Query;

function activateInternet($clientIp, $packageName, $downSpeed, $upSpeed, $hours, $commentTag, $loginUser = null, $loginPass = null)
{
    $config = require __DIR__ . "/../config/mikrotik.php";

    try {
        $client = new Client([
            "host" => $config["host"],
            "user" => $config["user"],
            "pass" => $config["pass"],
            "port" => $config["port"]
        ]);

        $expires = date("Y-m-d H:i:s", time() + ($hours * 3600));
        $queueName = "HN-" . str_replace(".", "-", $clientIp);
        $profileName = "HN-" . str_replace(["M","/"], ["","-"], $downSpeed . "-" . $upSpeed);
        $rateLimit = $downSpeed . "/" . $upSpeed;

        if (!$loginUser) $loginUser = "HN-" . str_replace(".", "-", $clientIp);
        if (!$loginPass) $loginPass = substr(md5($clientIp . time()), 0, 8);

        // Get MAC
        $hosts = $client->query(
            (new Query("/ip/hotspot/host/print"))
                ->where("address", $clientIp)
        )->read();

        $mac = $hosts[0]["mac-address"] ?? "";

        // Remove old bypass bindings for this IP/MAC
        $bindings = $client->query(new Query("/ip/hotspot/ip-binding/print"))->read();
        foreach ($bindings as $b) {
            $sameIp = isset($b["address"]) && $b["address"] === $clientIp;
            $sameMac = $mac && isset($b["mac-address"]) && strtoupper($b["mac-address"]) === strtoupper($mac);

            if (($sameIp || $sameMac) && !empty($b[".id"])) {
                $client->query(
                    (new Query("/ip/hotspot/ip-binding/remove"))
                        ->equal(".id", $b[".id"])
                )->read();
            }
        }

        // Remove old active sessions
        $active = $client->query(
            (new Query("/ip/hotspot/active/print"))
                ->where("address", $clientIp)
        )->read();

        foreach ($active as $a) {
            if (!empty($a[".id"])) {
                $client->query(
                    (new Query("/ip/hotspot/active/remove"))
                        ->equal(".id", $a[".id"])
                )->read();
            }
        }

        // Create/update profile
        $profiles = $client->query(
            (new Query("/ip/hotspot/user/profile/print"))
                ->where("name", $profileName)
        )->read();

        if (empty($profiles)) {
            $client->query(
                (new Query("/ip/hotspot/user/profile/add"))
                    ->equal("name", $profileName)
                    ->equal("rate-limit", $rateLimit)
                    ->equal("shared-users", "1")
            )->read();
        }

        // Remove existing user
        $users = $client->query(
            (new Query("/ip/hotspot/user/print"))
                ->where("name", $loginUser)
        )->read();

        foreach ($users as $u) {
            if (!empty($u[".id"])) {
                $client->query(
                    (new Query("/ip/hotspot/user/remove"))
                        ->equal(".id", $u[".id"])
                )->read();
            }
        }

        // Add hotspot user
        $client->query(
            (new Query("/ip/hotspot/user/add"))
                ->equal("name", $loginUser)
                ->equal("password", $loginPass)
                ->equal("profile", $profileName)
                ->equal("limit-uptime", ((int)$hours) . "h")
                ->equal("comment", $commentTag . " | " . $packageName . " | expires:" . $expires)
        )->read();

        // Force login into hotspot active
        $loginQuery = (new Query("/ip/hotspot/active/login"))
            ->equal("user", $loginUser)
            ->equal("password", $loginPass)
            ->equal("ip", $clientIp);

        if ($mac) {
            $loginQuery->equal("mac-address", $mac);
        }

        $client->query($loginQuery)->read();

        // Remove old queue
        $queues = $client->query(
            (new Query("/queue/simple/print"))
                ->where("name", $queueName)
        )->read();

        foreach ($queues as $q) {
            if (!empty($q[".id"])) {
                $client->query(
                    (new Query("/queue/simple/remove"))
                        ->equal(".id", $q[".id"])
                )->read();
            }
        }

        // Add queue
        $client->query(
            (new Query("/queue/simple/add"))
                ->equal("name", $queueName)
                ->equal("target", $clientIp . "/32")
                ->equal("max-limit", $upSpeed . "/" . $downSpeed)
                ->equal("comment", $commentTag . " | expires:" . $expires)
        )->read();

        return [
            "success" => true,
            "message" => "Internet activated and user logged in",
            "ip" => $clientIp,
            "mac" => $mac,
            "username" => $loginUser,
            "password" => $loginPass,
            "expires" => $expires
        ];

    } catch (Exception $e) {
        return [
            "success" => false,
            "message" => $e->getMessage()
        ];
    }
}
