<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Plugin {
    private $routing;
    private $resolver;
    private $renderer;
    private $seo;

    public function __construct() {
        $this->resolver = new Gus_Resolver();
        $this->renderer = new Gus_Renderer();
        $this->seo = new Gus_Seo();
        $this->routing = new Gus_Routing($this->resolver, $this->renderer, $this->seo);
    }

    public function init() {
        add_action('init', array($this->routing, 'register_routes'));
        add_filter('query_vars', array($this->routing, 'register_query_vars'));
        add_action('template_redirect', array($this->routing, 'handle_request'));
        add_filter('wp_robots', array($this->seo, 'filter_robots'));
    }

    public static function activate() {
        if (get_option(Gus_Utils::OPTION_BASE, null) === null) {
            add_option(Gus_Utils::OPTION_BASE, 'geo');
        }

        if (get_option(Gus_Utils::OPTION_ENABLED_POST_TYPES, null) === null) {
            add_option(Gus_Utils::OPTION_ENABLED_POST_TYPES, Gus_Utils::get_public_post_types());
        }

        $routing = new Gus_Routing(new Gus_Resolver(), new Gus_Renderer(), new Gus_Seo());
        $routing->register_routes();
        flush_rewrite_rules();
    }
}
