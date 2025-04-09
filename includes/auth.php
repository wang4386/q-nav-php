<?php
require_once __DIR__ . '/functions.php';

session_name(SESSION_NAME);
session_start();

// 检查是否登录
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// 登录
function login($password) {
    $settings = getSettings();
    
    if (password_verify($password, $settings['admin_password'])) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    
    return false;
}

// 登出
function logout() {
    session_unset();
    session_destroy();
}

// 修改密码
function changePassword($oldPassword, $newPassword) {
    $settings = getSettings();
    
    if (password_verify($oldPassword, $settings['admin_password'])) {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        db()->prepare("UPDATE settings SET admin_password = ? WHERE id = 1")
           ->execute([$newHash]);
        return true;
    }
    
    return false;
}

// 检查登录状态，未登录则跳转
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin.php');
        exit;
    }
}
