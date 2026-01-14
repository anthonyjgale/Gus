<?php

if (!defined('ABSPATH')) {
    exit;
}

$post = $context['post'];
$tier = $context['tier'];
$blocks = $context['blocks'];
$entity_url = $context['entity_url'];

get_header();
?>
<main class="gus-geo-page">
    <article>
        <?php foreach ($blocks as $block) : ?>
            <?php if (!isset($block['type'])) : ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if ($block['type'] === 'hero') : ?>
                <section class="gus-geo-hero">
                    <h1><?php echo esc_html($block['title']); ?></h1>
                    <p><?php echo esc_html($block['summary']); ?></p>
                </section>
            <?php elseif ($block['type'] === 'key_facts') : ?>
                <section class="gus-geo-key-facts">
                    <h2>Key facts</h2>
                    <ul>
                        <?php foreach ($block['items'] as $item) : ?>
                            <li>
                                <strong><?php echo esc_html($item['label']); ?>:</strong>
                                <?php echo esc_html($item['value']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php elseif ($block['type'] === 'faq') : ?>
                <section class="gus-geo-faq">
                    <h2>FAQs</h2>
                    <dl>
                        <?php foreach ($block['items'] as $item) : ?>
                            <dt><?php echo esc_html($item['q']); ?></dt>
                            <dd><?php echo esc_html($item['a']); ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </section>
            <?php elseif ($block['type'] === 'cta') : ?>
                <?php
                $cta_url = isset($block['url'])
                    ? $block['url']
                    : Gus_Utils::add_utm_params($entity_url, $post->post_type, $tier);
                ?>
                <section class="gus-geo-cta">
                    <h2><?php echo esc_html($block['label']); ?></h2>
                    <p>
                        <a href="<?php echo esc_url($cta_url); ?>">Apply / Enquire</a>
                    </p>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    </article>
</main>
<?php
get_footer();
