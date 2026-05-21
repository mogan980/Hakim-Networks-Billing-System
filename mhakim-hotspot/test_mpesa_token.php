<?php
require_once __DIR__ . "/mpesa_config.php";

$credentials = base64_encode($consumerKey . ":" . $consumerSecret);

$ch = curl_init($baseUrl . "/oauth/v1/generate?grant_type=client_credentials");

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . $credentials
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("CURL ERROR: " . curl_error($ch) . PHP_EOL);
}

curl_close($ch);

echo $response . PHP_EOL;
