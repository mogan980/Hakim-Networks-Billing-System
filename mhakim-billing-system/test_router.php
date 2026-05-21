<?php

require 'vendor/autoload.php';

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$config = new Config([
    'host' => '192.168.88.1',
    'user' => 'mhakimapi',
    'pass' => '12345678',
    'port' => 8728,
]);

try {

    $client = new Client($config);

    echo "<h1 style='color:green'>MikroTik Connected Successfully ✅</h1>";

    $query = new Query('/system/identity/print');

    $response = $client->query($query)->read();

    echo "<pre>";
    print_r($response);
    echo "</pre>";

} catch (Exception $e) {

    echo "<h1 style='color:red'>Connection Failed ❌</h1>";
    echo $e->getMessage();
}
?>
