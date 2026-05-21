<?php
date_default_timezone_set("Africa/Nairobi");
ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function fail($msg){
    file_put_contents(__DIR__."/voucher_queue_error.log", "[".date("Y-m-d H:i:s")."] ".$msg.PHP_EOL, FILE_APPEND);
    echo "<body style='background:#5b0808;color:white;font-family:Arial;text-align:center;padding-top:80px'>";
    echo "<h1>Voucher Activation Failed</h1>";
    echo "<p>".htmlspecialchars($msg)."</p>";
    echo "<a style='color:white' href='index.php'>Go Back</a>";
    echo "</body>";
    exit;
}

$code = strtoupper(trim($_POST["voucher_code"] ?? ""));

if ($code === "") {
    fail("Voucher code required.");
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
    $parts = explode("/", $speed);
    $down = trim($parts[0] ?? "8M");
    $up = trim($parts[1] ?? "2M");

    $profile = "default"; // safe working profile first
    $validityDays = (int)($voucher["validity_days"] ?? 1);
    if ($validityDays < 1) $validityDays = 1;
    $limitUptime = ($validityDays * 24) . "h";

    $config = new Config([
        "host" => "192.168.88.1",
        "user" => "mhakimapi",
        "pass" => "12345678",
        "port" => 8728,
        "timeout" => 10
    ]);

    $client = new Client($config);

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

    $clientIp = $_SERVER["REMOTE_ADDR"] ?? "";

    $client->query(
        (new Query("/ip/hotspot/user/add"))
            ->equal("name", $code)
            ->equal("password", $code)
            ->equal("profile", $profile)
            ->equal("limit-uptime", $limitUptime)
            ->equal("comment", "voucher:" . $code)
    )->read();

    if ($clientIp !== "") {
        $oldBindings = $client->query(
            (new Query("/ip/hotspot/ip-binding/print"))
                ->where("address", $clientIp)
        )->read();

        foreach ($oldBindings as $b) {
            if (isset($b[".id"])) {
                $client->query(
                    (new Query("/ip/hotspot/ip-binding/remove"))
                        ->equal(".id", $b[".id"])
                )->read();
            }
        }

        $client->query(
            (new Query("/ip/hotspot/ip-binding/add"))
                ->equal("address", $clientIp)
                ->equal("type", "bypassed")
                ->equal("comment", "voucher:" . $code)
        )->read();
    }

    $update = $pdo->prepare("UPDATE vouchers SET status='used' WHERE id=?");
    $update->execute([$voucher["id"]]);

    header("Location: voucher_success.php?u=" . urlencode($code) . "&p=" . urlencode($code) . "&ip=" . urlencode($_SERVER["REMOTE_ADDR"] ?? ""));
    exit;

} catch (Exception $e) {
    fail($e->getMessage());
}
