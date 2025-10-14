/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `capstone_project` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `capstone_project`;

CREATE TABLE IF NOT EXISTS `audit_trails` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_trails_user_id_foreign` (`user_id`),
  KEY `audit_trails_role_id_foreign` (`role_id`),
  CONSTRAINT `audit_trails_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `audit_trails_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `billing` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stall_id` bigint unsigned NOT NULL,
  `utility_type` enum('Rent','Electricity','Water') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `penalty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount_after_due` decimal(10,2) DEFAULT NULL,
  `previous_reading` decimal(10,2) DEFAULT NULL,
  `current_reading` decimal(10,2) DEFAULT NULL,
  `consumption` decimal(10,2) DEFAULT NULL,
  `rate` decimal(10,4) DEFAULT NULL,
  `due_date` date NOT NULL,
  `disconnection_date` date DEFAULT NULL,
  `status` enum('unpaid','paid','late') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `billing_stall_id_foreign` (`stall_id`),
  CONSTRAINT `billing_stall_id_foreign` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `billing` (`id`, `stall_id`, `utility_type`, `period_start`, `period_end`, `amount`, `penalty`, `amount_after_due`, `previous_reading`, `current_reading`, `consumption`, `rate`, `due_date`, `disconnection_date`, `status`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Rent', '2025-08-01', '2025-08-31', 2500.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-09-05', '2025-09-15', 'unpaid', '2025-08-22 18:32:28', '2025-08-22 18:32:28'),
	(2, 1, 'Electricity', '2025-08-01', '2025-08-31', 3500.00, 0.00, NULL, 1200.00, 1450.00, 250.00, 14.0000, '2025-09-10', '2025-09-20', 'unpaid', '2025-08-22 18:32:28', '2025-08-22 18:32:28'),
	(3, 1, 'Water', '2025-08-01', '2025-08-31', 1950.00, 0.00, NULL, 850.00, 900.00, 50.00, 39.0000, '2025-09-08', '2025-09-18', 'unpaid', '2025-08-22 18:32:28', '2025-08-22 18:32:28'),
	(4, 1, 'Rent', '2025-07-01', '2025-07-31', 2500.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-08-05', '2025-08-15', 'paid', '2025-08-22 18:32:28', '2025-08-22 18:32:28'),
	(5, 1, 'Electricity', '2025-07-01', '2025-07-31', 3100.00, 0.00, NULL, 980.00, 1200.00, 220.00, 14.0900, '2025-08-10', '2025-08-20', 'paid', '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(6, 1, 'Water', '2025-07-01', '2025-07-31', 1700.00, 0.00, NULL, 810.00, 850.00, 40.00, 42.5000, '2025-08-08', '2025-08-18', 'paid', '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(7, 1, 'Rent', '2025-04-01', '2025-04-30', 2500.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-05-05', '2025-05-15', 'unpaid', '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(8, 1, 'Electricity', '2025-04-01', '2025-04-30', 3200.00, 0.00, NULL, 750.00, 980.00, 230.00, 13.9100, '2025-05-10', '2025-05-20', 'unpaid', '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(9, 1, 'Water', '2025-04-01', '2025-04-30', 1800.00, 0.00, NULL, 770.00, 810.00, 40.00, 45.0000, '2025-05-08', '2025-05-18', 'unpaid', '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(10, 1, 'Rent', '2025-03-01', '2025-03-31', 2500.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-04-05', '2025-04-15', 'paid', '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(11, 1, 'Electricity', '2025-03-01', '2025-03-31', 3200.00, 0.00, NULL, 550.00, 750.00, 200.00, 16.0000, '2025-04-10', '2025-04-20', 'paid', '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(12, 1, 'Water', '2025-03-01', '2025-03-31', 1800.00, 0.00, NULL, 730.00, 770.00, 40.00, 45.0000, '2025-04-08', '2025-04-18', 'paid', '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(13, 1, 'Rent', '2024-12-01', '2024-12-31', 2500.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-01-05', '2025-01-15', 'paid', '2025-08-22 18:32:30', '2025-08-22 18:32:30'),
	(14, 1, 'Electricity', '2024-12-01', '2024-12-31', 3800.00, 0.00, NULL, 300.00, 550.00, 250.00, 15.2000, '2025-01-10', '2025-01-20', 'paid', '2025-08-22 18:32:30', '2025-08-22 18:32:30'),
	(15, 1, 'Water', '2024-12-01', '2024-12-31', 490.00, 0.00, NULL, 720.00, 730.00, 10.00, 49.0000, '2025-01-05', '2025-01-15', 'paid', '2025-08-22 18:32:30', '2025-08-22 18:32:30'),
	(50, 6, 'Rent', '2025-08-01', '2025-08-31', 3000.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-09-05', NULL, 'unpaid', NULL, NULL),
	(53, 6, 'Rent', '2025-08-01', '2025-08-31', 3000.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-09-05', NULL, 'unpaid', NULL, NULL);

CREATE TABLE IF NOT EXISTS `billing_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `billing_id` bigint unsigned NOT NULL,
  `field_changed` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint unsigned NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `billing_histories_billing_id_foreign` (`billing_id`),
  KEY `billing_histories_changed_by_foreign` (`changed_by`),
  CONSTRAINT `billing_histories_billing_id_foreign` FOREIGN KEY (`billing_id`) REFERENCES `billing` (`id`) ON DELETE CASCADE,
  CONSTRAINT `billing_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
	(1, 'default', '{"uuid":"74612455-6b3d-4dbe-ba52-bb5b9818fce6","displayName":"App\\\\Events\\\\TestEvent","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Broadcasting\\\\BroadcastEvent","command":"O:38:\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\":14:{s:5:\\"event\\";O:20:\\"App\\\\Events\\\\TestEvent\\":1:{s:7:\\"message\\";s:23:\\"This is a test message.\\";}s:5:\\"tries\\";N;s:7:\\"timeout\\";N;s:7:\\"backoff\\";N;s:13:\\"maxExceptions\\";N;s:10:\\"connection\\";N;s:5:\\"queue\\";N;s:5:\\"delay\\";N;s:11:\\"afterCommit\\";N;s:10:\\"middleware\\";a:0:{}s:7:\\"chained\\";a:0:{}s:15:\\"chainConnection\\";N;s:10:\\"chainQueue\\";N;s:19:\\"chainCatchCallbacks\\";N;}"},"createdAt":1756941060,"delay":null}', 0, NULL, 1756941060, 1756941060),
	(2, 'default', '{"uuid":"f5a7d31a-f9c8-4528-81d9-5437424c4e5c","displayName":"App\\\\Events\\\\TestEvent","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Broadcasting\\\\BroadcastEvent","command":"O:38:\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\":14:{s:5:\\"event\\";O:20:\\"App\\\\Events\\\\TestEvent\\":1:{s:7:\\"message\\";s:23:\\"This is a test message.\\";}s:5:\\"tries\\";N;s:7:\\"timeout\\";N;s:7:\\"backoff\\";N;s:13:\\"maxExceptions\\";N;s:10:\\"connection\\";N;s:5:\\"queue\\";N;s:5:\\"delay\\";N;s:11:\\"afterCommit\\";N;s:10:\\"middleware\\";a:0:{}s:7:\\"chained\\";a:0:{}s:15:\\"chainConnection\\";N;s:10:\\"chainQueue\\";N;s:19:\\"chainCatchCallbacks\\";N;}"},"createdAt":1756941833,"delay":null}', 0, NULL, 1756941833, 1756941833),
	(3, 'default', '{"uuid":"06b6cbc4-b9e5-4533-ac9a-5594ace6964f","displayName":"App\\\\Events\\\\TestEvent","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Broadcasting\\\\BroadcastEvent","command":"O:38:\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\":14:{s:5:\\"event\\";O:20:\\"App\\\\Events\\\\TestEvent\\":1:{s:7:\\"message\\";s:23:\\"This is a test message.\\";}s:5:\\"tries\\";N;s:7:\\"timeout\\";N;s:7:\\"backoff\\";N;s:13:\\"maxExceptions\\";N;s:10:\\"connection\\";N;s:5:\\"queue\\";N;s:5:\\"delay\\";N;s:11:\\"afterCommit\\";N;s:10:\\"middleware\\";a:0:{}s:7:\\"chained\\";a:0:{}s:15:\\"chainConnection\\";N;s:10:\\"chainQueue\\";N;s:19:\\"chainCatchCallbacks\\";N;}"},"createdAt":1756942666,"delay":null}', 0, NULL, 1756942666, 1756942666);

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000001_create_cache_table', 1),
	(2, '0001_01_01_000002_create_jobs_table', 1),
	(3, '2025_08_20_072813_create_roles_table', 1),
	(4, '2025_08_20_072958_create_users_table', 1),
	(5, '2025_08_20_073101_create_sections_table', 1),
	(6, '2025_08_20_073233_create_stalls_table', 1),
	(7, '2025_08_20_073722_create_rates_table', 1),
	(8, '2025_08_20_090139_create_rate_histories_table', 1),
	(9, '2025_08_20_090802_add_status_last_login_table', 1),
	(10, '2025_08_21_040321_create_audit_trails_table', 1),
	(11, '2025_08_21_040327_create_billing_table', 1),
	(12, '2025_08_21_040328_create_billing_histories_table', 1),
	(13, '2025_08_21_040333_create_payments_table', 1),
	(14, '2025_08_21_040339_create_utility_readings_table', 1),
	(15, '2025_08_21_040345_create_reading_edit_requests_table', 1),
	(16, '2025_08_21_040349_create_schedules_table', 1),
	(17, '2025_08_21_040358_create_schedule_histories_table', 1),
	(18, '2025_08_21_051402_create_sessions_table', 1),
	(19, '2025_08_23_021027_add_breakdown_details_to_billings_table', 1),
	(20, '2025_08_23_150005_add_rates_to_stalls_table', 2),
	(21, '2025_08_24_135735_create_personal_access_tokens_table', 3),
	(22, '2025_08_26_135822_add_unique_constraint_to_username_in_users_table', 4);

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recipient_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned DEFAULT NULL,
  `channel` enum('sms','in_app') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('sent','failed','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_notifications_recipient` (`recipient_id`),
  KEY `fk_notifications_sender` (`sender_id`),
  CONSTRAINT `fk_notifications_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `billing_id` bigint unsigned NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `penalty` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_billing_id_foreign` (`billing_id`),
  CONSTRAINT `payments_billing_id_foreign` FOREIGN KEY (`billing_id`) REFERENCES `billing` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`id`, `billing_id`, `amount_paid`, `payment_date`, `penalty`, `discount`, `created_at`, `updated_at`) VALUES
	(1, 4, 2500.00, '2025-08-04', 0.00, 0.00, '2025-08-22 18:32:28', '2025-08-22 18:32:28'),
	(2, 5, 3100.00, '2025-08-08', 0.00, 0.00, '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(3, 6, 1700.00, '2025-08-07', 0.00, 0.00, '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(4, 10, 2500.00, '2025-04-03', 0.00, 0.00, '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(5, 11, 3200.00, '2025-04-08', 0.00, 0.00, '2025-08-22 18:32:29', '2025-08-22 18:32:29'),
	(6, 12, 1800.00, '2025-04-06', 0.00, 0.00, '2025-08-22 18:32:30', '2025-08-22 18:32:30'),
	(7, 13, 2500.00, '2025-01-03', 0.00, 0.00, '2025-08-22 18:32:30', '2025-08-22 18:32:30'),
	(8, 14, 3800.00, '2025-01-08', 0.00, 0.00, '2025-08-22 18:32:30', '2025-08-22 18:32:30'),
	(9, 15, 490.00, '2025-01-03', 0.00, 0.00, '2025-08-22 18:32:30', '2025-08-22 18:32:30');

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `utility_type` enum('Rent','Electricity','Water') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_id` bigint unsigned DEFAULT NULL,
  `rate` decimal(10,2) NOT NULL,
  `monthly_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rates_section_id_foreign` (`section_id`),
  CONSTRAINT `rates_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rates` (`id`, `utility_type`, `section_id`, `rate`, `monthly_rate`, `created_at`, `updated_at`) VALUES
	(1, 'Electricity', NULL, 11.00, 1500.00, '2025-08-26 03:38:31', '2025-09-05 00:55:52'),
	(2, 'Water', NULL, 5.00, 200.00, '2025-08-26 03:38:31', '2025-08-31 04:28:41');

CREATE TABLE IF NOT EXISTS `rate_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rate_id` bigint unsigned NOT NULL,
  `old_rate` decimal(10,2) NOT NULL,
  `new_rate` decimal(10,2) NOT NULL,
  `changed_by` bigint unsigned NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `rate_histories_rate_id_foreign` (`rate_id`),
  KEY `rate_histories_changed_by_foreign` (`changed_by`),
  CONSTRAINT `rate_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `rate_histories_rate_id_foreign` FOREIGN KEY (`rate_id`) REFERENCES `rates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rate_histories` (`id`, `rate_id`, `old_rate`, `new_rate`, `changed_by`, `changed_at`) VALUES
	(1, 1, 12.00, 13.00, 1, '2025-08-25 21:28:33'),
	(5, 1, 13.00, 15.00, 1, '2025-08-25 21:47:12'),
	(6, 1, 15.00, 11.00, 1, '2025-08-26 08:10:02'),
	(7, 1, 11.00, 10.00, 1, '2025-08-26 16:15:58'),
	(8, 1, 10.00, 12.00, 1, '2025-08-31 02:38:02'),
	(10, 1, 12.00, 11.00, 1, '2025-08-31 02:38:29'),
	(11, 1, 11.00, 12.00, 1, '2025-08-31 02:40:29'),
	(13, 1, 12.00, 11.00, 1, '2025-08-31 02:40:56'),
	(15, 1, 11.00, 15.00, 1, '2025-08-31 02:43:30'),
	(16, 1, 15.00, 12.00, 1, '2025-09-01 06:02:41'),
	(17, 1, 12.00, 13.00, 1, '2025-09-01 06:03:03'),
	(18, 1, 13.00, 10.00, 1, '2025-09-01 06:28:36'),
	(19, 1, 10.00, 12.00, 1, '2025-09-01 06:34:38'),
	(20, 1, 12.00, 20.00, 1, '2025-09-01 06:42:18'),
	(21, 1, 20.00, 15.00, 1, '2025-09-01 06:42:41'),
	(22, 1, 15.00, 11.00, 1, '2025-09-01 07:04:13'),
	(23, 1, 11.00, 15.00, 1, '2025-09-03 00:18:24'),
	(24, 1, 15.00, 10.00, 1, '2025-09-04 21:09:17'),
	(25, 1, 10.00, 11.00, 1, '2025-09-05 00:55:52');

CREATE TABLE IF NOT EXISTS `reading_edit_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reading_id` bigint unsigned NOT NULL,
  `requested_by` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reading_edit_requests_reading_id_foreign` (`reading_id`),
  KEY `reading_edit_requests_requested_by_foreign` (`requested_by`),
  KEY `reading_edit_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `reading_edit_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `reading_edit_requests_reading_id_foreign` FOREIGN KEY (`reading_id`) REFERENCES `utility_readings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reading_edit_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `report_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `generated_by` bigint unsigned NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_reports_generated_by` (`generated_by`),
  CONSTRAINT `fk_reports_generated_by` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
	(1, 'Admin', '2025-08-22 18:32:27', '2025-08-22 18:32:27'),
	(2, 'Vendor', '2025-08-22 18:32:27', '2025-08-22 18:32:27'),
	(3, 'Staff', '2025-08-22 18:32:27', '2025-08-22 18:32:27'),
	(4, 'Meter Reader Clerk', '2025-08-22 18:32:27', '2025-08-22 18:32:27');

CREATE TABLE IF NOT EXISTS `schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_type` enum('Meter Reading','Due Date','Disconnection','SMS Notification') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schedule_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `schedules` (`id`, `schedule_type`, `description`, `schedule_date`, `created_at`, `updated_at`) VALUES
	(1, 'Meter Reading', '30', '2025-01-01', '2025-08-27 13:35:41', '2025-09-05 00:56:02'),
	(3, 'Due Date', '27', '2025-01-01', '2025-08-27 15:08:40', '2025-09-05 00:56:17'),
	(4, 'Disconnection', '30', '2025-01-01', '2025-08-27 15:08:40', '2025-09-05 00:56:18');

CREATE TABLE IF NOT EXISTS `schedule_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` bigint unsigned NOT NULL,
  `field_changed` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint unsigned NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `schedule_histories_schedule_id_foreign` (`schedule_id`),
  KEY `schedule_histories_changed_by_foreign` (`changed_by`),
  CONSTRAINT `schedule_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `schedule_histories_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `schedule_histories` (`id`, `schedule_id`, `field_changed`, `old_value`, `new_value`, `changed_by`, `changed_at`) VALUES
	(1, 1, 'schedule_day', '25', '28', 1, '2025-08-27 05:38:21'),
	(2, 1, 'schedule_day', '28', '30', 1, '2025-08-27 05:39:28'),
	(3, 3, 'schedule_day', '15', '29', 1, '2025-08-27 07:29:07'),
	(4, 4, 'schedule_day', '25', '30', 1, '2025-08-27 07:29:07'),
	(5, 3, 'schedule_day', '29', '24', 1, '2025-08-27 07:33:02'),
	(6, 4, 'schedule_day', '30', '28', 1, '2025-08-27 07:33:02'),
	(7, 1, 'schedule_day', '30', '27', 1, '2025-08-31 01:35:06'),
	(8, 3, 'schedule_day', '24', '29', 1, '2025-08-31 01:35:33'),
	(9, 4, 'schedule_day', '28', '30', 1, '2025-08-31 01:35:33'),
	(10, 1, 'schedule_day', '27', '20', 1, '2025-09-01 06:04:14'),
	(11, 3, 'schedule_day', '29', '25', 1, '2025-09-01 06:05:17'),
	(12, 4, 'schedule_day', '30', '27', 1, '2025-09-01 06:05:17'),
	(13, 3, 'schedule_day', '25', '26', 1, '2025-09-01 06:47:20'),
	(14, 1, 'schedule_day', '20', '23', 1, '2025-09-01 06:47:39'),
	(15, 1, 'schedule_day', '23', '28', 1, '2025-09-01 07:04:25'),
	(16, 3, 'schedule_day', '26', '21', 1, '2025-09-01 07:04:37'),
	(17, 4, 'schedule_day', '27', '25', 1, '2025-09-01 07:04:37'),
	(18, 1, 'schedule_day', '28', '27', 1, '2025-09-03 00:18:40'),
	(19, 4, 'schedule_day', '25', '24', 1, '2025-09-03 00:18:52'),
	(20, 3, 'schedule_day', '21', '24', 1, '2025-09-04 21:08:48'),
	(21, 4, 'schedule_day', '24', '28', 1, '2025-09-04 21:08:48'),
	(22, 1, 'schedule_day', '27', '29', 1, '2025-09-04 21:09:02'),
	(23, 1, 'schedule_day', '29', '30', 1, '2025-09-05 00:56:02'),
	(24, 3, 'schedule_day', '24', '27', 1, '2025-09-05 00:56:18'),
	(25, 4, 'schedule_day', '28', '30', 1, '2025-09-05 00:56:18');

CREATE TABLE IF NOT EXISTS `sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sections` (`id`, `name`, `created_at`, `updated_at`) VALUES
	(1, 'Wet Section', '2025-08-22 18:32:27', '2025-08-22 18:32:27'),
	(2, 'Dry Section', '2025-08-22 18:32:28', '2025-08-22 18:32:28'),
	(3, 'Semi-Dry', '2025-08-22 18:32:28', '2025-08-22 18:32:28');

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('Fr95FjXh7SkfM5n3ZEzlZq78z67AsU64Mq6WZ4NC', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiQzdyQk12c1JUMHM0SnM1RlVIV0s3aVZ0S1VCdVcyMTdzQUk2UG95cyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdXBlcmFkbWluL2JpbGxpbmdfbWFuYWdlbWVudCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YTowOnt9fQ==', 1757087474);

CREATE TABLE IF NOT EXISTS `sms_notification_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `message_template` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sms_notification_settings` (`id`, `name`, `message_template`, `enabled`, `created_at`, `updated_at`) VALUES
	(1, 'bill_statement_wet_section', 'Paisi tabi na ang bayadan mo para sa renta nagkakahalaga ning ₱{{rent_amount}}, asin para sa tubig - ₱{{water_amount}}, siring naman pati ang bayadan sa kuryente na - ₱{{electricity_amount}}. Total due: ₱{{total_due}} sa petsa ning {{due_date}}.', 1, '2025-08-28 09:33:07', '2025-09-04 21:08:37'),
	(2, 'bill_statement_dry_section', 'Your bill statement: Rent - ₱{{rent_amount}}, Water - ₱{{water_amount}}. Total due: ₱{{total_due}} sa petsa ning {{due_date}}. Thank you {{ vendor_name }} with {{ stall_number }} Stall/Table Number', 1, '2025-08-28 09:33:07', '2025-09-04 21:08:37'),
	(3, 'payment_reminder_template', 'Reminder: The following payments are due today: {{unpaid_items}}. Thank you.{{ electricity_amount }}', 1, '2025-08-28 09:33:07', '2025-09-04 21:08:37'),
	(4, 'overdue_alert_template', 'OVERDUE: Your payment for {{overdue_items}} is past due. Your new total with penalties is ₱{{new_total_due}}. Disconnection is on {{disconnection_date}}. Thank you {{vendor_name}} HAHA', 1, '2025-08-28 09:33:07', '2025-09-04 21:08:37');

CREATE TABLE IF NOT EXISTS `stalls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `section_id` bigint unsigned NOT NULL,
  `table_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `daily_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monthly_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_id` (`vendor_id`),
  UNIQUE KEY `vendor_id_2` (`vendor_id`),
  UNIQUE KEY `vendor_id_3` (`vendor_id`),
  KEY `stalls_section_id_foreign` (`section_id`),
  CONSTRAINT `stalls_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stalls_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `stalls` (`id`, `section_id`, `table_number`, `vendor_id`, `daily_rate`, `monthly_rate`, `created_at`, `updated_at`) VALUES
	(1, 1, 'MS-01', 2, 12.00, 1150.00, '2025-08-22 18:32:28', '2025-09-05 02:29:21'),
	(2, 1, 'MS-02', 5, 0.00, 1231.00, '2025-08-22 18:32:28', '2025-09-05 02:29:21'),
	(3, 1, 'MS-03', NULL, 0.00, 0.00, '2025-09-01 16:58:12', '2025-09-05 02:29:21'),
	(4, 1, 'MS-04', 91, 0.00, 0.00, '2025-09-01 16:59:56', '2025-09-05 02:29:21'),
	(5, 1, 'MS-05', 92, 0.00, 0.00, '2025-09-01 17:03:15', '2025-09-05 02:29:21'),
	(6, 2, 'L1', NULL, 12.00, 1100.00, '2025-08-22 18:32:28', '2025-09-05 07:51:12'),
	(7, 2, 'L2', NULL, 0.00, 0.00, '2025-08-22 18:32:28', '2025-09-05 07:51:12'),
	(8, 2, 'L3', NULL, 0.00, 0.00, '2025-08-22 18:32:28', '2025-09-05 07:51:12'),
	(9, 2, 'L4', NULL, 0.00, 0.00, '2025-08-22 18:32:28', '2025-09-05 07:51:12'),
	(10, 2, 'L5', NULL, 0.00, 0.00, '2025-08-22 18:32:28', '2025-09-05 07:51:12'),
	(11, 3, '21', NULL, 120.00, 200.00, '2025-08-22 18:32:28', '2025-09-02 23:35:13'),
	(12, 3, '22', NULL, 0.00, 0.00, '2025-08-22 18:32:28', '2025-09-02 23:35:13'),
	(13, 3, '23', NULL, 0.00, 0.00, '2025-08-22 18:32:28', '2025-09-02 23:35:13'),
	(14, 3, '24', NULL, 0.00, 0.00, '2025-08-22 18:32:28', '2025-09-02 23:35:13'),
	(15, 3, '25', NULL, 0.00, 0.00, '2025-08-24 14:59:41', '2025-09-02 23:35:13'),
	(68, 2, 'L6', 89, 19.00, 10584.00, '2025-09-01 05:01:10', '2025-09-05 07:51:12'),
	(77, 3, 'FVS-01', NULL, 0.00, 0.00, '2025-09-04 21:07:55', '2025-09-04 21:07:55'),
	(87, 3, 'FVS-01', NULL, 0.00, 0.00, '2025-09-05 00:58:46', '2025-09-05 00:58:46'),
	(88, 3, 'FVS-01', NULL, 0.00, 0.00, '2025-09-05 01:09:00', '2025-09-05 01:09:00'),
	(99, 1, 'MS-06', 93, 159.00, 2122.00, '2025-09-01 17:23:20', '2025-09-05 02:29:21');

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `contact_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `role_id`, `name`, `username`, `password`, `last_login`, `status`, `contact_number`, `application_date`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Andy Po', 'admin', '$2y$12$wOHFfO/jfT3m0d4ecpS1B.Cgy8qFascePHL1AN1hqO8snd5pKfxVS', NULL, 'active', '09174321238', '2025-01-15', '2025-08-22 18:32:28', '2025-08-22 18:32:28'),
	(2, 2, 'Mr. Suave', 'vendor', '$2y$12$8HKTqZJEemccN1BFtK1XJe.fl4eAdx3lALrOC7xxNvr6YVbpP/JUS', NULL, 'active', '09123456789', '2025-08-24', '2025-08-24 03:08:23', '2025-08-26 19:25:47'),
	(3, 2, 'Johnny Bravo', 'johnny.doe', '$2y$12$CRZgOE0SyI9oAxHCWNGR4u9/fzfO6Mqfb.9IpXwp4cQW4b2bRrepG', NULL, 'active', '09171234567', '2025-01-15', '2025-08-27 05:11:49', '2025-09-03 01:39:30'),
	(4, 4, 'Meter Reader', 'meter_reader', '$2y$12$MpZxr9CsPpV3eT14DIYUZOe2LlOTCcwvHU6o7zVhDeBaB23rD3FLy', NULL, 'active', '09123456780', '2025-01-15', '2025-08-29 03:01:46', '2025-08-31 16:09:17'),
	(5, 2, 'Splinter', 'vendorms2', '$2y$12$pA7vGzwfHwcEE.A/mMNn/elaSwZ/N0pzCvkJkbWy0ihH/j0RF33TO', NULL, 'active', '09450127891', '2025-04-05', '2025-08-29 00:47:17', '2025-08-29 02:50:42'),
	(85, 3, 'admin aide', 'admin_aide', '$2y$12$HJNSpwPkD1MZvrLVydM9XeTP2g5XFOFA8DxOHeR4TU3z5FXbF8mCm', NULL, 'active', NULL, NULL, '2025-08-31 16:45:11', '2025-08-31 16:45:11'),
	(89, 2, 'lyra', 'lyra', '$2y$12$PY/uPnri/ZnXCb4zktMtLek9U8qVpYCzA8NuAH02UsfPKymxxVNYa', NULL, 'active', '09411231414', '2025-08-31', '2025-09-01 05:01:09', '2025-09-01 05:01:09'),
	(91, 2, 'Beyonce', 'beyonce', '$2y$12$99k33k1PaKXbG.gGLLgAWOwmn8vRoiwN.IHglwPQX3xI10lSbEsMO', NULL, 'active', NULL, '2025-08-21', '2025-09-01 16:59:56', '2025-09-04 00:35:45'),
	(92, 2, 'Jefferz', 'Jeffer', '$2y$12$N/qan3gGq6.vM1Tiac/MQ.tSCODnWPv/PLP6XMCu/Ep7BRXShc16u', NULL, 'active', NULL, '2025-08-22', '2025-09-01 17:03:15', '2025-09-04 02:42:41'),
	(93, 2, 'Jean Antonette', 'jean', '$2y$12$4WclFtrtwtrngTIJJd4tjeER49E69UCx.cASulCmTvh6EMl7AUiFi', NULL, 'active', NULL, '2025-08-23', '2025-09-01 17:23:20', '2025-09-04 00:43:03'),
	(94, 2, 'Emmanuel', 'emman', '$2y$12$qjQxAtkBlLhrIncQvVErwe7UCvfUqgR/WNBspTV24sbKtnT7OEEF2', NULL, 'active', '09233135112', '2025-08-30', '2025-09-03 00:09:03', '2025-09-04 05:48:18');

CREATE TABLE IF NOT EXISTS `utility_readings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stall_id` bigint unsigned NOT NULL,
  `utility_type` enum('Electricity','Water') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reading_date` date NOT NULL,
  `current_reading` decimal(10,2) NOT NULL,
  `previous_reading` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `utility_readings_stall_id_foreign` (`stall_id`),
  CONSTRAINT `utility_readings_stall_id_foreign` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
