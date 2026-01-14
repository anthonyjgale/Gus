<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_SEO {
    private $canonical_url;

    public function set_canonical_url($url) {
        remove_action('wp_head', array($this, 'output_canonical'), 10);

        if (!$this->is_geo_route()) {
            $this->canonical_url = null;
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
        echo '<meta name="robots" content="noindex,follow" />' . "\n";
    }

    private function is_geo_route() {
        return !empty(get_query_var('gus_geo'));
    }
}
