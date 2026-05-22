<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config/database.php";

$today = $pdo->query("
SELECT COALESCE(SUM(amount),0)
FROM hotspot_payments
WHERE status='paid' AND DATE(paid_at)=CURDATE()
")->fetchColumn();

$month = $pdo->query("
SELECT COALESCE(SUM(amount),0)
FROM hotspot_payments
WHERE status='paid'
AND MONTH(paid_at)=MONTH(CURDATE())
AND YEAR(paid_at)=YEAR(CURDATE())
")->fetchColumn();

$total = $pdo->query("
SELECT COALESCE(SUM(amount),0)
FROM hotspot_payments
WHERE status='paid'
")->fetchColumn();

echo json_encode([
  "today" => (float)$today,
  "month" => (float)$month,
  "total" => (float)$total,
  "today_fmt" => "KES " . number_format((float)$today),
  "month_fmt" => "KES " . number_format((float)$month),
  "total_fmt" => "KES " . number_format((float)$total)
]);
