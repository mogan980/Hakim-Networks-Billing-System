<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isSuperAdmin() {
    return ($_SESSION["tenant_role"] ?? "") === "super_admin";
}

function requireSuperAdmin() {

    if (!isSuperAdmin()) {

        header("Location: noc_final_clean.php");
        exit;
    }
}
