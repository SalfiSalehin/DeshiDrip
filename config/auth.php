<?php
// Buffer output so redirects work even when the host has output_buffering off.
ob_start();

function isHttps()
{
    return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https");
}

if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params(array(
        "lifetime" => 0,
        "path" => "/",
        "secure" => isHttps(),
        "httponly" => true,
        "samesite" => "Lax"
    ));
    session_start();
}

require_once __DIR__ . "/../model/UserModel.php";

if (!isset($_SESSION["user_id"]) && !empty($_COOKIE["remember_token"]) && is_string($_COOKIE["remember_token"])) {
    $user = getUserByRememberToken($_COOKIE["remember_token"]);

    if ($user) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"];
    }
}

function isLoggedIn()
{
    return isset($_SESSION["user_id"]);
}

function isAdmin()
{
    return isset($_SESSION["role"]) && $_SESSION["role"] == "admin";
}

function isCustomer()
{
    return isset($_SESSION["role"]) && $_SESSION["role"] == "customer";
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin()
{
    requireLogin();

    if (!isAdmin()) {
        header("Location: home.php");
        exit;
    }
}

function requireCustomer()
{
    requireLogin();

    if (!isCustomer()) {
        header("Location: home.php");
        exit;
    }
}

function clean($value)
{
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

function rememberCookieOptions($expires)
{
    return array(
        "expires" => $expires,
        "path" => "/",
        "secure" => isHttps(),
        "httponly" => true,
        "samesite" => "Lax"
    );
}

function csrfToken()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . clean(csrfToken()) . '">';
}

function csrfCheck()
{
    $sent = $_POST["csrf_token"] ?? ($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "");

    return is_string($sent) && $sent !== "" && hash_equals(csrfToken(), $sent);
}

function requireCsrfJson()
{
    if (!csrfCheck()) {
        echo json_encode(array("status" => "error", "message" => "Session expired. Please reload the page and try again."));
        exit;
    }
}
?>
