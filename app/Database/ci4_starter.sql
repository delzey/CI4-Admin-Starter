-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 12, 2025 at 08:18 AM
-- Server version: 8.0.40
-- PHP Version: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



-- ci_ci4_starter.auth_logins definition

CREATE TABLE `auth_logins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `identifier` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_type_identifier` (`id_type`,`identifier`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.auth_token_logins definition

CREATE TABLE `auth_token_logins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `identifier` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_type_identifier` (`id_type`,`identifier`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.db_backups definition

CREATE TABLE `db_backups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.group_permissions definition

CREATE TABLE `group_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) NOT NULL,
  `permissions` json NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ci_ci4_starter.menu_categories definition

CREATE TABLE `menu_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menu_category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `permission_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `position` int unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.migrations definition

CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.settings definition

CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text COLLATE utf8mb4_general_ci,
  `type` varchar(31) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'string',
  `context` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.users definition

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_message` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.auth_groups_users definition

CREATE TABLE `auth_groups_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_groups_users_user_id_foreign` (`user_id`),
  CONSTRAINT `auth_groups_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.auth_identities definition

CREATE TABLE `auth_identities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `secret` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `secret2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `extra` text COLLATE utf8mb4_general_ci,
  `force_reset` tinyint(1) NOT NULL DEFAULT '0',
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_secret` (`type`,`secret`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `auth_identities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.auth_permissions_users definition

CREATE TABLE `auth_permissions_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `permission` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_permissions_users_user_id_foreign` (`user_id`),
  CONSTRAINT `auth_permissions_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.auth_remember_tokens definition

CREATE TABLE `auth_remember_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `selector` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `hashedValidator` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int unsigned NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `auth_remember_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `auth_remember_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.menus definition

CREATE TABLE `menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned DEFAULT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `permission` varchar(100) DEFAULT NULL,
  `position` int unsigned DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `menus_parent_fk` (`parent_id`),
  KEY `menus_category_fk` (`category_id`),
  CONSTRAINT `menus_category_fk` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menus_parent_fk` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ci_ci4_starter.messages definition

CREATE TABLE `messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_general_ci NOT NULL,
  `sent_by` int unsigned DEFAULT NULL,
  `sent_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sent_by` (`sent_by`),
  CONSTRAINT `messages_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ci_ci4_starter.user_details definition

CREATE TABLE `user_details` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(3) DEFAULT NULL,
  `zip` int DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_complete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `auth_identities_user_id_fk2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;


-- ci_ci4_starter.message_recipients definition

CREATE TABLE `message_recipients` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `folder` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_folder_is_deleted` (`user_id`,`folder`,`is_deleted`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `message_recipients_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `message_recipients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_groups_users`
--

INSERT INTO `auth_groups_users` (`id`, `user_id`, `group`, `created_at`) VALUES
(1, 1, 'superadmin', '2025-11-03 06:47:42');

--
-- Dumping data for table `auth_identities`
--

INSERT INTO `auth_identities` (`id`, `user_id`, `type`, `name`, `secret`, `secret2`, `expires`, `extra`, `force_reset`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'email_password', NULL, 'developer@mail.io', '$2y$12$a6.3RUS8ZcxxVjZkx8E8pOLwRvfInc2rT1WXWwnEzfgC7ZlJ./Azm', NULL, NULL, 0, '2025-11-18 00:48:27', '2025-11-03 06:47:41', '2025-11-18 00:48:27'),
(2, 1, 'magic-link', NULL, 'f96af5753e43d99ae19c', NULL, '2025-11-14 00:56:24', NULL, 0, NULL, '2025-11-13 23:56:24', '2025-11-13 23:56:24');

--
-- Dumping data for table `auth_permissions_users`
--

INSERT INTO `auth_permissions_users` (`id`, `user_id`, `permission`, `created_at`) VALUES
(10, 1, 'admin.access', '2025-11-11 07:33:55'),
(11, 1, 'admin.settings', '2025-11-11 07:33:56'),
(12, 1, 'beta.access', '2025-11-11 07:33:57'),
(13, 1, 'users.manage-admins', '2025-11-11 07:33:59'),
(14, 1, 'users.create', '2025-11-11 07:34:00'),
(15, 1, 'users.edit', '2025-11-11 07:34:02'),
(16, 1, 'users.delete', '2025-11-11 07:34:04'),
(17, 1, 'settings.view', '2025-11-11 17:29:15'),
(18, 1, 'settings.create', '2025-11-11 17:29:15'),
(19, 1, 'settings.edit', '2025-11-11 17:29:16'),
(20, 1, 'settings.delete', '2025-11-11 17:29:17'),
(21, 1, 'menu-management.delete', '2025-11-11 17:29:18'),
(22, 1, 'menu-management.edit', '2025-11-11 17:29:19'),
(23, 1, 'menu-management.create', '2025-11-11 17:29:20'),
(24, 1, 'menu-management.view', '2025-11-11 17:29:21'),
(25, 1, 'groups.view', '2025-11-11 17:29:21'),
(26, 1, 'groups.create', '2025-11-11 17:29:22'),
(27, 1, 'groups.edit', '2025-11-11 17:29:24'),
(28, 1, 'groups.delete', '2025-11-11 17:29:25');

--
-- Dumping data for table `group_permissions`
--

INSERT INTO `group_permissions` (`id`, `group_name`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', '[\"groups.view\", \"groups.create\", \"groups.edit\", \"groups.delete\", \"menu-management.view\", \"menu-management.create\", \"menu-management.edit\", \"menu-management.delete\", \"messages.view\", \"messages.create\", \"messages.edit\", \"messages.delete\", \"settings.view\", \"settings.create\", \"settings.edit\", \"settings.delete\", \"users.manage-admins\", \"users.view\", \"users.create\", \"users.edit\", \"users.delete\"]', '2025-11-12 17:21:31', '2025-11-18 00:50:43'),
(2, 'admin', '[\"users.view\", \"settings.view\"]', '2025-11-12 17:21:31', '2025-11-12 17:21:31'),
(3, 'developer', '[\"groups.view\", \"groups.edit\", \"menu-management.view\", \"menu-management.edit\", \"settings.view\", \"users.manage-admins\", \"users.view\"]', '2025-11-12 23:56:35', '2025-11-12 23:56:35'),
(4, 'beta', '[]', '2025-11-13 00:00:18', '2025-11-14 04:11:21');

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `parent_id`, `category_id`, `title`, `icon`, `route`, `permission`, `position`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 7, 'Users', 'fa fa-users', '#', 'users.view', 10, 1, '2025-11-11 00:18:45', '2025-11-12 23:39:20'),
(2, 1, 7, 'Auth Permissions', 'fa fa-lock', 'auth-permissions', 'users.permissions', 20, 1, '2025-11-11 00:20:15', '2025-11-13 00:12:25'),
(3, 1, 7, 'Auth Groups', 'fa fa-shield', 'auth-groups', 'users.groups', 10, 1, '2025-11-11 00:30:25', '2025-11-13 03:11:21'),
(4, NULL, 1, 'Settings', 'fa fa-cog', '#', 'settings.view', 10, 1, '2025-11-11 06:33:24', '2025-11-12 23:39:44'),
(5, 4, 1, 'Menu Manager', 'fa fa-bars', 'menu-management', 'menu-management.view', 20, 1, '2025-11-11 15:16:59', '2025-11-14 04:12:22'),
(6, NULL, 7, 'Dashboard', 'fa fa-align-justify', 'dashboard', '', 1, 1, '2025-11-11 15:59:26', '2025-11-13 05:04:12'),
(10, 4, 1, 'General Settings', 'fa fa-clipboard', 'settings', 'settings.view', 10, 1, '2025-11-13 00:07:45', '2025-11-14 03:12:33'),
(11, 1, 7, 'Auth Users', 'fa fa-user', 'users', 'users.view', 5, 1, '2025-11-13 00:11:05', '2025-11-13 00:12:07'),
(12, NULL, 7, 'Messages', 'fa fa-envelope', 'messages', 'messages.read', 30, 1, '2025-11-18 00:49:17', '2025-11-19 15:56:06'),
(13, 12, 7, 'Inbox', 'fa fa-inbox', 'messages/inbox', 'messages.read', 10, 1, '2025-11-18 02:30:07', '2025-11-19 15:56:18'),
(14, 12, 7, 'Outbox', 'fa fa-inbox', 'messages/outbox', 'messages.read', 20, 1, '2025-11-18 02:32:51', '2025-11-19 15:58:55');

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`id`, `menu_category`, `permission_name`, `position`, `created_at`, `updated_at`) VALUES
(1, 'System Settings', 'settings.view', 20, '2025-11-09 01:27:36', '2025-11-14 04:22:58'),
(7, 'Main', NULL, 10, NULL, NULL);

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `subject`, `body`, `sent_by`, `sent_at`) VALUES
(1, 'This is a test message', 'gfdnf h sdgfg sdfgn dfgn sgsdgfndgn dfgn fg sdhasdfhs', 3, '2025-11-18 00:51:55'),
(2, 'This is a test message', 'gfdnf h sdgfg sdfgn dfgn sgsdgfndgn dfgn fg sdhasdfhs', 3, '2025-11-18 00:53:41');

--
-- Dumping data for table `message_recipients`
--

INSERT INTO `message_recipients` (`id`, `message_id`, `user_id`, `folder`, `is_read`, `is_deleted`, `created_at`) VALUES
(1, 1, 3, 'outbox', 0, 0, '2025-11-18 00:51:55'),
(3, 2, 3, 'outbox', 0, 0, '2025-11-18 00:53:41'),
(4, 2, 1, 'inbox', 0, 0, '2025-11-18 00:53:41');

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `class`, `key`, `value`, `type`, `context`, `created_at`, `updated_at`) VALUES
(1, 'Config\\App', 'theme', 'dark', 'string', NULL, '2025-11-11 06:16:56', '2025-11-13 00:19:37'),
(2, 'Config\\App', 'sidebar_collapsed', '0', 'boolean', NULL, '2025-11-11 06:19:35', '2025-11-13 00:19:37'),
(3, 'Config\\Auth', 'allowRegistration', '0', 'boolean', NULL, '2023-04-24 22:34:08', '2025-04-18 04:11:58'),
(4, 'Config\\Auth', 'minimumPasswordLength', '8', 'integer', NULL, '2023-04-24 22:34:08', '2025-04-18 04:11:58'),
(5, 'Config\\AuthGroups', 'defaultGroup', 'user', 'string', NULL, '2023-04-24 22:34:08', '2025-04-18 04:11:58'),
(6, 'Config\\Auth', 'actions', 'a:2:{s:8:\"register\";s:56:\"CodeIgniter\\Shield\\Authentication\\Actions\\EmailActivator\";s:5:\"login\";N;}', 'array', NULL, '2023-04-24 22:34:08', '2025-04-18 04:11:58'),
(7, 'Config\\Auth', 'sessionConfig', 'a:4:{s:5:\"field\";s:9:\"logged_in\";s:16:\"allowRemembering\";b:0;s:18:\"rememberCookieName\";s:8:\"remember\";s:14:\"rememberLength\";s:4:\"3600\";}', 'array', NULL, '2023-04-24 22:34:08', '2025-04-18 04:11:58'),
(8, 'Config\\Site', 'siteName', 'BSS Base Starter', 'string', NULL, '2023-04-24 22:51:29', '2025-07-21 08:32:58'),
(9, 'Config\\Site', 'siteOnline', '1', 'string', NULL, '2023-04-24 22:51:29', '2025-07-21 08:32:58'),
(10, 'Config\\App', 'appTimezone', 'America/Chicago', 'string', NULL, '2023-04-24 22:51:29', '2025-07-21 08:32:58'),
(11, 'Config\\App', 'dateFormat', 'M j, Y', 'string', NULL, '2023-04-24 22:51:29', '2025-07-21 08:32:58'),
(12, 'Config\\App', 'timeFormat', 'g:i A', 'string', NULL, '2023-04-24 22:51:29', '2025-07-21 08:32:58'),
(13, 'Config\\Auth', 'passwordValidators', 'a:3:{i:0;s:64:\"CodeIgniter\\Shield\\Authentication\\Passwords\\CompositionValidator\";i:1;s:68:\"CodeIgniter\\Shield\\Authentication\\Passwords\\NothingPersonalValidator\";i:2;s:63:\"CodeIgniter\\Shield\\Authentication\\Passwords\\DictionaryValidator\";}', 'array', NULL, '2023-06-21 03:54:33', '2025-04-18 04:11:58'),
(14, 'Config\\Site', 'siteDescription', 'BitShout Solutions Software', 'string', NULL, '2025-04-18 04:11:33', '2025-07-21 08:32:58'),
(15, 'Config\\Site', 'siteKeyWords', 'software,users,user management,codeigniter', 'string', NULL, '2025-04-18 04:11:33', '2025-07-21 08:32:58');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `status`, `status_message`, `active`, `last_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'BSS-Admin', '', '', 1, '2025-11-19 16:51:40', '2025-11-03 06:47:40', '2025-11-05 15:29:59', NULL),
(3, 'bss-users', '', 'Some default details', 0, NULL, '2025-11-05 04:40:24', '2025-11-13 05:18:06', NULL);

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`id`, `user_id`, `firstname`, `middlename`, `lastname`, `phone`, `address1`, `address2`, `city`, `state`, `zip`, `updated_at`, `profile_complete`, `created_at`) VALUES
(12, 1, 'BSS', '', 'Admin', '5121231234', '123 Some Way', '', 'Austin', 'TX', 78682, '2025-11-14 01:31:53', 1, '2025-11-13 19:31:53');
COMMIT;