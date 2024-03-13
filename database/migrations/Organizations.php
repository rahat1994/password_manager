<?php

namespace FluentMailMigrations;

class Organizations
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

        $table = $wpdb->prefix . FLUENT_MAIL_DB_PREFIX . 'organizations';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE IF NOT EXISTS $table (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `billing_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                `user_id` bi  gint unsigned NOT NULL,
                PRIMARY KEY (`id`),
                KEY `organizations_user_id_foreign` (`user_id`),
                CONSTRAINT `organizations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                ) $charsetCollate;";

            dbDelta($sql);
        }
    }
}
