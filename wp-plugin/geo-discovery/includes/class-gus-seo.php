<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Seo {
    private $canonical_url;

    public function filter_robots($robots) {
        if (!Gus_Utils::is_geo_request()) {
            return $robots;
        }

        $robots['noindex'] = true;
        $robots['follow'] = true;

        return $robots;
    }

    public function set_canonical_url($url) {
        $this->canonical_url = esc_url_raw($url);
        remove_action('wp_head', 'rel_canonical');
        add_action('wp_head', array($this, 'output_canonical'), 1);
    }

    public function output_canonical() {
        if (empty($this->canonical_url)) {
            return;
        }

        echo '<link rel="canonical" href="' . esc_url($this->canonical_url) . '" />' . "\n";
    }
}
