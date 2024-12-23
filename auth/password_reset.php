<?php
require_once "../core/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter($_POST['email']);
    $reset_code = bin2hex(random_bytes(16));

    $stmt = $connect->prepare("UPDATE users SET reset_code = ? WHERE email = ?");
    if ($stmt->execute([$reset_code, $email])) {
        // Send email with the reset code (implementation of email sending is omitted here)
        // ...
        $message = "A password reset link has been sent to your email.";
    } else {
        $error = "Failed to send password reset link.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
</head>
<body>
    <h1>Password Reset</h1>
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    <form method="POST" action="">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
