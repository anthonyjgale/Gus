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
        $schema_version = Gus_Block_Schema::get_schema_version();
        $blocks = Gus_Block_Schema::build_placeholder_blocks($post, $tier);
        $validation = Gus_Block_Schema::validate_blocks($blocks, $tier);
        $validation_errors = $validation['ok'] ? array() : $validation['errors'];
        $fallback_validation_ok = null;

        if (!$validation['ok']) {
            $blocks = self::build_fallback_blocks($tier);
            $fallback_validation = Gus_Block_Schema::validate_blocks($blocks, $tier);
            $fallback_validation_ok = $fallback_validation['ok'];
            if (!$fallback_validation_ok) {
                $validation_errors = array_merge($validation_errors, $fallback_validation['errors']);
            }
        }
        $permalink = get_permalink($post);
        $source_urls = array();
        if (!empty($permalink)) {
            $source_urls[] = $permalink;
        }

        $grounding_notes = 'No grounding available in placeholder mode.';
        if (!$validation['ok']) {
            $grounding_notes = 'Validation failed. Fallback blocks applied.';
            if ($fallback_validation_ok === false) {
                $grounding_notes = 'Validation failed. Fallback blocks applied but still invalid.';
            }
        }

        $grounding = array(
            'mode' => 'placeholder',
            'generated_at' => $timestamp,
            'tier' => $tier,
            'block_sources' => array(),
            'notes' => $grounding_notes,
            'schema_version' => $schema_version,
            'validation_errors' => $validation_errors,
        );

        update_post_meta($post_id, '_gus_blocks_' . $tier, $blocks);
        update_post_meta($post_id, Gus_Utils::META_GROUNDING_PREFIX . $tier, $grounding);
        update_post_meta($post_id, Gus_Utils::META_SOURCE_URLS_PREFIX . $tier, $source_urls);
        update_post_meta($post_id, Gus_Utils::META_LAST_GENERATED_PREFIX . $tier, $timestamp);
        update_post_meta($post_id, Gus_Utils::META_GENERATION_VERSION, Gus_Utils::GENERATION_VERSION_GENERATOR_PLACEHOLDER);

        return array(
            'blocks' => $blocks,
            'grounding' => $grounding,
            'source_urls' => $source_urls,
        );
    }

    private static function build_fallback_blocks($tier) {
        $version = Gus_Block_Schema::get_schema_version();
        $count = Gus_Block_Schema::get_tier_count($tier);

        return array(
            array(
                'type' => 'hero',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'title' => 'Explore this GEO page',
                    'dek' => 'A quick overview is temporarily unavailable.',
                ),
            ),
            array(
                'type' => 'value',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'bullets' => array_fill(0, $count, 'Key value coming soon.'),
                ),
            ),
            array(
                'type' => 'highlights',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'items' => array_fill(0, $count, array('label' => 'Highlight', 'value' => 'Details on the way.')),
                ),
            ),
            array(
                'type' => 'how_it_works',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'steps' => array_fill(0, $count, array('title' => 'Step', 'body' => 'More guidance soon.')),
                ),
            ),
            array(
                'type' => 'faq',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'items' => array_fill(0, $count, array('q' => 'Question', 'a' => 'Answer forthcoming.')),
                ),
            ),
            array(
                'type' => 'cta',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'primary_text' => 'Return to GEO discovery',
                    'secondary_text' => 'Browse more locations',
                ),
            ),
        );
    }
}
