<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    // 获取链接
    case 'get-links':
        $categoryId = $_GET['category_id'] ?? null;
        $links = getLinks($categoryId);
        jsonResponse(['success' => true, 'data' => $links]);
        break;
        
    // 添加链接
    case 'add-link':
        requireLogin();
        $data = [
            'name' => $_POST['name'] ?? '',
            'url' => $_POST['url'] ?? '',
            'category_id' => $_POST['category_id'] ?? 0,
            'description' => $_POST['description'] ?? '',
            'status' => $_POST['status'] ?? 'normal',
            'logo' => $_POST['logo'] ?? ''
        ];
        
        if (empty($data['name']) || empty($data['url']) || !isValidUrl($data['url'])) {
            jsonResponse(['success' => false, 'message' => '名称和有效URL是必填项']);
        }
        
        if (addLink($data)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => '添加失败']);
        break;
        
    // 更新链接
    case 'update-link':
        requireLogin();
        $id = $_POST['id'] ?? 0;
        $data = [
            'name' => $_POST['name'] ?? '',
            'url' => $_POST['url'] ?? '',
            'category_id' => $_POST['category_id'] ?? 0,
            'description' => $_POST['description'] ?? '',
            'status' => $_POST['status'] ?? 'normal',
            'logo' => $_POST['logo'] ?? ''
        ];
        
        if (empty($data['name']) || empty($data['url']) || !isValidUrl($data['url'])) {
            jsonResponse(['success' => false, 'message' => '名称和有效URL是必填项']);
        }
        
        if (updateLink($id, $data)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => '更新失败']);
        break;
        
    // 删除链接
    case 'delete-link':
        requireLogin();
        $id = $_POST['id'] ?? 0;
        if (deleteLink($id)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => '删除失败']);
        break;
        
    // 获取分类
    case 'get-categories':
        jsonResponse(['success' => true, 'data' => getCategories()]);
        break;
        
    // 添加分类
    case 'add-category':
        requireLogin();
        $name = $_POST['name'] ?? '';
        if (empty($name)) {
            jsonResponse(['success' => false, 'message' => '分类名称不能为空']);
        }
        
        if (addCategory($name)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => '添加失败']);
        break;
        
    // 删除分类
    case 'delete-category':
        requireLogin();
        $id = $_POST['id'] ?? 0;
        if (deleteCategory($id)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => '删除失败']);
        break;
        
    // 获取设置
    case 'get-settings':
        $settings = getSettings();
        unset($settings['admin_password']); // 不返回密码
        jsonResponse(['success' => true, 'data' => $settings]);
        break;
        
    // 更新设置
    case 'update-settings':
        requireLogin();
        $data = [
            'website_logo' => $_POST['website_logo'] ?? '',
            'website_title' => $_POST['website_title'] ?? '',
            'footer_info' => $_POST['footer_info'] ?? ''
        ];
        
        if (updateSettings($data)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => '更新失败']);
        break;
        
    // 登录
    case 'login':
        $password = $_POST['password'] ?? '';
        if (login($password)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => '密码错误']);
        break;
        
    // 登出
    case 'logout':
        logout();
        jsonResponse(['success' => true]);
        break;
        
    // 修改密码
    case 'change-password':
        requireLogin();
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        if (empty($newPassword) || strlen($newPassword) < 6) {
            jsonResponse(['success' => false, 'message' => '新密码至少需要6位']);
        }
        
        if (changePassword($oldPassword, $newPassword)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => '旧密码错误']);
        break;
        
    // 更新链接排序
    case 'update-order':
        requireLogin();
        $order = json_decode($_POST['order'] ?? '[]', true);
        if (updateLinkOrder($order)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => '排序更新失败']);
        break;
        
    default:
        jsonResponse($response, 400);
}

jsonResponse($response);
