<?php
require_once __DIR__ . "/../config/db.php";

function ensurePasswordResetTable()
{
    global $conn;
    static $checked = false;

    if ($checked) {
        return;
    }

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash CHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (token_hash)
    )");

    $checked = true;
}

// Returns the raw token to put in the link. Only its hash is stored.
function createPasswordReset($userId)
{
    global $conn;
    ensurePasswordResetTable();

    $userId = (int) $userId;
    $token = bin2hex(random_bytes(32));
    $hash = hash("sha256", $token);

    mysqli_query($conn, "DELETE FROM password_resets WHERE user_id = $userId");
    mysqli_query($conn, "INSERT INTO password_resets (user_id, token_hash, expires_at)
                         VALUES ($userId, '$hash', DATE_ADD(NOW(), INTERVAL 30 MINUTE))");

    return $token;
}

// Returns the user id for a valid, unexpired token, or 0.
function findValidPasswordReset($token)
{
    global $conn;
    ensurePasswordResetTable();

    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
        return 0;
    }

    $hash = hash("sha256", $token);
    $result = mysqli_query($conn, "SELECT user_id FROM password_resets WHERE token_hash = '$hash' AND expires_at > NOW() LIMIT 1");
    $row = $result ? mysqli_fetch_assoc($result) : null;

    return $row ? (int) $row["user_id"] : 0;
}

function deletePasswordResetsForUser($userId)
{
    global $conn;
    ensurePasswordResetTable();

    $userId = (int) $userId;
    return mysqli_query($conn, "DELETE FROM password_resets WHERE user_id = $userId");
}
?>
