<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Utils {
    const META_GROUNDING_PREFIX = '_gus_grounding_';
    const META_LAST_GENERATED_PREFIX = '_gus_last_generated_';
    const META_GENERATION_VERSION = '_gus_generation_version';
    const META_SOURCE_URLS_PREFIX = '_gus_source_urls_';

    public static function build_placeholder_blocks(WP_Post $post, $tier) {
        $tier_label = ucfirst($tier);
        return array(
            sprintf('%s GEO block for %s', $tier_label, $post->post_title),
            sprintf('Highlight the %s tier value proposition for %s.', $tier, $post->post_title),
            sprintf('Add CTA content that aligns with %s tier intent.', $tier),
        );
    }

    public static function get_tier_labels() {
        return array(
            'broad' => 'Broad',
            'mid' => 'Mid',
            'ultra' => 'Ultra',
        );
    }
}
