<?php
require_once "/var/www/html/mhakim-billing-system/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

date_default_timezone_set("Africa/Nairobi");

$log = "/var/www/html/mhakim-hotspot/mpesa_callback.log";
$errorLog = "/var/www/html/mhakim-hotspot/mpesa_callback_error.log";

$raw = file_get_contents("php://input");
file_put_contents($log, $raw . PHP_EOL, FILE_APPEND);

$data = json_decode($raw, true);

try {
    if (!$data || !isset($data["Body"]["stkCallback"])) {
        throw new Exception("Invalid callback data");
    }

    $callback = $data["Body"]["stkCallback"];

    $checkoutId = $callback["CheckoutRequestID"] ?? "";
    $resultCode = (int)($callback["ResultCode"] ?? 1);
    $resultDesc = $callback["ResultDesc"] ?? "";

    if ($resultCode !== 0) {
        $stmt = $pdo->prepare("
            UPDATE payments
            SET status='failed', reference=?, checkout_id=?
            WHERE checkout_id=? OR reference=?
        ");
        $stmt->execute([$checkoutId, $checkoutId, $checkoutId, $checkoutId]);

        http_response_code(200);
        echo "Failed payment recorded";
        exit;
    }

    $amount = null;
    $receipt = null;
    $phone = null;

    foreach ($callback["CallbackMetadata"]["Item"] ?? [] as $item) {
        if ($item["Name"] === "Amount") {
            $amount = $item["Value"];
        }
        if ($item["Name"] === "MpesaReceiptNumber") {
            $receipt = $item["Value"];
        }
        if ($item["Name"] === "PhoneNumber") {
            $phone = $item["Value"];
        }
    }

    $paymentQ = $pdo->prepare("
        SELECT 
            payments.*,
            clients.id AS client_real_id,
            clients.username,
            clients.password,
            packages.duration_hours,
            packages.speed_down,
            packages.speed_up
        FROM payments
        LEFT JOIN clients ON payments.client_id = clients.id
        LEFT JOIN packages ON payments.package_id = packages.id
        WHERE payments.checkout_id=? OR payments.reference=?
        ORDER BY payments.id DESC
        LIMIT 1
    ");
    $paymentQ->execute([$checkoutId, $checkoutId]);
    $payment = $paymentQ->fetch(PDO::FETCH_ASSOC);

    if (!$payment && $phone && $amount !== null) {
        $paymentQ = $pdo->prepare("
            SELECT 
                payments.*,
                clients.id AS client_real_id,
                clients.username,
                clients.password,
                packages.duration_hours,
                packages.speed_down,
                packages.speed_up
            FROM payments
            LEFT JOIN clients ON payments.client_id = clients.id
            LEFT JOIN packages ON payments.package_id = packages.id
            WHERE payments.status='pending'
            AND payments.phone=?
            AND payments.amount=?
            ORDER BY payments.id DESC
            LIMIT 1
        ");
        $paymentQ->execute([$phone, $amount]);
        $payment = $paymentQ->fetch(PDO::FETCH_ASSOC);
    }

    if (!$payment) {
        throw new Exception("Payment not found. Checkout: $checkoutId Phone: $phone Amount: $amount");
    }

    $durationMinutes = round((float)$payment["duration_hours"] * 60);

    $pdo->prepare("
        UPDATE payments
        SET status='paid',
            reference=?,
            checkout_id=?,
            mpesa_receipt=?,
            phone=?
        WHERE id=?
    ")->execute([
        $checkoutId,
        $checkoutId,
        $receipt,
        $phone,
        $payment["id"]
    ]);

    if (!empty($payment["client_real_id"])) {
        $pdo->prepare("
            UPDATE clients
            SET status='active',
                starts_at=NOW(),
                expires_at=DATE_ADD(NOW(), INTERVAL ? MINUTE)
            WHERE id=?
        ")->execute([
            $durationMinutes,
            $payment["client_real_id"]
        ]);
    }

    // MikroTik activation
    try {
        $settings = $pdo->query("
            SELECT * FROM mikrotik_settings 
            ORDER BY id DESC 
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);

        if ($settings && !empty($payment["username"])) {
            $api = new Client(new Config([
                "host" => $settings["router_ip"],
                "user" => $settings["router_username"],
                "pass" => $settings["router_password"],
                "port" => (int)$settings["api_port"],
            ]));

            $username = $payment["username"];
            $password = $payment["password"] ?: "1234";

            $down = trim($payment["speed_down"] ?: "4M");
            $up = trim($payment["speed_up"] ?: "2M");

            $profile = "HOTSPOT-4M";

            if (strpos($down, "20") !== false) {
                $profile = "MONTHLY-20M";
            } elseif (strpos($down, "10") !== false) {
                $profile = "MONTHLY-10M";
            }

            $foundUser = $api->query(
                (new Query("/ip/hotspot/user/print"))
                    ->where("name", $username)
            )->read();

            if (!empty($foundUser)) {
                $api->query(
                    (new Query("/ip/hotspot/user/set"))
                        ->equal(".id", $foundUser[0][".id"])
                        ->equal("password", $password)
                        ->equal("profile", $profile)
                        ->equal("disabled", "no")
                )->read();
            } else {
                $api->query(
                    (new Query("/ip/hotspot/user/add"))
                        ->equal("name", $username)
                        ->equal("password", $password)
                        ->equal("profile", $profile)
                        ->equal("disabled", "no")
                )->read();
            }

            // Simple Queue
            $queueName = "MH-" . $username;
            $maxLimit = $down . "/" . $up;

            $foundQueue = $api->query(
                (new Query("/queue/simple/print"))
                    ->where("name", $queueName)
            )->read();

            if (!empty($foundQueue)) {
                $api->query(
                    (new Query("/queue/simple/set"))
                        ->equal(".id", $foundQueue[0][".id"])
                        ->equal("max-limit", $maxLimit)
                        ->equal("disabled", "no")
                )->read();
            } else {
                $api->query(
                    (new Query("/queue/simple/add"))
                        ->equal("name", $queueName)
                        ->equal("target", $username)
                        ->equal("max-limit", $maxLimit)
                        ->equal("disabled", "no")
                )->read();
            }
        }
    } catch (Exception $e) {
        file_put_contents($errorLog, "[" . date("Y-m-d H:i:s") . "] MikroTik error: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }

    http_response_code(200);
    echo "Payment processed successfully";

} catch (Exception $e) {
    file_put_contents($errorLog, "[" . date("Y-m-d H:i:s") . "] Callback error: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(200);
    echo "Callback received with error";
}
