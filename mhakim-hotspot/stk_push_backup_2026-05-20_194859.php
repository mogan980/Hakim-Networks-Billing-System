<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/config/database.php";

function out($data){ echo json_encode($data); exit; }

$phone = preg_replace('/\D/', '', $_POST["phone"] ?? "");
$amount = (int)($_POST["amount"] ?? 0);
$package_id = (int)($_POST["package_id"] ?? 0);
$client_ip = $_SERVER["REMOTE_ADDR"] ?? "";

if(substr($phone,0,1)==="0") $phone = "254".substr($phone,1);
if(substr($phone,0,3)!=="254") out(["ok"=>false,"error"=>"Invalid phone number"]);
if($amount < 1) out(["ok"=>false,"error"=>"Invalid amount"]);

$settings = $pdo->query("SELECT * FROM mpesa_settings WHERE status='active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if(!$settings) out(["ok"=>false,"error"=>"M-Pesa settings missing"]);

$baseUrl = $settings["environment"] === "production"
    ? "https://api.safaricom.co.ke"
    : "https://sandbox.safaricom.co.ke";

$credentials = base64_encode($settings["consumer_key"].":".$settings["consumer_secret"]);

$ch = curl_init($baseUrl."/oauth/v1/generate?grant_type=client_credentials");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Authorization: Basic ".$credentials],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    CURLOPT_TIMEOUT => 60
]);

$tokenRaw = curl_exec($ch);
if(curl_errno($ch)) out(["ok"=>false,"stage"=>"token","error"=>curl_error($ch)]);

$token = json_decode($tokenRaw,true)["access_token"] ?? "";
if(!$token) out(["ok"=>false,"stage"=>"token","error"=>$tokenRaw]);

$timestamp = date("YmdHis");
$password = base64_encode($settings["shortcode"].$settings["passkey"].$timestamp);

$partyB = $settings["till_number"] ?: ($settings["store_number"] ?: $settings["shortcode"]);

$payload = [
    "BusinessShortCode" => $settings["shortcode"],
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => $settings["transaction_type"],
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $partyB,
    "PhoneNumber" => $phone,
    "CallBackURL" => $settings["callback_url"],
    "AccountReference" => "HakimNet",
    "TransactionDesc" => "Hakim Networks Internet"
];

$curl = curl_init($baseUrl."/mpesa/stkpush/v1/processrequest");
curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => ["Authorization: Bearer ".$token, "Content-Type: application/json"],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($curl);
if(curl_errno($curl)) out(["ok"=>false,"stage"=>"stk","error"=>curl_error($curl)]);

$data = json_decode($response,true);

if(($data["ResponseCode"] ?? "") === "0"){
    $checkout = $data["CheckoutRequestID"];
    $stmt = $pdo->prepare("
        INSERT INTO hotspot_payments
        (phone, amount, package_id, username, password, checkout_request_id, status, client_ip)
        VALUES (?,?,?,?,?,?, 'pending', ?)
    ");
    $stmt->execute([$phone,$amount,$package_id,$phone,$phone,$checkout,$client_ip]);

    out([
        "ok"=>true,
        "ResponseCode"=>"0",
        "CheckoutRequestID"=>$checkout,
        "CustomerMessage"=>$data["CustomerMessage"] ?? "STK sent"
    ]);
}

out(["ok"=>false,"error"=>$data["errorMessage"] ?? $response]);
