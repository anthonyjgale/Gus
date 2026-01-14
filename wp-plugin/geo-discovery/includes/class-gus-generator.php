<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Generator {
    public static function generate($post_id, $tier) {
        $post = get_post($post_id);
        if (!$post) {
            return array();
        }

        $timestamp = time();
        $blocks = Gus_Utils::build_placeholder_blocks($post, $tier);
        $permalink = get_permalink($post);
        $source_urls = array();
        if (!empty($permalink)) {
            $source_urls[] = $permalink;
        }

        $grounding = array(
            'mode' => 'placeholder',
            'generated_at' => $timestamp,
            'tier' => $tier,
            'block_sources' => array(),
            'notes' => 'No grounding available in placeholder mode.',
        );

        update_post_meta($post_id, '_gus_blocks_' . $tier, $blocks);
        update_post_meta($post_id, Gus_Utils::META_GROUNDING_PREFIX . $tier, $grounding);
        update_post_meta($post_id, Gus_Utils::META_SOURCE_URLS_PREFIX . $tier, $source_urls);
        update_post_meta($post_id, Gus_Utils::META_LAST_GENERATED_PREFIX . $tier, $timestamp);
        update_post_meta($post_id, Gus_Utils::META_GENERATION_VERSION, 'v1-placeholder');

        return array(
            'blocks' => $blocks,
            'grounding' => $grounding,
            'source_urls' => $source_urls,
        );
    }
}
