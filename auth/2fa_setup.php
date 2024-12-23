<?php
session_start();
require_once "../core/functions.php";

if (!isUserLoggedIn()) {
    header('Location: /login');
    exit();
}

$user = getLoggedInUserInfo();
$totp = OTPHP\TOTP::create();
$_SESSION['totp_secret'] = $totp->getSecret();
$uri = $totp->getProvisioningUri($user['email'], 'YourApp');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>2FA Setup</title>
</head>
<body>
    <h1>2FA Setup</h1>
    <p>Scan the QR code with your authenticator app:</p>
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($uri); ?>" alt="QR Code">
    <form method="POST" action="/2fa-verify">
        <label for="token">Enter the code from your authenticator app:</label>
        <input type="text" id="token" name="token" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
