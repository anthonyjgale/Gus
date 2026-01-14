<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Renderer {
    public function render_discover() {
        $entities = $this->get_discover_entities();
        $template = GUS_GEO_PATH . 'templates/discover.php';

        if (!file_exists($template)) {
            return;
        }

        $context = array(
            'entities' => $entities,
        );

        require $template;
        exit;
    }

    public function render_geo_page($post, $tier) {
        $resolver = new Gus_Resolver();
        $blocks = $resolver->get_blocks($post, $tier);
        $template = GUS_GEO_PATH . 'templates/geo-page.php';

        if (!file_exists($template)) {
            return;
        }

        $context = array(
            'post' => $post,
            'tier' => $tier,
            'blocks' => $blocks,
            'entity_url' => get_permalink($post),
        );

        require $template;
        exit;
    }

    private function get_discover_entities() {
        $post_types = Gus_Utils::get_enabled_post_types();

        if (empty($post_types)) {
            return array();
        }

        $query = new WP_Query(
            array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => Gus_Utils::META_ENABLED,
                        'value' => '1',
                        'compare' => '=',
                    ),
                    array(
                        'key' => Gus_Utils::META_STATUS,
                        'value' => Gus_Utils::STATUS_PUBLISHED,
                        'compare' => '=',
                    ),
                ),
                'orderby' => 'title',
                'order' => 'ASC',
            )
        );

        return $query->posts;
    }
}
