<?php
session_start();
require_once "../core/functions.php";

if (!isUserLoggedIn()) {
    header('Location: /login');
    exit();
}

require_once "../pages/header.php";
require_once "../pages/navbar.php";

// Content for logged-in users
echo "<h1>Work Page</h1>";
echo "<p>Welcome to the work page. Only logged-in users can see this content.</p>";
// Add content for liking and chatting

require_once "../pages/footer.php";
?>
