<?php
date_default_timezone_set("Africa/Nairobi");

require_once "/var/www/html/mhakim-billing-system/config/database.php";
require_once "/var/www/html/mhakim-billing-system/core/activate_internet.php";

$raw = file_get_contents("php://input");
file_put_contents(__DIR__."/mpesa_callback.log", "[".date("Y-m-d H:i:s")."] ".$raw."\n", FILE_APPEND);

$data = json_decode($raw, true);
$stk = $data["Body"]["stkCallback"] ?? null;

if (!$stk) {
    echo json_encode(["ResultCode"=>0,"ResultDesc"=>"Accepted"]);
    exit;
}

$checkout = $stk["CheckoutRequestID"] ?? "";
$resultCode = (int)($stk["ResultCode"] ?? 999);

if ($checkout === "") {
    echo json_encode(["ResultCode"=>0,"ResultDesc"=>"Accepted"]);
    exit;
}

$receipt = null;
if (isset($stk["CallbackMetadata"]["Item"])) {
    foreach ($stk["CallbackMetadata"]["Item"] as $item) {
        if (($item["Name"] ?? "") === "MpesaReceiptNumber") {
            $receipt = $item["Value"] ?? null;
        }
    }
}

if ($resultCode !== 0) {
    $stmt = $pdo->prepare("UPDATE payments SET status='failed' WHERE checkout_id=?");
    $stmt->execute([$checkout]);

    echo json_encode(["ResultCode"=>0,"ResultDesc"=>"Accepted"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        payments.*,
        packages.name AS package_name,
        packages.speed_down,
        packages.speed_up,
        packages.duration_hours
    FROM payments
    LEFT JOIN packages ON payments.package_id = packages.id
    WHERE payments.checkout_id = ?
    ORDER BY payments.id DESC
    LIMIT 1
");
$stmt->execute([$checkout]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    file_put_contents(__DIR__."/mpesa_callback.log", "[".date("Y-m-d H:i:s")."] PAYMENT NOT FOUND ".$checkout."\n", FILE_APPEND);
    echo json_encode(["ResultCode"=>0,"ResultDesc"=>"Accepted"]);
    exit;
}

$clientIp = $payment["client_ip"] ?? "";

if ($clientIp !== "") {
    $activation = activateInternet(
        $clientIp,
        $payment["package_name"] ?? "Internet Package",
        $payment["speed_down"] ?? "8M",
        $payment["speed_up"] ?? "2M",
        (int)($payment["duration_hours"] ?? 1),
        "mpesa:" . $checkout,
        $payment["phone"] ?? $checkout,
        $payment["phone"] ?? $checkout
    );

    file_put_contents(__DIR__."/mpesa_callback.log", "[".date("Y-m-d H:i:s")."] ACTIVATION ".json_encode($activation)."\n", FILE_APPEND);
}

$paidAt = date("Y-m-d H:i:s");
$expires = date("Y-m-d H:i:s", time() + ((int)($payment["duration_hours"] ?? 1) * 3600));

$stmt = $pdo->prepare("
    UPDATE payments
    SET status='paid', mpesa_receipt=?
    WHERE checkout_id=?
");
$stmt->execute([$receipt, $checkout]);

file_put_contents(__DIR__."/mpesa_callback.log", "[".date("Y-m-d H:i:s")."] PAYMENT PAID ".$checkout."\n", FILE_APPEND);

echo json_encode(["ResultCode"=>0,"ResultDesc"=>"Accepted"]);
