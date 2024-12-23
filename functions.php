<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/dbConnect.php';

function getIP() {
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        return $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else {
        return $_SERVER["REMOTE_ADDR"];
    }
}

function filter($param) {
    $a = trim($param);
    $b = strip_tags($a);
    return htmlspecialchars($b, ENT_QUOTES);
}

function verifyCaptcha($captchaResponse) {
    global $recaptchaSecret;
    if (empty($captchaResponse)) return false;
    $url = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$captchaResponse";
    $response = json_decode(file_get_contents($url), true);
    return $response['success'] ?? false;
}

function checkUserCredentials($email, $password) {
    global $connect;
    $query = $connect->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $userData = $query->fetch(PDO::FETCH_ASSOC);

    return $userData && password_verify($password, $userData["password"]);
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getLoggedInUserInfo() {
    if (isUserLoggedIn()) {
        global $connect;
        $stmt = $connect->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}
?>
