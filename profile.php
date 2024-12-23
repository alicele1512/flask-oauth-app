<?php
session_start();
require_once "../core/functions.php";
require_once "../core/dbConnect.php";

// Ensure the user is logged in
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getLoggedInUserInfo();

// Handle updating profile information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullname = filter($_POST['fullname']);
        $email = filter($_POST['email']);
        $address = filter($_POST['address']);

        // Update user information in the database
        $stmt = $connect->prepare("UPDATE users SET fullname = ?, email = ?, address = ? WHERE user_id = ?");
        $stmt->execute([$fullname, $email, $address, $user['user_id']]);
        header('Location: profile.php?update=success');
        exit();
    }

    // Handle updating avatar
    if (isset($_POST['update_avatar'])) {
        $avatar = $_FILES['avatar'];

        // Check if file is an image
        $check = getimagesize($avatar["tmp_name"]);
        if ($check !== false) {
            $target_dir = "../uploads/avatars/";
            $target_file = $target_dir . basename($avatar["name"]);
            move_uploaded_file($avatar["tmp_name"], $target_file);

            // Update avatar path in the database
            $stmt = $connect->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
            $stmt->execute([$target_file, $user['user_id']]);
            $_SESSION['user_avatar'] = $target_file;
            header('Location: profile.php?avatar=success');
            exit();
        }
    }

    // Handle updating password
    if (isset($_POST['update_password'])) {
        $current_password = filter($_POST['current_password']);
        $new_password = password_hash(filter($_POST['new_password']), PASSWORD_BCRYPT);

        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Update password in the database
            $stmt = $connect->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([$new_password, $user['user_id']]);
            header('Location: profile.php?password=success');
            exit();
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

require_once "../pages/header.php";
require_once "../pages/navbar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
</head>
<body>
    <div class="container">
        <h1>Profile</h1>

        <!-- Profile Information -->
        <div class="profile-section">
            <h2>Profile Information</h2>
            <?php if (isset($_GET['update']) && $_GET['update'] === 'success') { echo "<p>Profile updated successfully.</p>"; } ?>
            <form action="profile.php" method="post">
                <div class="form-group">
                    <label for="fullname">Full Name:</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea class="form-control" id="address" name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                <input type="hidden" name="update_profile" value="1">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>

        <!-- Avatar Update -->
        <div class="profile-section">
            <h2>Change Avatar</h2>
            <?php if (isset($_GET['avatar']) && $_GET['avatar'] === 'success') { echo "<p>Avatar updated successfully.</p>"; } ?>
            <form action="profile.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="avatar">Upload Avatar:</label>
                    <input type="file" class="form-control" id="avatar" name="avatar">
                </div>
                <input type="hidden" name="update_avatar" value="1">
                <button type="submit" class="btn btn-primary">Update Avatar</button>
            </form>
        </div>

        <!-- Password Update -->
        <div class="profile-section">
            <h2>Change Password</h2>
            <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
            <?php if (isset($_GET['password']) && $_GET['password'] === 'success') { echo "<p>Password updated successfully.</p>"; } ?>
            <form action="profile.php" method="post">
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <input type="hidden" name="update_password" value="1">
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="profile-section">
            <h2>Security Settings</h2>
            <p>Manage your security settings here.</p>
            <a href="2fa_setup.php" class="btn btn-primary">Setup Two-Factor Authentication (2FA)</a>
        </div>
    </div>
</body>
</html>

<?php
require_once "../pages/footer.php";
?>
