<?php
// Default = local XAMPP. On a live host, copy config/db.local.example.php
// to config/db.local.php and fill in the hosting credentials (never commit it).
$host = "localhost";
$user = "root";
$password = "";
$database = "clothing_brand";

// Password reset: no mail server on XAMPP or free hosts, so customer reset links are
// shown on screen. Set to false in db.local.php once the host can really send email.
$resetDemoMode = true;

if (file_exists(__DIR__ . "/db.local.php")) {
    require __DIR__ . "/db.local.php";
}

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
