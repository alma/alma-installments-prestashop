-- File : install.sql
-- Creation of Alma module database table

CREATE TABLE IF NOT EXISTS `{_DB_PREFIX_}alma` (
    `id_cart` INT UNSIGNED NOT NULL,
    `orders` TEXT DEFAULT NULL,
    `alma_payment_id` VARCHAR(64) DEFAULT NULL,
    `is_bnpl_eligible` TINYINT(1) DEFAULT NULL,
    `plan_key` VARCHAR(32) DEFAULT NULL,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_cart`),
    UNIQUE KEY `uniq_alma_payment_id` (`alma_payment_id`)
) ENGINE={_MYSQL_ENGINE_} DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
