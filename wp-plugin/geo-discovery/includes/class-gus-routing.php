<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Routing {
    private $resolver;
    private $renderer;
    private $seo;

    public function __construct(Gus_Resolver $resolver, Gus_Renderer $renderer, Gus_Seo $seo) {
        $this->resolver = $resolver;
        $this->renderer = $renderer;
        $this->seo = $seo;
    }

    public function register_query_vars($vars) {
        $vars[] = 'gus_geo';
        $vars[] = 'gus_route';
        $vars[] = 'gus_post_type';
        $vars[] = 'gus_slug';
        $vars[] = 'gus_tier';

        return $vars;
    }

    public function register_routes() {
        $base = Gus_Utils::get_geo_base();
        $base_regex = preg_quote($base, '/');

        add_rewrite_rule(
            '^' . $base_regex . '/?$','index.php?gus_geo=1&gus_route=base',
            'top'
        );

        add_rewrite_rule(
            '^' . $base_regex . '/discover/?$','index.php?gus_geo=1&gus_route=discover',
            'top'
        );

        add_rewrite_rule(
            '^' . $base_regex . '/([^/]+)/([^/]+)/((?:broad|mid|ultra))/?$',
            'index.php?gus_geo=1&gus_route=entity&gus_post_type=$matches[1]&gus_slug=$matches[2]&gus_tier=$matches[3]',
            'top'
        );
    }

    public function handle_request() {
        if (!Gus_Utils::is_geo_request()) {
            return;
        }

        $route = get_query_var('gus_route');

        if ($route === 'base') {
            wp_safe_redirect(Gus_Utils::get_discover_url(), 301);
            exit;
        }

        if ($route === 'discover') {
            $this->seo->set_canonical_url(Gus_Utils::get_discover_url());
            $this->renderer->render_discover();
        }

        if ($route === 'entity') {
            $tier = get_query_var('gus_tier');
            $post_type = get_query_var('gus_post_type');
            $slug = get_query_var('gus_slug');

            if (!Gus_Utils::is_valid_tier($tier)) {
                $this->render_404();
            }

            $post = $this->resolver->resolve_entity($post_type, $slug);
            if (!$post) {
                $this->render_404();
            }

            if (!$this->resolver->passes_governance($post->ID)) {
                $this->render_404();
            }

            $geo_url = Gus_Utils::get_geo_url($post_type, $slug, $tier);
            $this->seo->set_canonical_url($geo_url);
            $this->renderer->render_geo_page($post, $tier);
        }
    }

    private function render_404() {
        global $wp_query;

        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        $template = get_404_template();

        if ($template) {
            include $template;
        } else {
            include get_query_template('404');
        }
        exit;
    }
}
