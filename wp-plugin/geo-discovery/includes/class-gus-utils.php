<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Utils {
    const OPTION_BASE = 'gus_geo_base';
    const OPTION_ENABLED_POST_TYPES = 'gus_geo_enabled_post_types';

    const META_ENABLED = '_gus_enabled';
    const META_STATUS = '_gus_status';
    const META_TIERS_ENABLED = '_gus_tiers_enabled';
    const META_BLOCKS_PREFIX = '_gus_blocks_';

    const STATUS_PUBLISHED = 'published';

    public static function get_geo_base() {
        $base = get_option(self::OPTION_BASE, 'geo');
        $base = trim(sanitize_title($base));

        return $base !== '' ? $base : 'geo';
    }

    public static function get_enabled_post_types() {
        $enabled = get_option(self::OPTION_ENABLED_POST_TYPES, array());
        if (!is_array($enabled)) {
            $enabled = array();
        }

        return $enabled;
    }

    public static function get_public_post_types() {
        return get_post_types(
            array(
                'public' => true,
            ),
            'names'
        );
    }

    public static function is_geo_request() {
        $is_geo = get_query_var('gus_geo');
        $route = get_query_var('gus_route');

        return $is_geo && in_array($route, array('discover', 'entity', 'base'), true);
    }

    public static function get_discover_url() {
        return home_url(trailingslashit(self::get_geo_base()) . 'discover/');
    }

    public static function get_geo_url($post_type, $slug, $tier) {
        $base = trailingslashit(self::get_geo_base());
        $path = $base . trailingslashit($post_type) . trailingslashit($slug) . trailingslashit($tier);

        return home_url($path);
    }

    public static function add_utm_params($url, $post_type, $tier) {
        return add_query_arg(
            array(
                'utm_source' => 'gus',
                'utm_medium' => 'geo',
                'utm_campaign' => $post_type,
                'utm_content' => $tier,
            ),
            $url
        );
    }

    public static function is_valid_tier($tier) {
        return in_array($tier, array('broad', 'mid', 'ultra'), true);
    }

    public static function get_enabled_tiers($post_id) {
        $tiers = get_post_meta($post_id, self::META_TIERS_ENABLED, true);

        if (!is_array($tiers) || empty($tiers)) {
            return array('broad', 'mid', 'ultra');
        }

        return array_values(array_intersect($tiers, array('broad', 'mid', 'ultra')));
    }

    public static function get_blocks_meta_key($tier) {
        return self::META_BLOCKS_PREFIX . $tier;
    }

    public static function build_placeholder_blocks($post, $tier) {
        $post_type = $post->post_type;
        $canonical_url = get_permalink($post);
        $cta_url = self::add_utm_params($canonical_url, $post_type, $tier);

        return array(
            array(
                'type' => 'hero',
                'title' => sprintf('%s for %s', get_the_title($post), ucfirst($tier)),
                'summary' => sprintf('Discover %s insights tailored for this %s.', $tier, $post_type),
            ),
            array(
                'type' => 'key_facts',
                'items' => array(
                    array(
                        'label' => 'Tier',
                        'value' => ucfirst($tier),
                    ),
                    array(
                        'label' => 'Entity',
                        'value' => get_the_title($post),
                    ),
                ),
            ),
            array(
                'type' => 'faq',
                'items' => array(
                    array(
                        'q' => 'What is this page?',
                        'a' => 'A GEO discovery page with tailored highlights.',
                    ),
                    array(
                        'q' => 'Where can I learn more?',
                        'a' => 'Visit the canonical page for full details.',
                    ),
                ),
            ),
            array(
                'type' => 'cta',
                'label' => 'Next steps',
                'url' => $cta_url,
            ),
        );
    }
}
