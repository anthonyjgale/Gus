<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Plugin {
    private $routing;
    private $renderer;
    private $resolver;
    private $seo;
    private $admin;

    public function init() {
        $this->seo = new Gus_SEO();
        $this->resolver = new Gus_Resolver();
        $this->renderer = new Gus_Renderer($this->seo);
        $this->routing = new Gus_Routing($this->resolver, $this->renderer, $this->seo);

        $this->routing->init();
        add_filter('wp_robots', array($this->seo, 'filter_robots'));

        if (is_admin()) {
            $this->admin = new Gus_Admin($this->routing);
            $this->admin->init();
        }
    }

    public static function activate() {
        if (false === get_option('gus_geo_base')) {
            add_option('gus_geo_base', 'geo');
        }

        if (false === get_option('gus_public_geo_enabled')) {
            add_option('gus_public_geo_enabled', true);
        }

        if (false === get_option('gus_geo_enabled_post_types')) {
            add_option('gus_geo_enabled_post_types', array('post'));
        }

        Gus_Routing::register_rewrite_rules();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }
}
