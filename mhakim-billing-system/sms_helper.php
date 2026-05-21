<?php
function sendSMS($pdo, $phone, $message) {
    $settings = $pdo->query("SELECT * FROM sms_settings WHERE status='active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        return false;
    }

    $username = $settings["username"];
    $apiKey = $settings["api_key"];
    $senderId = $settings["sender_id"];

    if (!$phone || !$message) {
        return false;
    }

    if (substr($phone, 0, 1) === "0") {
        $phone = "254" . substr($phone, 1);
    }

    if (substr($phone, 0, 3) !== "254") {
        $phone = "254" . ltrim($phone, "+");
    }

    $phone = "+" . $phone;

    $data = [
        "username" => $username,
        "to" => $phone,
        "message" => $message,
        "from" => $senderId
    ];

    $ch = curl_init("https://api.africastalking.com/version1/messaging");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
        "apiKey: " . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    $status = $error ? "failed" : "sent";

    $stmt = $pdo->prepare("INSERT INTO sms_logs(phone, message, status, response) VALUES(?,?,?,?)");
    $stmt->execute([$phone, $message, $status, $error ?: $response]);

    return !$error;
}
