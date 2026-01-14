<?php

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="gus-geo-discover">
    <section>
        <h1>GEO Discover</h1>
        <p>Browse GEO discovery pages for enabled entities.</p>
    </section>

    <?php if (empty($context['entities'])) : ?>
        <section>
            <p>No published GEO entities are available yet.</p>
        </section>
    <?php else : ?>
        <section>
            <ul>
                <?php foreach ($context['entities'] as $entity) : ?>
                    <?php
                    $tiers = Gus_Utils::get_enabled_tiers($entity->ID);
                    $post_type_object = get_post_type_object($entity->post_type);
                    $post_type_label = $post_type_object ? $post_type_object->labels->singular_name : $entity->post_type;
                    ?>
                    <li>
                        <strong><?php echo esc_html($entity->post_title); ?></strong>
                        <span>(<?php echo esc_html($post_type_label); ?>)</span>
                        <ul>
                            <?php foreach ($tiers as $tier) : ?>
                                <?php $geo_url = Gus_Utils::get_geo_url($entity->post_type, $entity->post_name, $tier); ?>
                                <li>
                                    <a href="<?php echo esc_url($geo_url); ?>">
                                        <?php echo esc_html(ucfirst($tier)); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</main>
<?php
get_footer();
