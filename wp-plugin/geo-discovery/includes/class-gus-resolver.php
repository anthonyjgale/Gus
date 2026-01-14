<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Resolver {
    public function resolve_entity($post_type, $slug) {
        $post_type = sanitize_key($post_type);
        $slug = sanitize_title($slug);

        if (!$this->is_post_type_allowed($post_type)) {
            return null;
        }

        $posts = get_posts(
            array(
                'name' => $slug,
                'post_type' => $post_type,
                'post_status' => 'publish',
                'numberposts' => 1,
            )
        );

        if (empty($posts)) {
            return null;
        }

        return $posts[0];
    }

    public function passes_governance($post_id) {
        $enabled = get_post_meta($post_id, Gus_Utils::META_ENABLED, true);
        $status = get_post_meta($post_id, Gus_Utils::META_STATUS, true);

        return (string) $enabled === '1' && $status === Gus_Utils::STATUS_PUBLISHED;
    }

    public function get_blocks($post, $tier) {
        $meta_key = Gus_Utils::get_blocks_meta_key($tier);
        $blocks = get_post_meta($post->ID, $meta_key, true);

        if (is_string($blocks)) {
            $decoded = json_decode($blocks, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $blocks = $decoded;
            }
        }

        if (empty($blocks)) {
            $blocks = Gus_Utils::build_placeholder_blocks($post, $tier);
            update_post_meta($post->ID, $meta_key, $blocks);
        }

        return $blocks;
    }

    private function is_post_type_allowed($post_type) {
        $post_type_object = get_post_type_object($post_type);
        if (!$post_type_object || !$post_type_object->public) {
            return false;
        }

        $enabled = Gus_Utils::get_enabled_post_types();

        return in_array($post_type, $enabled, true);
    }
}
