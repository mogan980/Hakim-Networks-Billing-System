<?php
ini_set("display_errors",1); error_reporting(E_ALL);
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function fail($msg){
    echo "<body style='background:#5b0808;color:white;font-family:Arial;text-align:center;padding-top:80px'>";
    echo "<h1>Voucher Activation Failed</h1>";
    echo "<p>" . htmlspecialchars($msg) . "</p>";
    echo "<a style='color:white' href='index.php'>Go Back</a>";
    echo "</body>";
    exit;
}

function ok($msg){
    echo "<body style='background:#064e3b;color:white;font-family:Arial;text-align:center;padding-top:80px'>";
    echo "<h1>Voucher Activated Successfully</h1>";
    echo "<p>" . htmlspecialchars($msg) . "</p>";
    echo "<p>You can now browse the internet.</p>";
    echo "</body>";
    exit;
}

$code = strtoupper(trim($_POST["voucher_code"] ?? $_GET["voucher_code"] ?? ""));

if ($code === "") {
    fail("Voucher code is required.");
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            v.id,
            v.voucher_code,
            v.plan_id,
            v.status,
            p.name AS plan_name,
            p.speed_limit,
            p.validity_days
        FROM vouchers v
        LEFT JOIN plans p ON p.id = v.plan_id
        WHERE v.voucher_code = ?
        LIMIT 1
    ");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$voucher) {
        fail("Invalid voucher.");
    }

    if ($voucher["status"] !== "unused") {
        fail("Voucher already used or expired.");
    }

    $speed = $voucher["speed_limit"] ?: "8M/2M";
    $down = trim(explode("/", $speed)[0] ?? "8M");
    $up = trim(explode("/", $speed)[1] ?? "2M");

    $profile = "VOUCHER-" . str_replace("M", "", $down) . "-" . str_replace("M", "", $up);

    $durationDays = (int)($voucher["validity_days"] ?? 1);
    if ($durationDays < 1) $durationDays = 1;

    $limitUptime = ($durationDays * 24) . "h";

    $settings = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        fail("MikroTik settings missing.");
    }

    $config = new Config([
        "host" => $settings["router_ip"],
        "user" => $settings["router_username"],
        "pass" => $settings["router_password"],
        "port" => (int)$settings["api_port"],
        "timeout" => 10
    ]);

    $client = new Client($config);

    // Remove old same voucher user if exists
    $existing = $client->query(
        (new Query("/ip/hotspot/user/print"))
            ->where("name", $code)
    )->read();

    foreach ($existing as $u) {
        if (isset($u[".id"])) {
            $client->query(
                (new Query("/ip/hotspot/user/remove"))
                    ->equal(".id", $u[".id"])
            )->read();
        }
    }

    // Add fresh MikroTik hotspot user
    $client->query(
        (new Query("/ip/hotspot/user/add"))
            ->equal("name", $code)
            ->equal("password", $code)
            ->equal("profile", $profile)
            ->equal("limit-uptime", $limitUptime)
            ->equal("comment", "voucher:" . $code)
    )->read();

    // Mark voucher used
    $update = $pdo->prepare("
        UPDATE vouchers 
        SET status='used'
        WHERE id=?
    ");
    $update->execute([$voucher["id"]]);

    header("Location: voucher_success.php?u=" . urlencode($code) . "&p=" . urlencode($code)); exit;

} catch (Exception $e) {
    fail($e->getMessage());
}
