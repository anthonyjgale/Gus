<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Resolver {
    public function get_entity($post_type, $slug) {
        $post = get_page_by_path($slug, OBJECT, $post_type);
        if (!$post || $post->post_type !== $post_type) {
            return null;
        }

        return $post;
    }

    public function is_entity_enabled(WP_Post $post) {
        return (bool) get_post_meta($post->ID, '_gus_enabled', true);
    }

    public function is_entity_published(WP_Post $post) {
        $status = get_post_meta($post->ID, '_gus_status', true);
        return $status === 'published';
    }

    public function is_tier_enabled(WP_Post $post, $tier) {
        $tiers = get_post_meta($post->ID, '_gus_tiers_enabled', true);
        if (!is_array($tiers)) {
            $tiers = array();
        }

        return in_array($tier, $tiers, true);
    }
}
