<?php
require_once "../core/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter($_POST['email']);
    $reset_code = filter($_POST['reset_code']);
    $new_password = password_hash(filter($_POST['new_password']), PASSWORD_BCRYPT);

    $stmt = $connect->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE email = ? AND reset_code = ?");
    if ($stmt->execute([$new_password, $email, $reset_code])) {
        $message = "Your password has been reset successfully.";
    } else {
        $error = "Failed to reset password. Please check your reset code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Password Reset</title>
</head>
<body>
    <h1>Confirm Password Reset</h1>
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    <form method="POST" action="">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="reset_code">Reset Code:</label>
        <input type="text" id="reset_code" name="reset_code" required>
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
