<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/core/session_resolver.php";

$ip = $_GET["ip"] ?? ($_SERVER["REMOTE_ADDR"] ?? "");
$ip = trim($ip);

if(!$ip){
    die("Missing IP");
}

$session = hn_get_best_session($pdo, $ip);

if(!$session){
    header("Location: index.php");
    exit;
}

$username = $session["username"];
$password = $session["password"];

$loginUrl = "http://192.168.88.1/login";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Connecting...</title>
</head>
<body style="background:#07131f;color:white;font-family:Arial;text-align:center;padding-top:80px;">

<h2>Connecting You To Internet...</h2>
<p>Session: <?php echo htmlspecialchars(strtoupper($session["source"])); ?></p>

<form id="loginForm" method="post" action="<?php echo $loginUrl; ?>">
<input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
<input type="hidden" name="password" value="<?php echo htmlspecialchars($password); ?>">
<input type="hidden" name="dst" value="http://neverssl.com">
<input type="hidden" name="popup" value="true">
</form>

<script>
document.getElementById("loginForm").submit();
</script>

</body>
</html>
