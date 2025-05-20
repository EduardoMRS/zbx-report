<?php
if (isset($_GET["modal"])) {
    include_once(__DIR__ . "/pages/modal/index.php");
    exit;
}

if (isset($_GET["setup"])) {
    include_once __DIR__ . "/setup/index.php";
    exit;
}

// Rotas permitidas apenas com login
if (isset($_SESSION) and isset($_SESSION["idUser"]) and $_SESSION['password']) {
    $stored_password = mysql_select_in_array('users', "email='$_SESSION[idUser]'");
    if ($stored_password && password_verify($password, $stored_password["password"])) {
        $_SESSION['idUser'] = $stored_password["id"];
        $_SESSION['firstName'] = explode(" ", $stored_password["firstName"])[0];
        $_SESSION['password'] = $stored_password["password"];
        return;
    }
}
if (isset($_SESSION["idUser"])) {
    $userData = mysql_select_in_array("users", "id=$_SESSION[idUser]");
    if ($userData) {
        mysql_update('users', ['last_access' => date("Y-m-d H:i:s")], 'id', $_SESSION['idUser']);
    }
}
if (!$userData) {
    header("location: {$site_url}login/");
    exit;
}




// Rota de Logout
if (isset($_GET["logout"])) {
    session_unset();
    setcookie("remember_user", "", time() - 100, "/");
    sleep(1);
    header("location: {$site_url}login/");
    exit;
}
