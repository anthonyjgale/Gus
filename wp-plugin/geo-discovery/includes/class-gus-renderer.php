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
    }

    public function render_entity(WP_Post $post, $tier) {
        $canonical_url = home_url('/' . $this->get_geo_base() . '/' . $post->post_type . '/' . $post->post_name . '/' . $tier . '/');
        $this->seo->set_canonical_url($canonical_url);

        status_header(200);
        nocache_headers();

        $blocks = get_post_meta($post->ID, '_gus_blocks_' . $tier, true);
        if (!is_array($blocks)) {
            $blocks = array();
        }

        $tier_labels = Gus_Utils::get_tier_labels();
        $geo_base = $this->get_geo_base();

        include GUS_PLUGIN_DIR . 'templates/geo-page.php';
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
}
