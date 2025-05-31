<?php
/**
 * Shared field manager for rendering and saving specialty meta fields.
 * Can be reused across CPTs and custom admin pages.
 *
 * @plugin Moxcar Chatbot
 */
class Specialty_Rebrand_Field_Manager {

    /**
     * Define all editable fields grouped by tabs
     *
     * @param int $post_id
     * @return array
     */
    public static function get_fields($post_id) {
        return [
            [
                'tab'    => 'display',
                'tab_label' => __('Display Settings', 'specialty-rebrand'),
                'type'   => 'text',
                'name'   => 'specialty_display_label',
                'label'  => __('Display Label', 'specialty-rebrand'),
                'value'  => get_post_meta($post_id, '_specialty_display_label', true),
                'id'     => 'specialty_display_label',
                'views'  => SPECIALTY_REBRAND_DIR . 'views/fields/text.php'
            ],
            [
                'tab'    => 'physicians',
                'tab_label' => __('Physicians', 'specialty-rebrand'),
                'type'   => 'selector-view',
                'name'   => 'specialty_physicians',
                'label'  => __('Physician Assignments', 'specialty-rebrand'),
                'views'  => SPECIALTY_REBRAND_DIR . 'views/selector-view.php',
                'tier_definition' => [
                    'tier'            => ['slug' => 'physicians', 'label' => 'Physicians'],
                    'label'           => 'Physicians',
                    'post_type'       => 'physician',
                    'meta_key'        => '_specialty_tier_order_physicians',
                    'field_prefix'    => 'specialty_physicians',
                    'is_alphabetical' => true,
                    'post_id'         => $post_id
                ]
            ],
            [
                'tab'    => 'subspecialty',
                'tab_label' => __('Sub Specialty', 'specialty-rebrand'),
                'type'   => 'selector-view',
                'name'   => 'specialty_subspecialties',
                'label'  => __('Sub Specialty Assignments', 'specialty-rebrand'),
                'views'  => SPECIALTY_REBRAND_DIR . 'views/selector-view.php',
                'tier_definition' => [
                    'tier'            => ['slug' => 'subspecialties', 'label' => 'Sub Specialties'],
                    'label'           => 'Sub Specialties',
                    'post_type'       => 'specialty',
                    'meta_key'        => '_specialty_tier_order_subspecialties',
                    'field_prefix'    => 'specialty_subspecialties',
                    'is_alphabetical' => false,
                    'post_id'         => $post_id
                ]
            ],
            [
                'tab'    => 'visibility',
                'tab_label' => __('Visibility Settings', 'specialty-rebrand'),
                'type'   => 'checkbox',
                'name'   => 'specialty_is_visible',
                'label'  => __('Show this specialty publicly', 'specialty-rebrand'),
                'value'  => get_post_meta($post_id, '_specialty_is_visible', true) ?: true,
                'id'     => 'specialty_is_visible',
                'views'  => SPECIALTY_REBRAND_DIR . 'views/fields/checkbox.php'
            ],
            [
                'tab'    => 'description',
                'tab_label' => __('Description Settings', 'specialty-rebrand'),
                'type'   => 'textarea',
                'name'   => 'specialty_description',
                'label'  => __('Description', 'specialty-rebrand'),
                'value'  => get_post_meta($post_id, '_specialty_description', true),
                'id'     => 'specialty_description',
                'views'  => SPECIALTY_REBRAND_DIR . 'views/fields/textarea.php'
            ],
            [
                'tab'    => 'url-mapping',
                'tab_label' => __('URL Mapping Labels', 'specialty-rebrand'),
                'type'   => 'view',
                'views'  => SPECIALTY_REBRAND_DIR . 'views/url-mapping.php'
            ]
        ];
    }

    /**
     * Render full tabbed field editor
     *
     * @param array $fields
     */
    public static function render_field_tabs($fields) {
        $tabs = self::group_fields_by_tab($fields);
        echo '<div class="specialty-metabox-container">';
        include SPECIALTY_REBRAND_DIR . 'views/tabs/tab-nav.php';
        include SPECIALTY_REBRAND_DIR . 'views/tabs/tab-content.php';
        echo '</div>';
    }

    /**
     * Group fields into tabs
     *
     * @param array $fields
     * @return array
     */
    private static function group_fields_by_tab($fields) {
        $tabs = [];
        foreach ($fields as $field) {
            $tab = $field['tab'] ?? 'general';
            $tabs[$tab]['label'] = $field['tab_label'] ?? ucfirst($tab);
            $tabs[$tab]['fields'][] = $field;
        }
        return $tabs;
    }

    /**
     * Render a single field from its view or type
     *
     * @param array $field
     */
    public static function render_field($field) {
        $type = $field['type'] ?? 'text';
        $view = $field['views'] ?? SPECIALTY_REBRAND_DIR . 'views/fields/' . $type . '.php';

        if (file_exists($view)) {
            include $view;
        } else {
            echo '<p>' . esc_html__('Unknown field type:', 'specialty-rebrand') . ' ' . esc_html($type) . '</p>';
        }
    }

    /**
     * Save all fields from a POST request
     *
     * @param int   $post_id
     * @param array $fields
     * @param array $request
     */
    public static function save_fields($post_id, $fields, $request) {
        foreach ($fields as $field) {
            $type = $field['type'] ?? 'text';
            $name = $field['name'] ?? '';
            if (!$name) continue;

            switch ($type) {
                case 'text':
                    $value = sanitize_text_field($request[$name] ?? '');
                    update_post_meta($post_id, "_{$name}", $value);
                    break;

                case 'textarea':
                    $value = sanitize_textarea_field($request[$name] ?? '');
                    update_post_meta($post_id, "_{$name}", $value);
                    break;

                case 'checkbox':
                    $value = isset($request[$name]) ? (bool) $request[$name] : false;
                    update_post_meta($post_id, "_{$name}", $value);
                    break;

                case 'selector-view':
                    if (!empty($field['tier_definition']['field_prefix'])) {
                        $prefix = $field['tier_definition']['field_prefix'];
                        if (isset($request[$prefix])) {
                            foreach ($request[$prefix] as $meta_key => $ids) {
                                if (is_array($ids)) {
                                    $ids = array_map('absint', $ids);
                                    update_post_meta($post_id, $meta_key, $ids);
                                }
                            }
                        }
                    }
                    break;

                case 'view':
                    // Let custom views manage their own save logic
                    break;
            }
        }
    }
}
