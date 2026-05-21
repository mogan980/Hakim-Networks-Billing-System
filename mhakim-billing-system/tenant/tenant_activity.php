<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';

$companyId = $_SESSION["tenant_company_id"];

$logs = $pdo->prepare("
SELECT * FROM tenant_router_logs
WHERE company_id=?
ORDER BY id DESC
LIMIT 50
");
$logs->execute([$companyId]);
$items=$logs->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>Activity Logs</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="/mhakim-billing-system/tenant/assets/pro.css">
</head>
<body>
<?php include __DIR__ . "/tenant_layout.php"; ?>

<div class="main">
<div class="hero">
<h1>Activity Logs</h1>
<p>Track router connections, disconnections and system activity.</p>
</div>


<div class="panel" style="margin-top:24px;">
<h2>Live Activity Feed</h2>

<?php foreach($items as $log): ?>

<div class="activity">
<div>
<span class="live-dot"></span>
<span><?php echo htmlspecialchars($log["message"]); ?></span>
</div>

<div>
<strong><?php echo htmlspecialchars($log["created_at"]); ?></strong>
</div>
</div>

<?php endforeach; ?>

</div>

</div>
</body>
</html>
