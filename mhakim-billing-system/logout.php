<?php
session_start();
session_unset();
session_destroy();

header("Location: saas_auth.php");
exit;
