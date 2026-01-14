<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_SEO {
    private $canonical_url;

    public function filter_robots($robots) {
        if (!$this->is_geo_route()) {
            return $robots;
        }

        $robots['noindex'] = true;
        $robots['follow'] = true;

        return $robots;
    }

    public function set_canonical_url($url) {
        if (!$this->is_geo_route()) {
            return;
        }

        $this->canonical_url = $url;
        remove_action('wp_head', 'rel_canonical');
        add_action('wp_head', array($this, 'output_canonical'), 10);
    }

    public function output_canonical() {
        if (!$this->canonical_url) {
            return;
        }

        echo '<link rel="canonical" href="' . esc_url($this->canonical_url) . '" />' . "\n";
    }

    private function is_geo_route() {
        return !empty(get_query_var('gus_geo'));
    }
}
