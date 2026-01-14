<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
?>
<main class="gus-geo-discover">
    <section>
        <h1>GEO Discover</h1>
        <p>Browse published GEO entities.</p>
    </section>
    <section>
        <?php if (empty($posts)) : ?>
            <p>No entities are currently published.</p>
        <?php else : ?>
            <ul>
                <?php foreach ($posts as $post) : ?>
                    <?php
                    $tiers = get_post_meta($post->ID, '_gus_tiers_enabled', true);
                    if (!is_array($tiers)) {
                        $tiers = array();
                    }
                    ?>
                    <li>
                        <strong><?php echo esc_html($post->post_title); ?></strong>
                        <div>
                            <?php foreach ($tiers as $tier) : ?>
                                <?php if (isset($tier_labels[$tier])) : ?>
                                    <a href="<?php echo esc_url(home_url('/' . $geo_base . '/' . $post->post_type . '/' . $post->post_name . '/' . $tier . '/')); ?>">
                                        <?php echo esc_html($tier_labels[$tier]); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>
<?php
get_footer();
