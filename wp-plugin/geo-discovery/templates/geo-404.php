<?php

if (!defined('ABSPATH')) {
    exit;
}

$geo_base = trim((string) get_option('gus_geo_base', 'geo'), '/');
$geo_base = $geo_base === '' ? 'geo' : $geo_base;
$discover_url = home_url('/' . $geo_base . '/discover/');

get_header();
?>
<main id="gus-geo-404" class="gus-geo-404">
    <h1><?php echo esc_html__('Page not found', 'geo-discovery'); ?></h1>
    <p>
        <?php
        echo sprintf(
            esc_html__('GEO page not available. Visit %s.', 'geo-discovery'),
            '<a href="' . esc_url($discover_url) . '">' . esc_html($discover_url) . '</a>'
        );
        ?>
    </p>
</main>
<?php
get_footer();
