<?php
session_start();
require_once "core/dbConnect.php";
require_once "core/functions.php";

// Ensure the user is logged in
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getLoggedInUserInfo();

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $avatar = $_FILES['avatar'];
    $avatarPath = 'uploads/' . basename($avatar['name']);

    if (move_uploaded_file($avatar['tmp_name'], $avatarPath)) {
        $stmt = $connect->prepare("UPDATE users SET avatar = :avatar WHERE user_id = :user_id");
        $stmt->execute(['avatar' => $avatarPath, 'user_id' => $user['user_id']]);
        $_SESSION['user_avatar'] = $avatarPath;
        header('Location: profile.php');
        exit();
    } else {
        $error = 'Failed to upload avatar.';
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'], $_POST['new_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];

    if (password_verify($currentPassword, $user['password'])) {
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $connect->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
        $stmt->execute(['password' => $newPasswordHash, 'user_id' => $user['user_id']]);
        $success = 'Password updated successfully.';
    } else {
        $error = 'Current password is incorrect.';
    }
}

// Handle security method update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['security_method'])) {
    $securityMethod = $_POST['security_method'];
    $stmt = $connect->prepare("UPDATE users SET security_method = :security_method WHERE user_id = :user_id");
    $stmt->execute(['security_method' => $securityMethod, 'user_id' => $user['user_id']]);
    $success = 'Security method updated successfully.';
}

require_once "pages/header.php";
require_once "pages/navbar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Profile</h1>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>

        <div class="profile-dashboard">
            <div class="profile-section">
                <h2>Profile Information</h2>
                <img src="<?= $user['avatar'] ?>" alt="Avatar" class="avatar">
                <form action="profile.php" method="post" enctype="multipart/form-data">
                    <label for="avatar">Change Avatar:</label>
                    <input type="file" name="avatar" id="avatar">
                    <button type="submit">Upload</button>
                </form>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            </div>

            <div class="security-section">
                <h2>Security Settings</h2>
                <form action="profile.php" method="post">
                    <label for="current_password">Current Password:</label>
                    <input type="password" name="current_password" id="current_password" required>
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" id="new_password" required>
                    <button type="submit">Update Password</button>
                </form>
                <form action="profile.php" method="post">
                    <label for="security_method">Security Method:</label>
                    <select name="security_method" id="security_method">
                        <option value="2fa" <?= $user['security_method'] === '2fa' ? 'selected' : '' ?>>Two-Factor Authentication</option>
                        <option value="sms" <?= $user['security_method'] === 'sms' ? 'selected' : '' ?>>SMS Verification</option>
                    </select>
                    <button type="submit">Update Security Method</button>
                </form>
            </div>
        </div>
    </div>

    <?php require_once "pages/footer.php"; ?>
</body>
</html>
