<?php
require_once __DIR__ . "/config/database.php";
header("Content-Type: application/json");

$logo = "";
try {
    $logo = $pdo->query("SELECT logo_path FROM admin_settings WHERE id=1 LIMIT 1")->fetchColumn();
} catch (Exception $e) {
    $logo = "";
}

echo json_encode([
    "logo" => $logo ? "/mhakim-billing-system/" . ltrim($logo, "/") : ""
]);
