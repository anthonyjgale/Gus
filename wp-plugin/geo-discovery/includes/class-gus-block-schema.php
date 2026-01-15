<?php

if (!defined('ABSPATH')) {
    exit;
}

class Gus_Block_Schema {
    const SCHEMA_VERSION = 'v1-blocks';

    public static function get_schema_version() {
        return self::SCHEMA_VERSION;
    }

    public static function get_block_contract($tier) {
        $count = self::get_tier_count($tier);
        return array(
            array(
                'type' => 'hero',
                'fields' => array(
                    'title' => 'string',
                    'dek' => 'string',
                ),
            ),
            array(
                'type' => 'value',
                'fields' => array(
                    'bullets' => array(
                        'count' => $count,
                        'item' => 'string',
                    ),
                ),
            ),
            array(
                'type' => 'highlights',
                'fields' => array(
                    'items' => array(
                        'count' => $count,
                        'item' => array('label' => 'string', 'value' => 'string'),
                    ),
                ),
            ),
            array(
                'type' => 'how_it_works',
                'fields' => array(
                    'steps' => array(
                        'count' => $count,
                        'item' => array('title' => 'string', 'body' => 'string'),
                    ),
                ),
            ),
            array(
                'type' => 'faq',
                'fields' => array(
                    'items' => array(
                        'count' => $count,
                        'item' => array('q' => 'string', 'a' => 'string'),
                    ),
                ),
            ),
            array(
                'type' => 'cta',
                'fields' => array(
                    'primary_text' => 'string',
                    'secondary_text' => 'string',
                ),
            ),
        );
    }

    public static function build_placeholder_blocks(WP_Post $post, $tier) {
        $count = self::get_tier_count($tier);
        $title = $post->post_title;
        $version = self::get_schema_version();
        $blocks = array(
            array(
                'type' => 'hero',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'title' => sprintf('Discover %s', $title),
                    'dek' => sprintf('Explore the essentials for %s in one quick view.', $title),
                ),
            ),
            array(
                'type' => 'value',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'bullets' => self::build_string_list(
                        $count,
                        'Key benefit %d for %s.',
                        $title
                    ),
                ),
            ),
            array(
                'type' => 'highlights',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'items' => self::build_pairs_list(
                        $count,
                        'Highlight %d',
                        'Notable detail for %s.',
                        $title
                    ),
                ),
            ),
            array(
                'type' => 'how_it_works',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'steps' => self::build_steps_list(
                        $count,
                        $title
                    ),
                ),
            ),
            array(
                'type' => 'faq',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'items' => self::build_faq_list(
                        $count,
                        $title
                    ),
                ),
            ),
            array(
                'type' => 'cta',
                'version' => $version,
                'tier' => $tier,
                'data' => array(
                    'primary_text' => sprintf('See more about %s', $title),
                    'secondary_text' => 'Browse more GEO discoveries',
                ),
            ),
        );

        return $blocks;
    }

    public static function validate_blocks(array $blocks, $tier) {
        $errors = array();
        $expected_types = self::get_block_types();

        if (!self::is_list_of_blocks($blocks)) {
            return array(
                'ok' => false,
                'errors' => array('Blocks must be an array of associative arrays.'),
            );
        }

        if (count($blocks) !== count($expected_types)) {
            $errors[] = sprintf(
                'Expected %d blocks in order, received %d.',
                count($expected_types),
                count($blocks)
            );
        }

        foreach ($expected_types as $index => $type) {
            if (!isset($blocks[$index]) || !is_array($blocks[$index])) {
                $errors[] = sprintf('Block %d must be an array.', $index + 1);
                continue;
            }

            $block = $blocks[$index];
            if (!self::is_assoc($block)) {
                $errors[] = sprintf('Block %d must be an associative array.', $index + 1);
            }

            $block_type = isset($block['type']) ? $block['type'] : '';
            if ($block_type !== $type) {
                $errors[] = sprintf('Block %d must be of type "%s".', $index + 1, $type);
            }

            if (!self::is_non_empty_string($block['version'] ?? null)) {
                $errors[] = sprintf('Block %d is missing a version.', $index + 1);
            } elseif ($block['version'] !== self::get_schema_version()) {
                $errors[] = sprintf('Block %d has an invalid schema version.', $index + 1);
            }

            if (!self::is_non_empty_string($block['tier'] ?? null)) {
                $errors[] = sprintf('Block %d is missing a tier.', $index + 1);
            } elseif ($block['tier'] !== $tier) {
                $errors[] = sprintf('Block %d tier must be "%s".', $index + 1, $tier);
            }

            if (!isset($block['data']) || !is_array($block['data'])) {
                $errors[] = sprintf('Block %d data must be an array.', $index + 1);
                continue;
            }

            self::validate_block_data($block_type, $block['data'], $tier, $errors);
        }

        return array(
            'ok' => empty($errors),
            'errors' => $errors,
        );
    }

    private static function validate_block_data($block_type, $data, $tier, array &$errors) {
        $expected_count = self::get_tier_count($tier);

        switch ($block_type) {
            case 'hero':
                self::require_string_field($data, 'title', 'hero title', $errors);
                self::require_string_field($data, 'dek', 'hero dek', $errors);
                break;
            case 'value':
                self::require_string_list($data, 'bullets', $expected_count, $errors);
                break;
            case 'highlights':
                self::require_pairs_list($data, 'items', $expected_count, array('label', 'value'), 'highlight', $errors);
                break;
            case 'how_it_works':
                self::require_pairs_list($data, 'steps', $expected_count, array('title', 'body'), 'step', $errors);
                break;
            case 'faq':
                self::require_pairs_list($data, 'items', $expected_count, array('q', 'a'), 'faq item', $errors);
                break;
            case 'cta':
                self::require_string_field($data, 'primary_text', 'cta primary text', $errors);
                self::require_string_field($data, 'secondary_text', 'cta secondary text', $errors);
                break;
        }
    }

    private static function require_string_field($data, $key, $label, array &$errors) {
        if (!array_key_exists($key, $data) || !self::is_non_empty_string($data[$key])) {
            $errors[] = sprintf('Missing or empty %s.', $label);
        }
    }

    private static function require_string_list($data, $key, $count, array &$errors) {
        if (!isset($data[$key]) || !is_array($data[$key])) {
            $errors[] = sprintf('Field "%s" must be an array of strings.', $key);
            return;
        }

        if (count($data[$key]) !== $count) {
            $errors[] = sprintf('Field "%s" must have %d items.', $key, $count);
        }

        foreach ($data[$key] as $index => $value) {
            if (!self::is_non_empty_string($value)) {
                $errors[] = sprintf('Field "%s" item %d must be a non-empty string.', $key, $index + 1);
            }
        }
    }

    private static function require_pairs_list($data, $key, $count, array $fields, $label, array &$errors) {
        if (!isset($data[$key]) || !is_array($data[$key])) {
            $errors[] = sprintf('Field "%s" must be an array of %s entries.', $key, $label);
            return;
        }

        if (count($data[$key]) !== $count) {
            $errors[] = sprintf('Field "%s" must have %d items.', $key, $count);
        }

        foreach ($data[$key] as $index => $item) {
            if (!is_array($item)) {
                $errors[] = sprintf('Field "%s" item %d must be an array.', $key, $index + 1);
                continue;
            }

            foreach ($fields as $field) {
                if (!array_key_exists($field, $item) || !self::is_non_empty_string($item[$field])) {
                    $errors[] = sprintf('Field "%s" item %d must include a non-empty "%s".', $key, $index + 1, $field);
                }
            }
        }
    }

    private static function build_string_list($count, $template, $title) {
        $items = array();
        for ($i = 1; $i <= $count; $i++) {
            $items[] = sprintf($template, $i, $title);
        }
        return $items;
    }

    private static function build_pairs_list($count, $label_template, $value_template, $title) {
        $items = array();
        for ($i = 1; $i <= $count; $i++) {
            $items[] = array(
                'label' => sprintf($label_template, $i),
                'value' => sprintf($value_template, $title),
            );
        }
        return $items;
    }

    private static function build_steps_list($count, $title) {
        $steps = array();
        for ($i = 1; $i <= $count; $i++) {
            $steps[] = array(
                'title' => sprintf('Step %d', $i),
                'body' => sprintf('Follow step %d to explore %s.', $i, $title),
            );
        }
        return $steps;
    }

    private static function build_faq_list($count, $title) {
        $items = array();
        for ($i = 1; $i <= $count; $i++) {
            $items[] = array(
                'q' => sprintf('What should I know about %s? (%d)', $title, $i),
                'a' => sprintf('This is a brief answer about %s for item %d.', $title, $i),
            );
        }
        return $items;
    }

    private static function get_block_types() {
        return array('hero', 'value', 'highlights', 'how_it_works', 'faq', 'cta');
    }

    public static function get_tier_count($tier) {
        $counts = array(
            'broad' => 3,
            'mid' => 5,
            'ultra' => 7,
        );

        $tier = is_scalar($tier) ? (string) $tier : '';
        $tier = sanitize_key($tier);
        return $counts[$tier] ?? $counts['broad'];
    }

    private static function is_non_empty_string($value) {
        return is_string($value) && trim($value) !== '';
    }

    private static function is_list_of_blocks($blocks) {
        if (!is_array($blocks)) {
            return false;
        }

        foreach ($blocks as $block) {
            if (!is_array($block) || !self::is_assoc($block)) {
                return false;
            }
        }

        return true;
    }

    private static function is_assoc(array $array) {
        foreach (array_keys($array) as $key) {
            if (is_string($key)) {
                return true;
            }
        }
        return false;
    }
}
