<?php
require_once __DIR__ . '/../includes/functions.php';
$settings = getSettings();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($settings['website_title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    <link rel='stylesheet' href='https://chinese-fonts-cdn.deno.dev/packages/lxgwwenkai/dist/LXGWWenKai-Bold/result.css' />
</head>
<body>
    <div class="frontend">
        <div class="nav-header">
            <div class="logo-container">
                <?php if (!empty($settings['website_logo'])): ?>
                    <img id="website-logo" src="<?= e($settings['website_logo']) ?>" alt="<?= e($settings['website_title']) ?> Logo">
                <?php endif; ?>
                <h1 id="website-title"><?= e($settings['website_title']) ?></h1>
            </div>
        </div>

        <div class="nav-categories" id="category-filters">
            <button class="category-btn active" data-category="all">全部</button>
        </div>

        <ul class="nav-list" id="nav-links"></ul>

        <div class="pagination-container">
            <div class="pagination-controls">
                <button class="btn-secondary" id="prev-page" disabled>上页</button>
            </div>

            <div class="admin-button-container">
                <button class="btn-secondary admin-button-small" onclick="window.location.href='/admin.php'">登录</button>
            </div>

            <div class="pagination-controls">
                <button class="btn-secondary" id="next-page">下页</button>
            </div>
        </div>

        <div class="footer" id="footer-info">
            <p><?= e($settings['footer_info']) ?></p>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
