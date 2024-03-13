<?php

namespace FluentMailMigrations;

class Items
{
    /**
     * Migrate the table.
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . FLUENT_MAIL_DB_PREFIX . 'items';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE IF NOT EXISTS $table (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `note` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
            `master_pass_secured` tinyint(1) NOT NULL,
            `is_favourite` tinyint(1) NOT NULL,
            `in_trash` tinyint(1) NOT NULL,
            `deleted` tinyint(1) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `type_id` bigint unsigned NOT NULL,
            `organization_id` bigint unsigned NOT NULL,
            `folder_id` bigint unsigned DEFAULT NULL,
            `collection_id` bigint unsigned NOT NULL,
            `user_id` bigint unsigned NOT NULL,
            `login_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `items_type_id_foreign` (`type_id`),
            KEY `items_organization_id_foreign` (`organization_id`),
            KEY `items_collection_id_foreign` (`collection_id`),
            KEY `items_user_id_foreign` (`user_id`),
            KEY `items_folder_id_foreign` (`folder_id`),
            CONSTRAINT `items_collection_id_foreign` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`),
            CONSTRAINT `items_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE SET NULL,
            CONSTRAINT `items_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
            CONSTRAINT `items_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`),
            CONSTRAINT `items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
            ) $charsetCollate;
            ";

            dbDelta($sql);
        }
    }
}
