<?php
session_start();
require_once "../core/functions.php";

if (!isUserLoggedIn()) {
    header('Location: /login');
    exit();
}

$user = getLoggedInUserInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
</head>
<body>
    <h1>Profile</h1>
    <p>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</p>
    <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="Profile Picture">
    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
</body>
</html>
