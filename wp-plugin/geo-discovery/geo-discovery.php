<?php
/**
 * Plugin Name: GEO Discovery Landing Page
 * Description: Adds a public GEO landing page for MBA discovery routes.
 * Version: 1.0.0
 * Author: GEO Discovery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register rewrite tags and rules for GEO discovery routes.
 */
function geo_discovery_register_rewrite_rules() {
    add_rewrite_tag('%geo_discovery%', '([^&]+)');
    add_rewrite_rule(
        '^geo/programmes/mba/broad/?$',
        'index.php?geo_discovery=mba_broad',
        'top'
    );
}
add_action('init', 'geo_discovery_register_rewrite_rules');

/**
 * Flush rewrite rules on activation to ensure the route works immediately.
 */
function geo_discovery_activate() {
    geo_discovery_register_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'geo_discovery_activate');

/**
 * Flush rewrite rules on deactivation to clean up routes.
 */
function geo_discovery_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'geo_discovery_deactivate');

/**
 * Register the GEO Discovery settings page.
 */
function geo_discovery_register_settings_page() {
    add_options_page(
        'GEO Discovery',
        'GEO Discovery',
        'manage_options',
        'geo-discovery',
        'geo_discovery_render_settings_page'
    );
}
add_action('admin_menu', 'geo_discovery_register_settings_page');

/**
 * Register the GEO Discovery settings.
 */
function geo_discovery_register_settings() {
    register_setting(
        'geo_discovery_settings',
        'geo_discovery_mba_url',
        array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://example.com/mba'
        )
    );

    add_settings_section(
        'geo_discovery_main',
        'MBA Landing Page',
        '__return_null',
        'geo-discovery'
    );

    add_settings_field(
        'geo_discovery_mba_url',
        'Canonical MBA URL',
        'geo_discovery_render_mba_url_field',
        'geo-discovery',
        'geo_discovery_main'
    );
}
add_action('admin_init', 'geo_discovery_register_settings');

/**
 * Output structured data for the GEO MBA landing page.
 */
function geo_discovery_output_structured_data() {
    if (get_query_var('geo_discovery') !== 'mba_broad') {
        return;
    }

    $canonical_url = esc_url(get_option('geo_discovery_mba_url', 'https://example.com/mba'));

    // Course schema for the MBA landing page.
    $course_schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => 'MBA',
        'provider' => array(
            '@type' => 'Organization',
            'name' => 'Berlin School of Business and Innovation',
        ),
        'description' => 'Explore a broad MBA programme designed to accelerate your leadership journey.',
        'url' => $canonical_url,
    );

    // FAQPage schema for the MBA landing page FAQs.
    $faq_schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array(
            array(
                '@type' => 'Question',
                'name' => 'Who is this MBA designed for?',
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => 'Professionals seeking to expand their strategic and leadership skills.',
                ),
            ),
            array(
                '@type' => 'Question',
                'name' => 'What career outcomes can I expect?',
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => 'Graduates pursue roles in consulting, tech, finance, and entrepreneurship.',
                ),
            ),
            array(
                '@type' => 'Question',
                'name' => 'How do I learn more about admissions?',
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => 'Visit the canonical MBA page for requirements and deadlines.',
                ),
            ),
        ),
    );

    $schema = array($course_schema, $faq_schema);

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}
add_action('wp_head', 'geo_discovery_output_structured_data');

/**
 * Render the settings field for the canonical MBA URL.
 */
function geo_discovery_render_mba_url_field() {
    $value = esc_url(get_option('geo_discovery_mba_url', 'https://example.com/mba'));
    echo '<input type="url" name="geo_discovery_mba_url" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://example.com/mba" />';
}

/**
 * Render the GEO Discovery settings page.
 */
function geo_discovery_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>GEO Discovery</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('geo_discovery_settings');
            do_settings_sections('geo-discovery');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Render the GEO landing page when the route is requested.
 */
function geo_discovery_render_landing_page() {
    if (get_query_var('geo_discovery') !== 'mba_broad') {
        return;
    }

    $canonical_url = esc_url(get_option('geo_discovery_mba_url', 'https://example.com/mba'));

    status_header(200);
    nocache_headers();

    get_header();
    ?>
    <main class="geoffy-geo-page">
        <section>
            <h1>Discover the MBA that fits your ambitions</h1>
            <p>Explore a broad MBA programme designed to accelerate your leadership journey.</p>
        </section>

        <section>
            <h2>Key facts</h2>
            <ul>
                <li>Full-time, immersive learning experience.</li>
                <li>Global cohort with diverse industry backgrounds.</li>
                <li>Career services and leadership development built in.</li>
                <li>Experiential projects with real business impact.</li>
            </ul>
        </section>

        <section>
            <h2>Why this matches your search</h2>
            <p>
                You searched for a broad MBA programme. This landing page highlights the
                flexible curriculum, global community, and leadership focus that align with
                a wide-ranging business education.
            </p>
        </section>

        <section>
            <h2>FAQs</h2>
            <dl>
                <dt>Who is this MBA designed for?</dt>
                <dd>Professionals seeking to expand their strategic and leadership skills.</dd>

                <dt>What career outcomes can I expect?</dt>
                <dd>Graduates pursue roles in consulting, tech, finance, and entrepreneurship.</dd>

                <dt>How do I learn more about admissions?</dt>
                <dd>Visit the canonical MBA page for requirements and deadlines.</dd>
            </dl>
        </section>

        <section>
            <h2>Next steps</h2>
            <p>
                <a href="<?php echo esc_url($canonical_url); ?>">View the MBA programme details</a>
            </p>
            <p>
                <a href="<?php echo esc_url($canonical_url); ?>">Request more information</a>
            </p>
        </section>
    </main>
    <?php
    get_footer();
    exit;
}
add_action('template_redirect', 'geo_discovery_render_landing_page');
