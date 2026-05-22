<?php
session_start();

function isLoggedIn(){
    return isset($_SESSION["user_id"]);
}

function currentUser(){
    return [
        "id" => $_SESSION["user_id"] ?? null,
        "name" => $_SESSION["full_name"] ?? null,
        "username" => $_SESSION["username"] ?? null,
        "role" => $_SESSION["role"] ?? null
    ];
}

function requireLogin(){

    if(!isLoggedIn()){
        header("Location: saas_auth.php");
        exit;
    }

}

function requireRole($roles = []){

    requireLogin();

    $role = $_SESSION["role"] ?? null;

    if(!in_array($role, $roles)){

        http_response_code(403);

        die("
        <h1 style='font-family:Arial;padding:40px;color:#991b1b'>
        Access Denied
        </h1>
        ");

    }

}
