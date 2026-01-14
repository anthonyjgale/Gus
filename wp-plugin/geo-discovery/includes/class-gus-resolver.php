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

    public function passes_governance(WP_Post $post): bool {
        return $this->is_entity_enabled($post) && $this->is_entity_published($post);
    }

    public function get_enabled_tiers(WP_Post $post): array {
        $tiers = get_post_meta($post->ID, '_gus_tiers_enabled', true);
        if (!is_array($tiers)) {
            return array();
        }

        $valid_tiers = array('broad', 'mid', 'ultra');
        $tiers = array_values(array_filter($tiers, static function ($tier) use ($valid_tiers) {
            return is_string($tier) && in_array($tier, $valid_tiers, true);
        }));

        return array_values(array_unique($tiers));
    }

    public function is_tier_enabled(WP_Post $post, $tier) {
        $tiers = $this->get_enabled_tiers($post);
        return in_array($tier, $tiers, true);
    }
}
