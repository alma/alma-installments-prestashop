-- File : install.sql
-- Creation of Alma module database table

CREATE TABLE IF NOT EXISTS `{_DB_PREFIX_}alma` (
    `id_alma` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart` INT UNSIGNED NOT NULL,
    `id_order` INT UNSIGNED DEFAULT NULL,
    `alma_payment_id` VARCHAR(64) NOT NULL,
    `is_bnpl_eligible` TINYINT(1) NOT NULL DEFAULT 0,
    `plan_key` VARCHAR(32) NOT NULL,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_alma`),
    UNIQUE KEY `uniq_alma_payment_id` (`alma_payment_id`),
    KEY `idx_id_cart` (`id_cart`),
    KEY `idx_id_order` (`id_order`)
) ENGINE={_MYSQL_ENGINE_} DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
