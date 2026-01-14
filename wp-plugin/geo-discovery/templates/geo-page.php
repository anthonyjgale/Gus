<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
?>
<main class="gus-geo-page">
    <section>
        <h1><?php echo esc_html($post->post_title); ?> - <?php echo esc_html($tier_labels[$tier] ?? ucfirst($tier)); ?> Tier</h1>
        <p>This GEO page is generated for <?php echo esc_html($post->post_type); ?> content.</p>
    </section>
    <section>
        <h2>GEO Blocks</h2>
        <?php if (empty($blocks)) : ?>
            <p>No blocks have been generated for this tier yet.</p>
        <?php else : ?>
            <ul>
                <?php foreach ($blocks as $block) : ?>
                    <li><?php echo esc_html($block); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <section>
        <p><a href="<?php echo esc_url(get_permalink($post)); ?>">Back to entity</a></p>
    </section>
</main>
<?php
get_footer();
