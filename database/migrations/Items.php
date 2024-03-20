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

        // for foreign key constraints
        $usersTable = $wpdb->prefix . 'users';
        $organizationsTable = $wpdb->prefix . FLUENT_MAIL_DB_PREFIX . 'organizations';
        $collectionsTable = $wpdb->prefix . FLUENT_MAIL_DB_PREFIX . 'collections';
        $foldersTable = $wpdb->prefix . FLUENT_MAIL_DB_PREFIX . 'folders';


        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE IF NOT EXISTS $table (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `note` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `master_pass_secured` tinyint(1) NOT NULL,
            `folder_id` bigint unsigned DEFAULT NULL,
            `collection_id` bigint unsigned NOT NULL,
            `user_id` bigint unsigned NOT NULL,
            `login_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `is_favourite` tinyint(1) NOT NULL DEFAULT 0,
            `in_trash` tinyint(1) NOT NULL DEFAULT 0,
            `deleted` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `organization_id` bigint unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `items_organization_id_foreign` (`organization_id`),
            KEY `items_collection_id_foreign` (`collection_id`),
            KEY `items_user_id_foreign` (`user_id`),
            KEY `items_folder_id_foreign` (`folder_id`),
            CONSTRAINT `items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `$usersTable` (`ID`) ON DELETE CASCADE,
            CONSTRAINT `items_collection_id_foreign` FOREIGN KEY (`collection_id`) REFERENCES `$collectionsTable` (`id`),
            CONSTRAINT `items_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `$foldersTable` (`id`) ON DELETE SET NULL,
            CONSTRAINT `items_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `$organizationsTable` (`id`)
            ) $charsetCollate;";

            dbDelta($sql);
        }
    }
}
