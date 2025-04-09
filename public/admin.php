<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$settings = getSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (login($_POST['password'])) {
        header('Location: /admin.php');
        exit;
    }
    $error = '密码错误';
}

if (isLoggedIn()) {
    // 已登录，显示管理界面
    $categories = getCategories();
    $links = getLinks();
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($settings['website_title']) ?> - 管理后台</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="/assets/css/style.css">
        <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
        <link rel='stylesheet' href='https://chinese-fonts-cdn.deno.dev/packages/lxgwwenkai/dist/LXGWWenKai-Bold/result.css' />
    </head>
    <body>
        <div class="backend">
            <div class="admin-panel">
                <div class="page-title">
                    <h2>链接管理</h2>
                    <div class="action-buttons">
                        <button class="btn-secondary" onclick="window.location.href='/'">返回前台</button>
                        <button class="btn-secondary" onclick="logout()">退出登录</button>
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h3>网站设置</h3>
                    <div class="form-group">
                        <input type="text" id="website-logo-input" placeholder="输入网站LOGO URL（可选）" value="<?= e($settings['website_logo']) ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" id="website-title-input" placeholder="输入网站标题" value="<?= e($settings['website_title']) ?>">
                    </div>
                    <button class="btn-primary" onclick="saveWebsiteSettings()">保存设置</button>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h3>分类管理</h3>
                    <div class="form-group">
                        <input type="text" id="new-category" placeholder="输入新分类名称">
                    </div>
                    <button class="btn-primary" onclick="addNewCategory()">添加分类</button>
                    <div class="categories-list" id="categories-list">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-item">
                                <span><?= e($category['name']) ?></span>
                                <button onclick="deleteCategory(<?= $category['id'] ?>)">✕</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h3>页脚信息</h3>
                    <div class="form-group">
                        <textarea id="footer-text" placeholder="输入页脚信息"><?= e(str_replace('© 青柠', '', $settings['footer_info'])) ?></textarea>
                    </div>
                    <button class="btn-primary" onclick="saveFooterInfo()">保存页脚信息</button>
                </div>

                <div class="links-table-container">
                    <table class="links-table">
                        <thead>
                            <tr>
                                <th>拖动</th>
                                <th>名称</th>
                                <th>网址</th>
                                <th>分类</th>
                                <th>简介</th>
                                <th>状态</th>
                                <th>Logo</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="links-list">
                            <?php foreach ($links as $link): ?>
                                <tr data-id="<?= $link['id'] ?>">
                                    <td class="arrow-cell">
                                        <button class="arrow-btn up-arrow" onclick="moveUp(this.closest('tr'))">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button class="arrow-btn down-arrow" onclick="moveDown(this.closest('tr'))">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input value="<?= e($link['name']) ?>" placeholder="名称" class="name-input">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input value="<?= e($link['url']) ?>" placeholder="网址" class="url-input">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <select class="category-select">
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $link['category_id'] ? 'selected' : '' ?>>
                                                        <?= e($category['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <textarea class="description-textarea"><?= e($link['description']) ?></textarea>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <select class="status-select">
                                                <option value="normal" <?= $link['status'] === 'normal' ? 'selected' : '' ?>>正常</option>
                                                <option value="error" <?= $link['status'] !== 'normal' ? 'selected' : '' ?>>维护</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type="text" placeholder="https://example.com/logo.png" value="<?= e($link['logo'] ?? '') ?>" class="logo-input">
                                        </div>
                                    </td>
                                    <td class="action-cell">
                                        <button class="btn-secondary btn-save" onclick="saveLink(this.closest('tr'))">保存</button>
                                        <button class="btn-secondary btn-delete" onclick="deleteLink(this.closest('tr'))">删除</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="action-buttons" style="margin-top: 2rem;">
                    <button class="btn-primary" onclick="addNewLink()">+ 新增链接</button>
                    <button class="btn-secondary" onclick="saveAllLinks()">全部保存</button>
                    <input type="file" id="import-file" style="display: none;" onchange="importLinks(this.files[0])">
                    <button class="btn-secondary" onclick="document.getElementById('import-file').click()">导入链接</button>
                    <button class="btn-secondary" onclick="exportLinks()">导出链接</button>
                </div>

                <div class="password-change">
                    <h3>修改密码</h3>
                    <div class="form-group">
                        <input type="password" id="old-password" placeholder="输入旧密码">
                    </div>
                    <div class="form-group">
                        <input type="password" id="new-password" placeholder="输入新密码">
                    </div>
                    <div class="form-group">
                        <input type="password" id="confirm-password" placeholder="确认新密码">
                    </div>
                    <button class="btn-success" onclick="changePassword()">修改密码</button>
                </div>

                <div class="admin-footer">
                    <p><?= e($settings['footer_info']) ?></p>
                </div>
            </div>
        </div>

        <script src="/assets/js/app.js"></script>
    </body>
    </html>
    <?php
} else {
    // 未登录，显示登录界面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($settings['website_title']) ?> - 管理员登录</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="/assets/css/style.css">
        <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
        <link rel='stylesheet' href='https://chinese-fonts-cdn.deno.dev/packages/lxgwwenkai/dist/LXGWWenKai-Bold/result.css' />
    </head>
    <body>
        <div class="backend">
            <div class="login-form">
                <h2>管理员登录</h2>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <input type="password" name="password" placeholder="输入管理密码" required>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button type="button" class="btn-secondary" onclick="window.location.href='/'" style="flex: 1;">返回</button>
                        <button type="submit" class="btn-primary" style="flex: 1;">登录</button>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
}
