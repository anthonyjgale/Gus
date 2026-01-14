<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Routing {
    private $resolver;
    private $renderer;
    private $seo;

    public function __construct(Gus_Resolver $resolver, Gus_Renderer $renderer, Gus_SEO $seo) {
        $this->resolver = $resolver;
        $this->renderer = $renderer;
        $this->seo = $seo;
    }

    public function init() {
        add_action('init', array($this, 'register_routes'));
        add_filter('query_vars', array($this, 'register_query_vars'));
        add_action('template_redirect', array($this, 'handle_request'));
    }

    public static function register_rewrite_rules() {
        $base = trim((string) get_option('gus_geo_base', 'geo'), '/');
        $base = $base === '' ? 'geo' : $base;

        add_rewrite_tag('%gus_geo%', '([^&]+)');
        add_rewrite_tag('%gus_geo_type%', '([^&]+)');
        add_rewrite_tag('%gus_geo_slug%', '([^&]+)');
        add_rewrite_tag('%gus_geo_tier%', '([^&]+)');

        add_rewrite_rule(
            '^' . $base . '/discover/?$',
            'index.php?gus_geo=discover',
            'top'
        );

        add_rewrite_rule(
            '^' . $base . '/([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?gus_geo=entity&gus_geo_type=$matches[1]&gus_geo_slug=$matches[2]&gus_geo_tier=$matches[3]',
            'top'
        );
    }

    public function register_routes() {
        self::register_rewrite_rules();
    }

    public function register_query_vars($vars) {
        $vars[] = 'gus_geo';
        $vars[] = 'gus_geo_type';
        $vars[] = 'gus_geo_slug';
        $vars[] = 'gus_geo_tier';
        return $vars;
    }

    public function handle_request() {
        $route = get_query_var('gus_geo');
        if (empty($route)) {
            return;
        }

        if (!$this->is_public_geo_enabled()) {
            $this->render_404();
            return;
        }

        if ($route === 'discover') {
            $this->renderer->render_discover();
            exit;
        }

        if ($route === 'entity') {
            $this->handle_entity_route();
            exit;
        }

        $this->render_404();
    }

    private function handle_entity_route() {
        $post_type = sanitize_key(get_query_var('gus_geo_type'));
        $slug = sanitize_title(get_query_var('gus_geo_slug'));
        $tier = sanitize_key(get_query_var('gus_geo_tier'));

        if (empty($post_type) || empty($slug) || empty($tier)) {
            $this->render_404();
            return;
        }

        $enabled_post_types = $this->get_enabled_post_types();
        if (!in_array($post_type, $enabled_post_types, true)) {
            $this->render_404();
            return;
        }

        $entity = $this->resolver->get_entity($post_type, $slug);
        if (!$entity) {
            $this->render_404();
            return;
        }

        if (!$this->resolver->is_entity_enabled($entity)) {
            $this->render_404();
            return;
        }

        if (!$this->resolver->is_entity_published($entity)) {
            $this->render_404();
            return;
        }

        if (!$this->resolver->is_tier_enabled($entity, $tier)) {
            $this->render_404();
            return;
        }

        $this->renderer->render_entity($entity, $tier);
    }

    private function render_404() {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        include get_404_template();
        exit;
    }

    public function get_geo_base() {
        $base = trim((string) get_option('gus_geo_base', 'geo'), '/');
        return $base === '' ? 'geo' : $base;
    }

    public function get_enabled_post_types() {
        $post_types = get_option('gus_geo_enabled_post_types', array('post'));
        if (!is_array($post_types)) {
            $post_types = array('post');
        }

        return array_values(array_filter($post_types, static function ($post_type) {
            return is_string($post_type) && $post_type !== '';
        }));
    }

    private function is_public_geo_enabled() {
        return (bool) get_option('gus_public_geo_enabled', true);
    }
}
