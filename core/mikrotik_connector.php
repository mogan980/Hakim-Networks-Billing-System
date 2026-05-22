<?php

require_once __DIR__ . "/../vendor/autoload.php";

use RouterOS\Client;

function connectMikrotikStable() {

    $config = require __DIR__ . "/../config/mikrotik.php";

    $hosts = [
        "192.168.88.1",
        "10.10.10.1"
    ];

    $lastError = "Unknown error";

    foreach ($hosts as $host) {

        try {

            $client = new Client([
                "host" => $host,
                "user" => $config["user"],
                "pass" => $config["pass"],
                "port" => $config["port"],
                "timeout" => 4
            ]);

            return $client;

        } catch(Exception $e) {

            $lastError = $e->getMessage();
        }
    }

    throw new Exception($lastError);
}
