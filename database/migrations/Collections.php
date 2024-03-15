<?php

namespace FluentMailMigrations;

class Collections
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

        $table = $wpdb->prefix . FLUENT_MAIL_DB_PREFIX . 'collections';

        // for foreign key constraints
        $usersTable = $wpdb->prefix . 'users';
        $organizationsTable = $wpdb->prefix . FLUENT_MAIL_DB_PREFIX . 'organizations';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE IF NOT EXISTS $table (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `organization_id` bigint unsigned NOT NULL,
                `user_id` bigint unsigned NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `collections_organization_id_foreign` (`organization_id`),
                KEY `collections_user_id_foreign` (`user_id`),
                CONSTRAINT `collections_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `$organizationsTable` (`id`) ON DELETE CASCADE,
                CONSTRAINT `collections_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `$usersTable` (`ID`) ON DELETE CASCADE
              ) $charsetCollate;";

            dbDelta($sql);
        }
    }
}
