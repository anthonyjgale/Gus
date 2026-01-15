<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
$hero = $blocks_by_type['hero']['data'] ?? array();
$value = $blocks_by_type['value']['data'] ?? array();
$highlights = $blocks_by_type['highlights']['data'] ?? array();
$how_it_works = $blocks_by_type['how_it_works']['data'] ?? array();
$faq = $blocks_by_type['faq']['data'] ?? array();
$cta = $blocks_by_type['cta']['data'] ?? array();
$discover_url = home_url('/' . $geo_base . '/discover/');
?>
<main class="gus-geo-page">
    <section class="gus-hero" id="gus-hero">
        <p class="gus-tier-label"><?php echo esc_html($tier_labels[$tier] ?? ucfirst($tier)); ?> Tier</p>
        <h1 class="gus-hero-title"><?php echo esc_html($hero['title'] ?? $post->post_title); ?></h1>
        <p class="gus-hero-dek"><?php echo esc_html($hero['dek'] ?? ''); ?></p>
    </section>

    <section class="gus-value" id="gus-value">
        <h2>Value</h2>
        <ul>
            <?php foreach (($value['bullets'] ?? array()) as $bullet) : ?>
                <li><?php echo esc_html($bullet); ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="gus-highlights" id="gus-highlights">
        <h2>Highlights</h2>
        <dl>
            <?php foreach (($highlights['items'] ?? array()) as $item) : ?>
                <dt><?php echo esc_html($item['label'] ?? ''); ?></dt>
                <dd><?php echo esc_html($item['value'] ?? ''); ?></dd>
            <?php endforeach; ?>
        </dl>
    </section>

    <section class="gus-how-it-works" id="gus-how-it-works">
        <h2>How it works</h2>
        <ol>
            <?php foreach (($how_it_works['steps'] ?? array()) as $step) : ?>
                <li>
                    <strong><?php echo esc_html($step['title'] ?? ''); ?></strong>
                    <p><?php echo esc_html($step['body'] ?? ''); ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section class="gus-faq" id="gus-faq">
        <h2>FAQ</h2>
        <?php foreach (($faq['items'] ?? array()) as $item) : ?>
            <details class="gus-faq-item">
                <summary><?php echo esc_html($item['q'] ?? ''); ?></summary>
                <p><?php echo esc_html($item['a'] ?? ''); ?></p>
            </details>
        <?php endforeach; ?>
    </section>

    <section class="gus-cta" id="gus-cta">
        <h2>Next steps</h2>
        <p class="gus-cta-primary">
            <a href="<?php echo esc_url($discover_url); ?>"><?php echo esc_html($cta['primary_text'] ?? 'Explore more'); ?></a>
        </p>
        <p class="gus-cta-secondary">
            <a href="<?php echo esc_url($discover_url); ?>"><?php echo esc_html($cta['secondary_text'] ?? 'Visit discovery'); ?></a>
        </p>
    </section>

    <section class="gus-back-link" id="gus-back-link">
        <p><a href="<?php echo esc_url(get_permalink($post)); ?>">Back to entity</a></p>
    </section>
</main>
<?php
get_footer();
