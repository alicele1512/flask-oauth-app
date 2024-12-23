<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/functions.php';

// Function to translate text using LibreTranslate
function libreTranslate($text, $source_lang, $target_lang) {
    $endpoint = 'https://libretranslate.de/translate';
    $data = array(
        'q' => $text,
        'source' => $source_lang,
        'target' => $target_lang,
    );

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($endpoint, false, $context);
    if ($result === FALSE) {
        return $text; // Return the original text if translation fails
    }

    $response = json_decode($result, true);
    return $response['translatedText'] ?? $text;
}

$current_lang = $_SESSION['lang'] ?? 'en';
$translations = [
    'home' => libreTranslate('Home', 'en', $current_lang),
    'about' => libreTranslate('About', 'en', $current_lang),
    'work' => libreTranslate('Work', 'en', $current_lang),
    'blog' => libreTranslate('Blog', 'en', $current_lang),
    'contact' => libreTranslate('Contact', 'en', $current_lang),
    'profile' => libreTranslate('Profile', 'en', $current_lang),
    'logout' => libreTranslate('Logout', 'en', $current_lang),
];

$url = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
?>
<header class="header">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/1_cta.php'; ?>
    <div class="header-content responsive-wrapper">
        <nav class="header-navigation">
            <div class="header-logo">
                <div class="dropdown">
                    <button class="button" id="menu-button" aria-label="open menu">
                        <!-- Menu Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                            <path d="M128,80a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160Zm109.94-52.79a8,8,0,0,0-3.89-5.4l-29.83-17-.12-33.62a8,8,0,0,0-2.83-6.08,111.91,111.91,0,0,0-36.72-20.67,8,8,0,0,0-6.46.59L128,41.85,97.88,25a8,8,0,0,0-6.47-.6A112.1,112.1,0,0,0,54.73,45.15a8,8,0,0,0-2.83,6.07l-.15,33.65-29.83,17a8,8,0,0,0-3.89,5.4,106.47,106.47,0,0,0,0,41.56,8,8,0,0,0,3.89,5.4l29.83,17,.12,33.62a8,8,0,0,0,2.83,6.08,111.91,111.91,0,0,0,36.72,20.67,8,8,0,0,0,6.46-.59L128,214.15,158.12,231a7.91,7.91,0,0,0,3.9,1,8.09,8.09,0,0,0,2.57-.42,112.1,112.1,0,0,0,36.68-20.73,8,8,0,0,0,2.83-6.07l.15-33.65,29.83-17a8,8,0,0,0,3.89-5.4A106.47,106.47,0,0,0,237.94,107.21Zm-15,34.91-28.57,16.25a8,8,0,0,0-3,3c-.58,1-1.19,2.06-1.81,3.06a7.94,7.94,0,0,0-1.22,4.21l-.15,32.25a95.89,95.89,0,0,1-25.37,14.3L134,199.13a8,8,0,0,0-3.91-1h-.19c-1.21,0-2.43,0-3.64,0a8.08,8.08,0,0,0-4.1,1l-28.84,16.1A96,96,0,0,1,67.88,201l-.11-32.2a8,8,0,0,0-1.22-4.22c-.62-1-1.23-2-1.8-3.06a8.09,8.09,0,0,0-3-3.06l-28.6-16.29a90.49,90.49,0,0,1,0-28.26L61.67,97.63a8,8,0,0,0,3-3c.58-1,1.19-2.06,1.81-3.06a7.94,7.94,0,0,0,1.22-4.21l.15-32.25a95.89,95.89,0,0,1,25.37-14.3L122,56.87a8,8,0,0,0,4.1,1c1.21,0,2.43,0,3.64,0a8.08,8.08,0,0,0,4.1-1l28.84-16.1A96,96,0,0,1,188.12,55l.11,32.2a8,8,0,0,0,1.22,4.22c.62,1,1.23,2,1.8,3.06a8.09,8.09,0,0,0,3,3.06l28.6,16.29A90.49,90.49,0,0,1,222.9,142.12Z"></path>
                        </svg>
                    </button>
                    <ul class="dropdown-menu" id="menu-dropdown">
                        <li><a href="../" class="<?= ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/index.php') ? 'active' : '' ?>"><?= $translations['home'] ?></a></li>
                        <li><a href="../about" class="<?= ($_SERVER['REQUEST_URI'] == '/about.php') ? 'active' : '' ?>"><?= $translations['about'] ?></a></li>
                        <li><a href="../work" class="<?= ($_SERVER['REQUEST_URI'] == '/work.php') ? 'active' : '' ?>"><?= $translations['work'] ?></a></li>
                        <li><a href="../blog" class="<?= ($_SERVER['REQUEST_URI'] == '/blog.php') ? 'active' : '' ?>"><?= $translations['blog'] ?></a></li>
                        <li><a href="../contact" class="<?= ($_SERVER['REQUEST_URI'] == '/contact.php') ? 'active' : '' ?>"><?= $translations['contact'] ?></a></li>
                        <li><a href="../profile" class="<?= ($_SERVER['REQUEST_URI'] == '/profile.php') ? 'active' : '' ?>"><?= $translations['profile'] ?></a></li>
                        <li><a href="../logout" class="<?= ($_SERVER['REQUEST_URI'] == '/logout.php') ? 'active' : '' ?>"><?= $translations['logout'] ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="header-navigation-links">
                <a href="../../" class="<?= ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/index.php') ? 'active' : '' ?>"><?= $translations['home'] ?></a>
                <a href="../../about" class="<?= ($_SERVER['REQUEST_URI'] == '/about.php') ? 'active' : '' ?>"><?= $translations['about'] ?></a>
                <a href="../../work/index.php" class="<?= (strpos($_SERVER['REQUEST_URI'], '/work') === 0) ? 'active' : '' ?>"><?= $translations['work'] ?></a>
                <a href="../../blog/index.php" class="<?= (strpos($_SERVER['REQUEST_URI'], '/blog') === 0) ? 'active' : '' ?>"><?= $translations['blog'] ?></a>
                <a href="../../#service" class="<?= (($_SERVER['REQUEST_URI'] == '/index.php' || $_SERVER['REQUEST_URI'] == '/') && isset($_GET['service'])) ? 'active' : '' ?>"><?= $translations['service'] ?></a>
                <a href="../../checkout" class="<?= ($_SERVER['REQUEST_URI'] == '/checkout.php') ? 'active' : '' ?>"><?= $translations['checkout'] ?></a>
                <a href="../../contact" class="<?= ($_SERVER['REQUEST_URI'] == '/contact.php') ? 'active' : '' ?>"><?= $translations['contact'] ?></a>
            </div>
            <div class="header-navigation-actions">
                <!-- Notification Icon -->
                <a class="button no-border icon-button" id="notification-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                        <path d="M221.8,175.94C216.25,166.38,208,139.33,208,104a80,80,0,1,0-160,0c0,35.34-8.26,62.38-13.81,71.94A16,16,0,0,0,48,200H88.81a40,40,0,0,0,78.38,0H208a16,16,0,0,0,13.8-24.06ZM128,216a24,24,0,0,1-22.62-16h45.24A24,24,0,0,1,128,216ZM48,184c7.7-13.24,16-43.92,16-80a64,64,0,1,1,128,0c0,36.05,8.28,66.73,16,80Z"></path>
                    </svg>
                    <?php
                    $stmt = $connect->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = '0'");
                    $stmt->execute();
                    $unreadCount = $stmt->fetchColumn();
                    if ($unreadCount > 0) {
                        echo '<span class="notification-count">' . $unreadCount . '</span>';
                    }
                    ?>
                </a>
                <div class="notification-box" id="message-box">
                    <div class="message-box-header">Surprise!</div>
                    <?php
                    $stmt = $connect->prepare("
                        SELECT n.icon, n.source_type, b.sef, b.title, b.subtitle, b.content
                        FROM notifications n
                        LEFT JOIN blog b ON n.source_id = b.id
                        WHERE n.is_read = '0'
                    ");
                    $stmt->execute();
                    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($notifications)) {
                        echo '<ul class="message-box-list">';
                        foreach ($notifications as $notification) {
                            $sef = htmlspecialchars($notification['sef'] ?? '#');
                            $icon = $notification['icon'] ?? '';
                            $blog_title = htmlspecialchars($notification['title'] ?? 'No Title');
                            $blog_subtitle = htmlspecialchars($notification['subtitle'] ?? 'No Subtitle');
                            $content = $notification['content'] ?? 'No Description';
                            $b_trimmedContent = implode(' ', array_slice(explode(' ', $content), 0, 15));  //15 words
                            $sourceType = htmlspecialchars($notification['source_type'] ?? 'blog');

                            echo "<li>
                                    <span class='message-box-icon'>$icon</span>
                                    <a href='https://weareicers.com/$sourceType/$sef'><b>$blog_title</b> - $blog_subtitle</a>
                                  </li>";
                        }
                        echo '</ul>';
                    } else {
                        echo '<p class="message-box-header">No new notifications</p>';
                    }
                    ?>
                </div>

                <!-- Language Switcher -->
                <div class="language-switcher">
                    <button onclick="switchLanguage('en')">EN</button>
                    <button onclick="switchLanguage('vi')">VN</button>
                    <button onclick="switchLanguage('zh')">CN</button>
                </div>

                <!-- User Profile and Logout -->
                <a class="button no-border dropdown" data-toggle="modal" data-target="#modalLoginForm">
                    <img src="https://weareicers.com/assets/img/favicon.ico" alt="User Avatar" width="100" height="100" class="white-circle" title="default_avatar" loading="eager">
                    <div class="dropdown-menu-1">
                        <li>
                            <svg onclick='window.location.href="../../profile.php"' xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                                <path d="M256,136a8,8,0,0,1-8,8H232v16a8,8,0,0,1-16,0V144H200a8,8,0,0,1,0-16h16V112a8,8,0,0,1,16,0v16h16A8,8,0,0,1,256,136Zm-57.87,58.85a8,8,0,0,1-12.26,10.3C165.75,181.19,138.09,168,108,168s-57.75,13.19-77.87,37.15a8,8,0,0,1-12.25-10.3c14.94-17.78,33.52-30.41,54.17-37.17a68,68,0,1,1,71.9,0C164.6,164.44,183.18,177.07,198.13,194.85ZM108,152a52,52,0,1,0-52-52A52.06,52.06,0,0,0,108,152Z"></path>
                            </svg>
                            <p class="dropdown-text"><?= $translations['profile'] ?></p>
                        </li>
                        <li>
                            <svg onclick='window.location.href="../../logout.php"' xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                                <path d="M120,128V48a8,8,0,0,1,16,0v80a8,8,0,0,1-16,0Zm60.37-78.7a8,8,0,0,0-8.74,13.4C194.74,77.77,208,101.57,208,128a80,80,0,0,1-160,0c0-26.43,13.26-50.23,36.37-65.3a8,8,0,0,0-8.74-13.4C47.9,67.38,32,96.06,32,128a96,96,0,0,0,192,0C224,96.06,208.1,67.38,180.37,49.3Z"></path>
                            </svg>
                            <p class="dropdown-text"><?= $translations['logout'] ?></p>
                        </li>
                    </div>
                </a>
            </div>
        </nav>
    </div>
</header>
<script src="../../assets/js/navbar.js"></script>
<script>
    function switchLanguage(lang) {
        // Save the selected language to the session (using AJAX)
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'set_language.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                location.reload(); // Reload the page to apply the new language
            }
        };
        xhr.send('lang=' + lang);
    }
</script>
