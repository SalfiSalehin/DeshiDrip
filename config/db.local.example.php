<?php
// Copy to db.local.php and fill in the values from your hosting control panel.
$host = "sqlXXX.infinityfree.com";
$user = "if0_XXXXXXXX";
$password = "YOUR_DB_PASSWORD";
$database = "if0_XXXXXXXX_clothing";

// Do not show PHP errors to visitors on the live site.
ini_set("display_errors", "0");

// Set to false only if your host can send email (the reset link is then emailed only).
$resetDemoMode = true;
