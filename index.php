<?php
require 'vendor/autoload.php';
require 'core/dbConnect.php';
require 'core/functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use OTPHP\TOTP;
use Firebase\JWT\JWT;

session_start();

// PHPMailer configuration
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.example.com';
$mail->SMTPAuth = true;
$mail->Username = 'your_email@example.com';
$mail->Password = 'your_password';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

// JWT secret key
$jwt_secret = 'your_secret_key';

// Routing
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = trim($path, '/');

switch ($route) {
    case '':
    case 'home':
        require 'auth/home.php';
        break;
    case 'login':
        require 'auth/login.php';
        break;
    case 'profile':
        require 'auth/profile.php';
        break;
    case 'password-reset':
        require 'auth/password_reset.php';
        break;
    case 'password-reset/confirm':
        require 'auth/password_reset_confirm.php';
        break;
    case '2fa-setup':
        require 'auth/2fa_setup.php';
        break;
    case '2fa-verify':
        handle2FAVerify();
        break;
    case 'work':
        require 'pages/work.php';
        break;
    case 'book':
        require 'pages/book.php';
        break;
    case 'blog':
        require 'pages/blog.php';
        break;
    case 'about':
        require 'pages/about.php';
        break;
    case 'checkout':
        require 'pages/checkout.php';
        break;
    case 'order':
        require 'pages/order.php';
        break;
    default:
        http_response_code(404);
        echo '404 Not Found';
        break;
}

function handle2FAVerify() {
    global $connect;
    session_start();
    $user_id = $_SESSION['user_id'];
    $user = getUserById($connect, $user_id);
    $totp = TOTP::create($user['2fa_secret']);
    $token = $_POST['token'];

    if ($totp->verify($token)) {
        $_SESSION['2fa_verified'] = true;
        header('Location: /profile');
    } else {
        echo 'Invalid 2FA token';
    }
}

function getUserById($pdo, $user_id) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
