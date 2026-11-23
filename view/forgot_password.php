<?php
require_once "header.php";
require_once __DIR__ . "/../model/UserModel.php";
require_once __DIR__ . "/../model/PasswordResetModel.php";

$message = "";
$error = false;
$demoLink = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !csrfCheck()) {
    $message = "Security check failed. Please reload the page and try again.";
    $error = true;
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email address.";
        $error = true;
    } else {
        $user = getUserByEmail($email);

        if ($user) {
            $token = createPasswordReset($user["id"]);
            $host = preg_replace('/[^a-zA-Z0-9.:\-]/', '', $_SERVER["HTTP_HOST"] ?? "localhost");
            $folder = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/\\");
            $link = (isHttps() ? "https" : "http") . "://" . $host . $folder . "/reset_password.php?token=" . $token;

            $body = "Hello " . $user["name"] . ",\n\nUse this link to set a new DeshiDrip password. It expires in 30 minutes:\n" . $link . "\n\nIf you did not ask for this, ignore this email.";
            @mail($user["email"], "Reset your DeshiDrip password", $body, "From: no-reply@deshidrip.com");

            // Demo mode: free hosts rarely send email, so customer links are shown on screen.
            // Admin links are never shown, so the public demo admin cannot be taken over.
            if ($resetDemoMode && $user["role"] == "customer") {
                $demoLink = $link;
            }
        }

        // Same answer whether or not the email exists.
        $message = "If that email is registered, a password reset link has been sent. It works for 30 minutes.";
    }
}
?>

<a class="back-link" href="login.php">&larr; Back to Login</a>
<h2>Forgot Password</h2>
<p class="page-intro">Enter the email you registered with. We will send you a link to choose a new password.</p>

<?php if ($message != "") { ?><div class="message <?php echo $error ? "" : "success"; ?>"><?php echo clean($message); ?></div><?php } ?>

<?php if ($demoLink != "") { ?>
    <div class="message">
        <strong>Demo mode:</strong> this site cannot send email, so here is your link.
        <br><a href="<?php echo clean($demoLink); ?>">Open the reset link</a>
    </div>
<?php } ?>

<form method="post" onsubmit="return validateForgot()">
    <?php echo csrfField(); ?>
    <label>Email</label>
    <input type="email" name="email" id="email">
    <button type="submit">Send reset link</button>
</form>

<script>
function validateForgot() {
    if (document.getElementById("email").value.trim() == "") {
        alert("Email is required.");
        return false;
    }
    return true;
}
</script>

<?php require_once "footer.php"; ?>
