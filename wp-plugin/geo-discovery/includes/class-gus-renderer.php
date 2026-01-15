<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Renderer {
    private $seo;

    public function __construct(Gus_SEO $seo) {
        $this->seo = $seo;
    }

    public function get_discover_entities() {
        $query = new WP_Query(
            array(
                'post_type' => $this->get_enabled_post_types(),
                'post_status' => 'publish',
                'posts_per_page' => 200,
                'no_found_rows' => true,
                'fields' => 'ids',
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'meta_query' => array(
                    array(
                        'key' => '_gus_enabled',
                        'value' => '1',
                    ),
                    array(
                        'key' => '_gus_status',
                        'value' => 'published',
                    ),
                ),
            )
        );

        return $query->posts;
    }

    public function render_discover() {
        $post_ids = $this->get_discover_entities();
        $posts = array();

        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if ($post) {
                $posts[] = $post;
            }
        }

        $canonical_url = home_url('/' . $this->get_geo_base() . '/discover/');
        $this->seo->set_canonical_url($canonical_url);

        status_header(200);
        nocache_headers();

        $tier_labels = Gus_Utils::get_tier_labels();
        $geo_base = $this->get_geo_base();

        include GUS_PLUGIN_DIR . 'templates/discover.php';
        exit;
    }

    public function render_entity(WP_Post $post, $tier) {
        $canonical_url = home_url('/' . $this->get_geo_base() . '/' . $post->post_type . '/' . $post->post_name . '/' . $tier . '/');
        $this->seo->set_canonical_url($canonical_url);

        status_header(200);
        nocache_headers();

        $blocks = get_post_meta($post->ID, '_gus_blocks_' . $tier, true);
        $blocks = is_array($blocks) ? $blocks : array();
        $is_legacy = $this->is_legacy_blocks($blocks);
        $needs_repair = $is_legacy || empty($blocks);
        if (!$needs_repair) {
            $validation = Gus_Block_Schema::validate_blocks($blocks, $tier);
            $needs_repair = !$validation['ok'];
        }

        if ($needs_repair) {
            Gus_Generator::generate($post->ID, $tier);
            $blocks = get_post_meta($post->ID, '_gus_blocks_' . $tier, true);
            $blocks = is_array($blocks) ? $blocks : array();
        }

        $validation = Gus_Block_Schema::validate_blocks($blocks, $tier);
        if (!$validation['ok']) {
            $blocks = Gus_Block_Schema::build_placeholder_blocks($post, $tier);
            $timestamp = time();
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
                'notes' => 'Renderer fallback applied after validation failure.',
                'schema_version' => Gus_Block_Schema::get_schema_version(),
                'validation_errors' => $validation['errors'],
            );
            update_post_meta($post->ID, '_gus_blocks_' . $tier, $blocks);
            update_post_meta($post->ID, Gus_Utils::META_GROUNDING_PREFIX . $tier, $grounding);
            update_post_meta($post->ID, Gus_Utils::META_SOURCE_URLS_PREFIX . $tier, $source_urls);
            update_post_meta($post->ID, Gus_Utils::META_LAST_GENERATED_PREFIX . $tier, $timestamp);
            update_post_meta($post->ID, Gus_Utils::META_GENERATION_VERSION, 'v1-renderer-placeholder');
        }

        $blocks_by_type = array();
        foreach ($blocks as $block) {
            if (is_array($block) && isset($block['type'])) {
                $blocks_by_type[$block['type']] = $block;
            }
        }

        $tier_labels = Gus_Utils::get_tier_labels();
        $geo_base = $this->get_geo_base();

        include GUS_PLUGIN_DIR . 'templates/geo-page.php';
        exit;
    }

    private function get_geo_base() {
        $base = trim((string) get_option('gus_geo_base', 'geo'), '/');
        return $base === '' ? 'geo' : $base;
    }

    private function get_enabled_post_types() {
        $post_types = get_option('gus_geo_enabled_post_types', array('post'));
        if (!is_array($post_types)) {
            return array('post');
        }

        return array_values(array_filter($post_types, static function ($post_type) {
            return is_string($post_type) && $post_type !== '';
        }));
    }

    private function is_legacy_blocks(array $blocks) {
        if (empty($blocks)) {
            return false;
        }

        foreach ($blocks as $block) {
            if (!is_string($block)) {
                return false;
            }
        }

        return true;
    }
}
