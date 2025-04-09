<?php
require_once __DIR__ . '/db.php';

// 获取网站设置
function getSettings() {
    $stmt = db()->query("SELECT * FROM settings WHERE id = 1");
    return $stmt->fetch();
}

// 更新网站设置
function updateSettings($data) {
    $sql = "UPDATE settings SET 
            website_logo = :website_logo,
            website_title = :website_title,
            footer_info = :footer_info
            WHERE id = 1";
    return db()->prepare($sql)->execute($data);
}

// 获取所有分类
function getCategories() {
    $stmt = db()->query("SELECT * FROM categories ORDER BY sort_order ASC");
    return $stmt->fetchAll();
}

// 添加分类
function addCategory($name) {
    $sql = "INSERT INTO categories (name) VALUES (?)";
    $stmt = db()->prepare($sql);
    return $stmt->execute([$name]);
}

// 删除分类
function deleteCategory($id) {
    // 先更新该分类下的链接到默认分类
    $defaultCat = db()->query("SELECT id FROM categories ORDER BY sort_order ASC LIMIT 1")->fetch();
    if ($defaultCat) {
        db()->prepare("UPDATE links SET category_id = ? WHERE category_id = ?")
            ->execute([$defaultCat['id'], $id]);
    }
    
    // 删除分类
    return db()->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
}

// 获取所有链接
function getLinks($categoryId = null) {
    $sql = "SELECT l.*, c.name AS category_name FROM links l 
            JOIN categories c ON l.category_id = c.id";
    
    if ($categoryId) {
        $sql .= " WHERE l.category_id = ?";
        $stmt = db()->prepare($sql);
        $stmt->execute([$categoryId]);
    } else {
        $stmt = db()->query($sql);
    }
    
    return $stmt->fetchAll();
}

// 添加链接
function addLink($data) {
    $sql = "INSERT INTO links (name, url, category_id, description, status, logo) 
            VALUES (:name, :url, :category_id, :description, :status, :logo)";
    return db()->prepare($sql)->execute($data);
}

// 更新链接
function updateLink($id, $data) {
    $sql = "UPDATE links SET 
            name = :name,
            url = :url,
            category_id = :category_id,
            description = :description,
            status = :status,
            logo = :logo
            WHERE id = :id";
    $data['id'] = $id;
    return db()->prepare($sql)->execute($data);
}

// 删除链接
function deleteLink($id) {
    return db()->prepare("DELETE FROM links WHERE id = ?")->execute([$id]);
}

// 更新链接排序
function updateLinkOrder($order) {
    $db = db();
    $db->beginTransaction();
    try {
        foreach ($order as $index => $id) {
            $db->prepare("UPDATE links SET sort_order = ? WHERE id = ?")
               ->execute([$index, $id]);
        }
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// 安全输出
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// JSON响应
function jsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// 验证URL
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}
