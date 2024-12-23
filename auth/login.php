<?php
session_start();
require_once "../core/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter($_POST['email']);
    $password = filter($_POST['password']);

    if (checkUserCredentials($email, $password)) {
        $user = getLoggedInUserInfo();
        $_SESSION['user_id'] = $user['user_id'];
        header('Location: /profile');
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    <form method="POST" action="">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Login</button>
    </form>
    <a href="/password-reset">Forgot your password?</a>
</body>
</html>
