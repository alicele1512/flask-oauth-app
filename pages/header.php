<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo($settings["homeSlogan"]); ?> | Homepage</title>
    <meta property="og:image" content="<?= $getUrl ?>/assets/img/alice-photo.jpg" alt="icon" loading="lazy">
    <meta property="og:description" content="<?php echo($settings["description"]); ?>">
    <meta property="og:url" content="<?= $getUrl ?>/">
    <meta property="og:title" content="<?php echo($settings["homeSlogan"]); ?>">
    <meta property="twitter:url" content="<?= $getUrl ?>">
    <link rel="canonical" href="https://weareicers.com">
    <link rel="stylesheet" href="https://vjs.zencdn.net/5-unsafe/video-js.css" disabled>
    <link rel="stylesheet" href="../blog.css?v=<?php echo time(); ?>">
    <meta name="description" content="<?php echo($settings["description"]); ?>">
    <script async src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha384-Qg00WFl9r0Xr6rUqNLv1ffTSSKEFFCDCKVyHZ+sVt8KuvG99nWw5RNvbhuKgif9z" crossorigin="anonymous"></script>
</head>
<body class="container">
