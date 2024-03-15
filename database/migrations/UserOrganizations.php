<?php

namespace FluentMailMigrations;

class UserOrganizations
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

        $table = $wpdb->prefix . FLUENT_MAIL_DB_PREFIX . 'user_organizations';

        $usersTable = $wpdb->prefix . 'users';
        $organizationsTable = $wpdb->prefix . FLUENT_MAIL_DB_PREFIX . 'organizations';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint unsigned NOT NULL,
                `organization_id` bigint unsigned NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `user_organizations_user_id_foreign` (`user_id`),
                KEY `user_organizations_organization_id_foreign` (`organization_id`),
                CONSTRAINT `user_organizations_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `$organizationsTable` (`id`),
                CONSTRAINT `user_organizations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `$usersTable` (`id`)
                ) $charsetCollate;";

            dbDelta($sql);
        }
    }
}
