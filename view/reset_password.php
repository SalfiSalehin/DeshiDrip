<?php
require_once "header.php";
require_once __DIR__ . "/../model/UserModel.php";
require_once __DIR__ . "/../model/PasswordResetModel.php";

$token = $_GET["token"] ?? ($_POST["token"] ?? "");
$userId = findValidPasswordReset($token);
$message = "";
$done = false;

if ($userId > 0 && $_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if (!csrfCheck()) {
        $message = "Security check failed. Please reload the page and try again.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $message = "The two passwords do not match.";
    } else {
        updateUserPassword($userId, password_hash($password, PASSWORD_DEFAULT));
        clearRememberToken($userId);
        deletePasswordResetsForUser($userId);
        $done = true;
    }
}
?>

<a class="back-link" href="login.php">&larr; Back to Login</a>
<h2>Reset Password</h2>

<?php if ($done) { ?>
    <div class="message success">Your password was changed. You can log in now.</div>
    <a class="btn" href="login.php">Go to Login</a>
<?php } elseif ($userId == 0) { ?>
    <div class="message">This reset link is invalid or has expired.</div>
    <a class="btn" href="forgot_password.php">Request a new link</a>
<?php } else { ?>
    <?php if ($message != "") { ?><div class="message"><?php echo clean($message); ?></div><?php } ?>

    <form method="post" onsubmit="return validateReset()">
        <?php echo csrfField(); ?>
        <input type="hidden" name="token" value="<?php echo clean($token); ?>">

        <label>New Password</label>
        <input type="password" name="password" id="password">

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm_password">

        <button type="submit">Change password</button>
    </form>

    <script>
    function validateReset() {
        let p = document.getElementById("password").value;
        if (p.length < 8) {
            alert("Password must be at least 8 characters.");
            return false;
        }
        if (p !== document.getElementById("confirm_password").value) {
            alert("The two passwords do not match.");
            return false;
        }
        return true;
    }
    </script>
<?php } ?>

<?php require_once "footer.php"; ?>
