CREATE DATABASE IF NOT EXISTS `qnav` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `qnav`;

-- 网站设置表
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website_logo` varchar(255) DEFAULT '',
  `website_title` varchar(100) NOT NULL DEFAULT 'My Website Favorites',
  `footer_info` varchar(255) NOT NULL DEFAULT '© 青柠',
  `admin_password` varchar(255) NOT NULL DEFAULT 'admin123456',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 分类表
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 链接表
CREATE TABLE `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('normal','error') NOT NULL DEFAULT 'normal',
  `logo` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 初始数据
INSERT INTO `settings` VALUES (1, '', 'My Website Favorites', '© 青柠', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO `categories` (`id`, `name`, `sort_order`) VALUES 
(1, '博客', 0),
(2, '论坛', 1),
(3, '工具', 2);

INSERT INTO `links` (`name`, `url`, `category_id`, `description`, `status`, `logo`, `sort_order`) VALUES 
('倾城于你', 'https://qninq.cn', 1, '', 'normal', 'https://qninq.cn/favicon.ico', 0),
('大佬论坛', 'https://www.dalao.net/', 2, '', 'normal', 'https://icon.qninq.cn/favicon/www.dalao.net?larger=true', 1),
('NodeSeek', 'https://www.nodeseek.com/', 2, '', 'normal', 'https://icon.qninq.cn/favicon/www.nodeseek.com?larger=true', 2),
('LinuxDo', 'https://linux.do/', 2, '', 'normal', 'https://icon.qninq.cn/favicon/linux.do?larger=true', 3);
