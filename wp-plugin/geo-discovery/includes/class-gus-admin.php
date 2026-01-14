<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Admin {
    private $routing;

    public function __construct(Gus_Routing $routing) {
        $this->routing = $routing;
    }

    public function init() {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_entities_submission'));
    }

    public function register_menu() {
        add_menu_page(
            'GUS',
            'GUS',
            'manage_options',
            'gus-settings',
            array($this, 'render_settings_page'),
            'dashicons-admin-site'
        );

        add_submenu_page(
            'gus-settings',
            'GUS Settings',
            'Settings',
            'manage_options',
            'gus-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'gus-settings',
            'GUS Entities',
            'Entities',
            'manage_options',
            'gus-entities',
            array($this, 'render_entities_page')
        );
    }

    public function register_settings() {
        register_setting(
            'gus_settings',
            'gus_geo_base',
            array(
                'type' => 'string',
                'sanitize_callback' => array($this, 'sanitize_geo_base'),
                'default' => 'geo',
            )
        );

        register_setting(
            'gus_settings',
            'gus_public_geo_enabled',
            array(
                'type' => 'boolean',
                'sanitize_callback' => array($this, 'sanitize_checkbox'),
                'default' => true,
            )
        );

        register_setting(
            'gus_settings',
            'gus_geo_enabled_post_types',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_post_types'),
                'default' => array('post'),
            )
        );

        add_settings_section(
            'gus_settings_main',
            'GUS Settings',
            '__return_null',
            'gus-settings'
        );

        add_settings_field(
            'gus_geo_base',
            'GEO Base',
            array($this, 'render_geo_base_field'),
            'gus-settings',
            'gus_settings_main'
        );

        add_settings_field(
            'gus_public_geo_enabled',
            'Public GEO Enabled',
            array($this, 'render_public_geo_field'),
            'gus-settings',
            'gus_settings_main'
        );

        add_settings_field(
            'gus_geo_enabled_post_types',
            'Enabled Post Types',
            array($this, 'render_post_types_field'),
            'gus-settings',
            'gus_settings_main'
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>GUS Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('gus_settings');
                do_settings_sections('gus-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_entities_page() {
        $post_types = $this->routing->get_enabled_post_types();
        $entities = $this->get_entities($post_types);
        $tier_labels = Gus_Utils::get_tier_labels();
        $geo_base = $this->routing->get_geo_base();
        ?>
        <div class="wrap">
            <h1>GUS Entities</h1>
            <form method="post">
                <?php wp_nonce_field('gus_entities_action', 'gus_entities_nonce'); ?>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Entity</th>
                            <th>Post Type</th>
                            <th>Enabled</th>
                            <th>Status</th>
                            <th>Tiers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entities)) : ?>
                            <tr>
                                <td colspan="6">No published entities available.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($entities as $entity) : ?>
                                <?php
                                $enabled = (bool) get_post_meta($entity->ID, '_gus_enabled', true);
                                $status = get_post_meta($entity->ID, '_gus_status', true);
                                $status = in_array($status, array('draft', 'published'), true) ? $status : 'draft';
                                $tiers = get_post_meta($entity->ID, '_gus_tiers_enabled', true);
                                if (!is_array($tiers)) {
                                    $tiers = array();
                                }
                                $preview_links = $this->get_preview_links($entity, $tiers, $status, $geo_base, $tier_labels);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($entity->post_title); ?></strong>
                                    </td>
                                    <td><?php echo esc_html($entity->post_type); ?></td>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="entities[<?php echo esc_attr($entity->ID); ?>][enabled]" value="1" <?php checked($enabled); ?> />
                                            Enabled
                                        </label>
                                    </td>
                                    <td>
                                        <select name="entities[<?php echo esc_attr($entity->ID); ?>][status]">
                                            <option value="draft" <?php selected($status, 'draft'); ?>>Draft</option>
                                            <option value="published" <?php selected($status, 'published'); ?>>Published</option>
                                        </select>
                                    </td>
                                    <td>
                                        <?php foreach ($tier_labels as $tier_key => $tier_label) : ?>
                                            <label style="display:block;">
                                                <input type="checkbox" name="entities[<?php echo esc_attr($entity->ID); ?>][tiers][<?php echo esc_attr($tier_key); ?>]" value="1" <?php checked(in_array($tier_key, $tiers, true)); ?> />
                                                <?php echo esc_html($tier_label); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <button class="button" name="gus_generate_blocks" value="<?php echo esc_attr($entity->ID); ?>">Generate blocks</button>
                                        <?php if (!empty($preview_links)) : ?>
                                            <div style="margin-top:8px;">
                                                <?php echo wp_kses_post($preview_links); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php submit_button('Save Entities'); ?>
            </form>
        </div>
        <?php
    }

    public function sanitize_geo_base($value) {
        $value = sanitize_title_with_dashes($value);
        $value = $value === '' ? 'geo' : $value;
        $previous = get_option('gus_geo_base', 'geo');
        if ($previous !== $value) {
            flush_rewrite_rules();
        }

        return $value;
    }

    public function sanitize_checkbox($value) {
        return !empty($value);
    }

    public function sanitize_post_types($value) {
        $public_post_types = get_post_types(array('public' => true));
        $value = is_array($value) ? $value : array();
        $filtered = array();

        foreach ($value as $post_type) {
            $post_type = sanitize_key($post_type);
            if (in_array($post_type, $public_post_types, true)) {
                $filtered[] = $post_type;
            }
        }

        return array_values(array_unique($filtered));
    }

    public function render_geo_base_field() {
        $value = esc_attr(get_option('gus_geo_base', 'geo'));
        echo '<input type="text" name="gus_geo_base" value="' . $value . '" class="regular-text" />';
    }

    public function render_public_geo_field() {
        $value = (bool) get_option('gus_public_geo_enabled', true);
        echo '<input type="hidden" name="gus_public_geo_enabled" value="0" />';
        echo '<label><input type="checkbox" name="gus_public_geo_enabled" value="1" ' . checked($value, true, false) . ' /> Enable public GEO routes</label>';
    }

    public function render_post_types_field() {
        $selected = get_option('gus_geo_enabled_post_types', array('post'));
        if (!is_array($selected)) {
            $selected = array();
        }

        $post_types = get_post_types(array('public' => true), 'objects');
        echo '<input type="hidden" name="gus_geo_enabled_post_types[]" value="" />';
        foreach ($post_types as $post_type) {
            if ($post_type->name === 'attachment') {
                continue;
            }

            $checked = in_array($post_type->name, $selected, true);
            echo '<label style="display:block;">';
            echo '<input type="checkbox" name="gus_geo_enabled_post_types[]" value="' . esc_attr($post_type->name) . '" ' . checked($checked, true, false) . ' /> ';
            echo esc_html($post_type->labels->singular_name);
            echo '</label>';
        }
    }

    private function get_entities($post_types) {
        if (empty($post_types)) {
            return array();
        }

        $query = new WP_Query(
            array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => 200,
                'no_found_rows' => true,
            )
        );

        return $query->posts;
    }

    private function get_preview_links(WP_Post $post, $tiers, $status, $geo_base, $tier_labels) {
        if ($status !== 'published') {
            return '';
        }

        $links = array();
        foreach ($tiers as $tier) {
            if (!isset($tier_labels[$tier])) {
                continue;
            }
            $url = home_url('/' . $geo_base . '/' . $post->post_type . '/' . $post->post_name . '/' . $tier . '/');
            $links[] = '<a href="' . esc_url($url) . '" target="_blank">Preview ' . esc_html($tier_labels[$tier]) . '</a>';
        }

        return implode('<br />', $links);
    }

    private function handle_entities_submission() {
        if (!isset($_POST['gus_entities_nonce'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('gus_entities_action', 'gus_entities_nonce');

        $entities = isset($_POST['entities']) ? (array) $_POST['entities'] : array();
        $generate_post_id = isset($_POST['gus_generate_blocks']) ? absint($_POST['gus_generate_blocks']) : 0;

        foreach ($entities as $post_id => $data) {
            $post_id = absint($post_id);
            if (!$post_id) {
                continue;
            }

            $enabled = !empty($data['enabled']) ? 1 : 0;
            $status = isset($data['status']) && in_array($data['status'], array('draft', 'published'), true) ? $data['status'] : 'draft';
            $tiers = array();

            if (isset($data['tiers']) && is_array($data['tiers'])) {
                foreach (array('broad', 'mid', 'ultra') as $tier) {
                    if (!empty($data['tiers'][$tier])) {
                        $tiers[] = $tier;
                    }
                }
            }

            update_post_meta($post_id, '_gus_enabled', $enabled);
            update_post_meta($post_id, '_gus_status', $status);
            update_post_meta($post_id, '_gus_tiers_enabled', $tiers);

            if ($generate_post_id === $post_id) {
                $post = get_post($post_id);
                if ($post) {
                    foreach ($tiers as $tier) {
                        $blocks = Gus_Utils::build_placeholder_blocks($post, $tier);
                        update_post_meta($post_id, '_gus_blocks_' . $tier, $blocks);
                    }
                }
            }
        }
    }
}
