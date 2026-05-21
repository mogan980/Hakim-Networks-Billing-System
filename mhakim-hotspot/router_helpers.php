<?php
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function getRouterApi($pdo) {
    $settings = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        throw new Exception("MikroTik settings missing");
    }

    return new Client(new Config([
        "host" => $settings["router_ip"],
        "user" => $settings["router_username"],
        "pass" => $settings["router_password"],
        "port" => (int)$settings["api_port"],
    ]));
}

function syncHotspotUserAndQueue($api, $username, $password, $speedDown, $speedUp) {
    $foundUser = $api->query(
        (new Query('/ip/hotspot/user/print'))->where('name', $username)
    )->read();

    if (!empty($foundUser)) {
        $api->query(
            (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $foundUser[0][".id"])
                ->equal('password', $password)
                ->equal('profile', 'default')
                ->equal('disabled', 'no')
        )->read();
    } else {
        $api->query(
            (new Query('/ip/hotspot/user/add'))
                ->equal('name', $username)
                ->equal('password', $password)
                ->equal('profile', 'default')
        )->read();
    }

    $queueName = "MH-" . $username;
    $maxLimit = $speedUp . "/" . $speedDown;

    $foundQueue = $api->query(
        (new Query('/queue/simple/print'))->where('name', $queueName)
    )->read();

    if (!empty($foundQueue)) {
        $api->query(
            (new Query('/queue/simple/set'))
                ->equal('.id', $foundQueue[0][".id"])
                ->equal('max-limit', $maxLimit)
                ->equal('disabled', 'no')
        )->read();
    } else {
        $api->query(
            (new Query('/queue/simple/add'))
                ->equal('name', $queueName)
                ->equal('max-limit', $maxLimit)
        )->read();
    }
}

function expireRouterUserAndQueue($api, $username) {
    $activeSessions = $api->query(
        (new Query('/ip/hotspot/active/print'))->where('user', $username)
    )->read();

    foreach ($activeSessions as $session) {
        if (isset($session[".id"])) {
            $api->query(
                (new Query('/ip/hotspot/active/remove'))->equal('.id', $session[".id"])
            )->read();
        }
    }

    $foundUsers = $api->query(
        (new Query('/ip/hotspot/user/print'))->where('name', $username)
    )->read();

    foreach ($foundUsers as $user) {
        if (isset($user[".id"])) {
            $api->query(
                (new Query('/ip/hotspot/user/disable'))->equal('.id', $user[".id"])
            )->read();
        }
    }

    foreach (["MH-" . $username, "VH-" . $username] as $queueName) {
        $queues = $api->query(
            (new Query('/queue/simple/print'))->where('name', $queueName)
        )->read();

        foreach ($queues as $queue) {
            if (isset($queue[".id"])) {
                $api->query(
                    (new Query('/queue/simple/remove'))->equal('.id', $queue[".id"])
                )->read();
            }
        }
    }
}
