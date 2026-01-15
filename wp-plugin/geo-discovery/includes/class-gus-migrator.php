<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Migrator {
    const MIGRATION_OPTION = 'gus_geo_migrated_generation_versions';

    public static function migrate_generation_versions() {
        if (get_option(self::MIGRATION_OPTION)) {
            return;
        }

        $post_types = get_option('gus_geo_enabled_post_types', array('post'));
        if (!is_array($post_types)) {
            $post_types = array('post');
        }

        $post_types = array_values(array_filter($post_types, static function ($post_type) {
            return is_string($post_type) && $post_type !== '' && $post_type !== 'attachment';
        }));

        if (empty($post_types)) {
            update_option(self::MIGRATION_OPTION, 1);
            return;
        }

        $legacy_map = array(
            'v1-placeholder-blocks' => Gus_Utils::GENERATION_VERSION_GENERATOR_PLACEHOLDER,
        );

        $query = new WP_Query(
            array(
                'post_type' => $post_types,
                'post_status' => 'any',
                'posts_per_page' => -1,
                'no_found_rows' => true,
                'fields' => 'ids',
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'meta_query' => array(
                    array(
                        'key' => Gus_Utils::META_GENERATION_VERSION,
                        'value' => array_keys($legacy_map),
                        'compare' => 'IN',
                    ),
                ),
            )
        );

        if (!empty($query->posts)) {
            foreach ($query->posts as $post_id) {
                $current_value = get_post_meta($post_id, Gus_Utils::META_GENERATION_VERSION, true);
                if (isset($legacy_map[$current_value]) && $legacy_map[$current_value] !== $current_value) {
                    update_post_meta($post_id, Gus_Utils::META_GENERATION_VERSION, $legacy_map[$current_value]);
                }
            }
        }

        update_option(self::MIGRATION_OPTION, 1);
    }
}
