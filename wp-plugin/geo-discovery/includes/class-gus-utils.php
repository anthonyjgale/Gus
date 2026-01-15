<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Utils {
    const META_GROUNDING_PREFIX = '_gus_grounding_';
    const META_LAST_GENERATED_PREFIX = '_gus_last_generated_';
    const META_GENERATION_VERSION = '_gus_generation_version';
    const META_SOURCE_URLS_PREFIX = '_gus_source_urls_';
    const GENERATION_VERSION_GENERATOR_PLACEHOLDER = 'v1-generator-placeholder';
    const GENERATION_VERSION_RENDERER_PLACEHOLDER = 'v1-renderer-placeholder';

    /**
     * @deprecated 1.2.0 Use Gus_Block_Schema::build_placeholder_blocks() instead.
     */
    public static function build_placeholder_blocks(WP_Post $post, $tier) {
        return Gus_Block_Schema::build_placeholder_blocks($post, $tier);
    }

    public static function get_tier_labels() {
        return array(
            'broad' => 'Broad',
            'mid' => 'Mid',
            'ultra' => 'Ultra',
        );
    }
}
