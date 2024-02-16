/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cut_offs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cut_offs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cut_off_id` bigint unsigned NOT NULL,
  `end_of_day_id` bigint unsigned NOT NULL,
  `pos_machine_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `beginning_or` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ending_or` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beginning_amount` double(15,4) NOT NULL,
  `ending_amount` double(15,4) NOT NULL,
  `total_transactions` int NOT NULL,
  `gross_sales` double(15,4) NOT NULL,
  `net_sales` double(15,4) NOT NULL,
  `vatable_sales` double(15,4) NOT NULL,
  `vat_exempt_sales` double(15,4) NOT NULL,
  `vat_amount` double(15,4) NOT NULL,
  `vat_expense` double(15,4) NOT NULL,
  `void_amount` double(15,4) NOT NULL,
  `total_cash_payments` double(15,4) NOT NULL,
  `total_card_payments` double(15,4) NOT NULL,
  `total_online_payments` double(15,4) NOT NULL,
  `total_ar_payments` double(15,4) NOT NULL,
  `total_mobile_payments` double(15,4) NOT NULL,
  `total_charge` double(15,4) NOT NULL,
  `senior_count` int NOT NULL,
  `senior_amount` double(15,4) NOT NULL,
  `pwd_count` int NOT NULL,
  `pwd_amount` double(15,4) NOT NULL,
  `others_count` int NOT NULL,
  `others_amount` double(15,4) NOT NULL,
  `others_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `total_payout` double(15,4) NOT NULL,
  `total_service_charge` double(15,4) NOT NULL,
  `total_discount_amount` double(15,4) NOT NULL,
  `total_ar_cash_redeemed_amount` double(15,4) NOT NULL,
  `total_ar_card_redeemed_amount` double(15,4) NOT NULL,
  `total_cost` double(15,4) NOT NULL,
  `total_sk` double(15,4) NOT NULL,
  `cashier_id` bigint unsigned NOT NULL,
  `cashier_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_id` bigint unsigned NOT NULL,
  `admin_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shift_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sent_to_server` tinyint(1) NOT NULL DEFAULT '0',
  `treg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cut_offs_cut_off_id_index` (`cut_off_id`),
  KEY `cut_offs_end_of_day_id_foreign` (`end_of_day_id`),
  KEY `cut_offs_pos_machine_id_foreign` (`pos_machine_id`),
  KEY `cut_offs_branch_id_foreign` (`branch_id`),
  KEY `cut_offs_cashier_id_foreign` (`cashier_id`),
  KEY `cut_offs_admin_id_foreign` (`admin_id`),
  CONSTRAINT `cut_offs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `isync`.`users` (`id`),
  CONSTRAINT `cut_offs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `isync`.`branches` (`id`),
  CONSTRAINT `cut_offs_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `isync`.`users` (`id`),
  CONSTRAINT `cut_offs_end_of_day_id_foreign` FOREIGN KEY (`end_of_day_id`) REFERENCES `end_of_days` (`end_of_day_id`),
  CONSTRAINT `cut_offs_pos_machine_id_foreign` FOREIGN KEY (`pos_machine_id`) REFERENCES `isync`.`pos_machines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `end_of_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `end_of_days` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `end_of_day_id` bigint unsigned NOT NULL,
  `pos_machine_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `beginning_or` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ending_or` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beginning_amount` double(15,4) NOT NULL,
  `ending_amount` double(15,4) NOT NULL,
  `total_transactions` int NOT NULL,
  `gross_sales` double(15,4) NOT NULL,
  `net_sales` double(15,4) NOT NULL,
  `vatable_sales` double(15,4) NOT NULL,
  `vat_exempt_sales` double(15,4) NOT NULL,
  `vat_amount` double(15,4) NOT NULL,
  `vat_expense` double(15,4) NOT NULL,
  `void_amount` double(15,4) NOT NULL,
  `total_cash_payments` double(15,4) NOT NULL,
  `total_card_payments` double(15,4) NOT NULL,
  `total_online_payments` double(15,4) NOT NULL,
  `total_ar_payments` double(15,4) NOT NULL,
  `total_mobile_payments` double(15,4) NOT NULL,
  `total_charge` double(15,4) NOT NULL,
  `senior_count` int NOT NULL,
  `senior_amount` double(15,4) NOT NULL,
  `pwd_count` int NOT NULL,
  `pwd_amount` double(15,4) NOT NULL,
  `others_count` int NOT NULL,
  `others_amount` double(15,4) NOT NULL,
  `others_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `total_payout` double(15,4) NOT NULL,
  `total_service_charge` double(15,4) NOT NULL,
  `total_discount_amount` double(15,4) NOT NULL,
  `total_ar_cash_redeemed_amount` double(15,4) NOT NULL,
  `total_ar_card_redeemed_amount` double(15,4) NOT NULL,
  `total_cost` double(15,4) NOT NULL,
  `total_sk` double(15,4) NOT NULL,
  `cashier_id` bigint unsigned NOT NULL,
  `cashier_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_id` bigint unsigned NOT NULL,
  `admin_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shift_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sent_to_server` tinyint(1) NOT NULL DEFAULT '0',
  `treg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `end_of_days_end_of_day_id_index` (`end_of_day_id`),
  KEY `end_of_days_pos_machine_id_foreign` (`pos_machine_id`),
  KEY `end_of_days_branch_id_foreign` (`branch_id`),
  KEY `end_of_days_cashier_id_foreign` (`cashier_id`),
  KEY `end_of_days_admin_id_foreign` (`admin_id`),
  CONSTRAINT `end_of_days_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `isync`.`users` (`id`),
  CONSTRAINT `end_of_days_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `isync`.`branches` (`id`),
  CONSTRAINT `end_of_days_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `isync`.`users` (`id`),
  CONSTRAINT `end_of_days_pos_machine_id_foreign` FOREIGN KEY (`pos_machine_id`) REFERENCES `isync`.`pos_machines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `pos_machine_id` bigint unsigned NOT NULL,
  `transaction_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abbreviation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost` double(15,4) NOT NULL,
  `qty` double(15,4) NOT NULL,
  `amount` double(15,4) NOT NULL,
  `original_amount` double(15,4) NOT NULL,
  `gross` double(15,4) NOT NULL,
  `total` double(15,4) NOT NULL,
  `total_cost` double(15,4) NOT NULL,
  `is_vatable` tinyint(1) NOT NULL DEFAULT '0',
  `vat_amount` double(15,4) NOT NULL,
  `vatable_sales` double(15,4) NOT NULL,
  `vat_exempt_sales` double(15,4) NOT NULL,
  `discount_amount` double(15,4) NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `department_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `category_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subcategory_id` bigint unsigned NOT NULL,
  `subcategory_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_id` bigint unsigned NOT NULL,
  `unit_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_void` tinyint(1) NOT NULL DEFAULT '0',
  `void_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `void_at` datetime DEFAULT NULL,
  `is_back_out` tinyint(1) NOT NULL DEFAULT '0',
  `is_back_out_id` bigint unsigned DEFAULT NULL,
  `back_out_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_amount_sold` double(15,4) NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `is_sent_to_server` tinyint(1) NOT NULL DEFAULT '0',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `is_cut_off` tinyint(1) NOT NULL DEFAULT '0',
  `cut_off_id` bigint unsigned DEFAULT NULL,
  `cut_off_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_order_id_index` (`order_id`),
  KEY `orders_pos_machine_id_foreign` (`pos_machine_id`),
  KEY `orders_transaction_id_foreign` (`transaction_id`),
  KEY `orders_product_id_foreign` (`product_id`),
  KEY `orders_department_id_foreign` (`department_id`),
  KEY `orders_category_id_foreign` (`category_id`),
  KEY `orders_subcategory_id_foreign` (`subcategory_id`),
  KEY `orders_is_back_out_id_foreign` (`is_back_out_id`),
  KEY `orders_branch_id_foreign` (`branch_id`),
  CONSTRAINT `orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `isync`.`branches` (`id`),
  CONSTRAINT `orders_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `isync`.`categories` (`id`),
  CONSTRAINT `orders_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `isync`.`departments` (`id`),
  CONSTRAINT `orders_is_back_out_id_foreign` FOREIGN KEY (`is_back_out_id`) REFERENCES `isync`.`users` (`id`),
  CONSTRAINT `orders_pos_machine_id_foreign` FOREIGN KEY (`pos_machine_id`) REFERENCES `isync`.`pos_machines` (`id`),
  CONSTRAINT `orders_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `isync`.`products` (`id`),
  CONSTRAINT `orders_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `isync`.`subcategories` (`id`),
  CONSTRAINT `orders_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint unsigned NOT NULL,
  `pos_machine_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `transaction_id` bigint unsigned NOT NULL,
  `payment_type_id` bigint unsigned NOT NULL,
  `payment_type_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double(15,4) NOT NULL,
  `other_informations` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_advance_payment` tinyint(1) NOT NULL DEFAULT '0',
  `is_cut_off` tinyint(1) NOT NULL DEFAULT '0',
  `cut_off_id` bigint unsigned DEFAULT NULL,
  `cut_off_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_pos_machine_id_foreign` (`pos_machine_id`),
  KEY `payments_branch_id_foreign` (`branch_id`),
  KEY `payments_transaction_id_foreign` (`transaction_id`),
  KEY `payments_payment_type_id_foreign` (`payment_type_id`),
  CONSTRAINT `payments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `isync`.`branches` (`id`),
  CONSTRAINT `payments_payment_type_id_foreign` FOREIGN KEY (`payment_type_id`) REFERENCES `isync`.`payment_types` (`id`),
  CONSTRAINT `payments_pos_machine_id_foreign` FOREIGN KEY (`pos_machine_id`) REFERENCES `isync`.`pos_machines` (`id`),
  CONSTRAINT `payments_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `safekeeping_denominations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `safekeeping_denominations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint unsigned NOT NULL,
  `pos_machine_id` bigint unsigned NOT NULL,
  `safekeeping_denomination_id` bigint unsigned NOT NULL,
  `safekeeping_id` bigint unsigned NOT NULL,
  `cash_denomination_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double(15,4) NOT NULL,
  `qty` int NOT NULL,
  `total` double(15,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `safekeeping_denominations_branch_id_foreign` (`branch_id`),
  KEY `safekeeping_denominations_pos_machine_id_foreign` (`pos_machine_id`),
  KEY `safekeeping_denominations_safekeeping_id_foreign` (`safekeeping_id`),
  KEY `safekeeping_denominations_cash_denomination_id_foreign` (`cash_denomination_id`),
  CONSTRAINT `safekeeping_denominations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `isync`.`branches` (`id`),
  CONSTRAINT `safekeeping_denominations_cash_denomination_id_foreign` FOREIGN KEY (`cash_denomination_id`) REFERENCES `isync`.`cash_denominations` (`id`),
  CONSTRAINT `safekeeping_denominations_pos_machine_id_foreign` FOREIGN KEY (`pos_machine_id`) REFERENCES `isync`.`pos_machines` (`id`),
  CONSTRAINT `safekeeping_denominations_safekeeping_id_foreign` FOREIGN KEY (`safekeeping_id`) REFERENCES `safekeepings` (`safekeeping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `safekeepings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `safekeepings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `safekeeping_id` bigint unsigned NOT NULL,
  `pos_machine_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `amount` double(15,4) NOT NULL,
  `cashier_id` bigint unsigned NOT NULL,
  `cashier_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorize_id` bigint unsigned NOT NULL,
  `authorize_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_cut_off` tinyint(1) NOT NULL DEFAULT '0',
  `cut_off_id` bigint unsigned DEFAULT NULL,
  `cut_off_at` datetime DEFAULT NULL,
  `is_sent_to_server` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `safekeepings_safekeeping_id_index` (`safekeeping_id`),
  KEY `safekeepings_pos_machine_id_foreign` (`pos_machine_id`),
  KEY `safekeepings_branch_id_foreign` (`branch_id`),
  KEY `safekeepings_cashier_id_foreign` (`cashier_id`),
  KEY `safekeepings_authorize_id_foreign` (`authorize_id`),
  CONSTRAINT `safekeepings_authorize_id_foreign` FOREIGN KEY (`authorize_id`) REFERENCES `isync`.`users` (`id`),
  CONSTRAINT `safekeepings_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `isync`.`branches` (`id`),
  CONSTRAINT `safekeepings_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `isync`.`users` (`id`),
  CONSTRAINT `safekeepings_pos_machine_id_foreign` FOREIGN KEY (`pos_machine_id`) REFERENCES `isync`.`pos_machines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `pos_machine_id` bigint unsigned NOT NULL,
  `control_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gross_sales` double(15,4) NOT NULL,
  `net_sales` double(15,4) NOT NULL,
  `vatable_sales` double(15,4) NOT NULL,
  `vat_excempt_sales` double(15,4) NOT NULL,
  `vat_amount` double(15,4) NOT NULL,
  `discount_amount` double(15,4) NOT NULL,
  `tender_amount` double(15,4) NOT NULL,
  `change` double(15,4) NOT NULL,
  `service_charge` double(15,4) NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cashier_id` bigint unsigned NOT NULL,
  `cashier_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_order_id` bigint unsigned DEFAULT NULL,
  `take_order_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_unit_cost` double(15,4) NOT NULL,
  `total_void_amount` double(15,4) NOT NULL,
  `shift_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_void` tinyint(1) NOT NULL DEFAULT '0',
  `void_by_id` bigint unsigned DEFAULT NULL,
  `void_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `void_at` datetime DEFAULT NULL,
  `is_back_out` tinyint(1) NOT NULL DEFAULT '0',
  `is_back_out_id` bigint unsigned DEFAULT NULL,
  `back_out_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `charge_account_id` bigint unsigned DEFAULT NULL,
  `charge_account_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_account_receivable` tinyint(1) NOT NULL DEFAULT '0',
  `is_sent_to_server` tinyint(1) NOT NULL DEFAULT '0',
  `is_complete` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  `is_cut_off` tinyint(1) NOT NULL DEFAULT '0',
  `cut_off_id` bigint unsigned DEFAULT NULL,
  `cut_off_at` datetime DEFAULT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `guest_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_resume_printed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_transaction_id_index` (`transaction_id`),
  KEY `transactions_pos_machine_id_foreign` (`pos_machine_id`),
  KEY `transactions_cashier_id_foreign` (`cashier_id`),
  KEY `transactions_take_order_id_foreign` (`take_order_id`),
  KEY `transactions_void_by_id_foreign` (`void_by_id`),
  KEY `transactions_is_back_out_id_foreign` (`is_back_out_id`),
  KEY `transactions_charge_account_id_foreign` (`charge_account_id`),
  KEY `transactions_branch_id_foreign` (`branch_id`),
  CONSTRAINT `transactions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `isync`.`branches` (`id`),
  CONSTRAINT `transactions_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `isync`.`users` (`id`),
  CONSTRAINT `transactions_charge_account_id_foreign` FOREIGN KEY (`charge_account_id`) REFERENCES `isync`.`charge_accounts` (`id`),
  CONSTRAINT `transactions_is_back_out_id_foreign` FOREIGN KEY (`is_back_out_id`) REFERENCES `isync`.`users` (`id`),
  CONSTRAINT `transactions_pos_machine_id_foreign` FOREIGN KEY (`pos_machine_id`) REFERENCES `isync`.`pos_machines` (`id`),
  CONSTRAINT `transactions_take_order_id_foreign` FOREIGN KEY (`take_order_id`) REFERENCES `isync`.`pos_machines` (`id`),
  CONSTRAINT `transactions_void_by_id_foreign` FOREIGN KEY (`void_by_id`) REFERENCES `isync`.`users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2024_01_17_120622_create_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2024_01_17_135442_create_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2024_01_22_071743_create_payments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2024_01_22_091545_create_safekeepings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2024_01_22_094221_create_safekeeping_denominations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_01_23_121052_create_end_of_days_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2024_01_23_121106_create_cut_offs_table',1);
