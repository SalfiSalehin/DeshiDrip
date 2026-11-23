<?php
require_once __DIR__ . "/../config/auth.php";

if (isLoggedIn()) {
    clearRememberToken($_SESSION["user_id"]);
}

session_destroy();
setcookie("remember_token", "", rememberCookieOptions(time() - 3600));
header("Location: home.php");
exit;
?>
