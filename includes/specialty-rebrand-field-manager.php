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
                'views'  => SPECIALTY_REBRAND_DIR . 'views/fields/selector.php',
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
                'views'  => SPECIALTY_REBRAND_DIR . 'views/fields/selector.php',
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
                'tab_label' => __('Navigation Settings', 'specialty-rebrand'),
                'type'   => 'checkbox', 
                'name'   => 'specialty_disable_parent',
                'label'  => __('Disable parent text', 'specialty-rebrand'),
                'value'  => get_post_meta($post_id, '_specialty_disable_parent', true) ?? false,
                'id'     => 'specialty_disable_parent',
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
                'tab'    => 'button-label',
                'tab_label' => __('Button Label', 'specialty-rebrand'),
                'type'   => 'text',
                'name'   => 'specialty_button_label', 
                'label'  => __('Button Label', 'specialty-rebrand'),
                'value'  => get_post_meta($post_id, '_specialty_button_label', true),
                'id'     => 'specialty_button_label',
                'views'  => SPECIALTY_REBRAND_DIR . 'views/fields/text.php'
            ]
        ];
    }


    /**
     * Get field types for text, textarea, and checkbox fields
     *
     * @return array
     */
    public static function get_simple_field_types() {
        $fields = self::get_fields(get_the_ID());
        $simple_fields = [];
        
        foreach ($fields as $field) {
            if (in_array($field['type'], ['text', 'textarea', 'checkbox'])) {
                $field_name = str_replace('specialty_', '', $field['name']);
                $simple_fields[$field_name] = $field['type'];
            }
        }
        
        return $simple_fields;
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
     * Save metabox data
     *
     * @param int $post_id The ID of the post being saved
     */

    /**
     * Helper method to save tier orders
     *
     * @param int $post_id The post ID
     * @param string $field_prefix The prefix for the POST field
     */
    public static function save_tier_order($post_id, $field_prefix, $meta_key) {
        // Save POST data to JSON for debugging/logging

        $post_data = [
            'timestamp' => current_time('mysql'),
            'post_id' => $post_id,
            'field_prefix' => $field_prefix,
            'post_data' => $_POST[$field_prefix] ?? [],
            'meta_key' => $meta_key
            , 'ids' => $_POST[$field_prefix][$meta_key] ?? []
        ];

        $ids = isset($_POST[$field_prefix][$meta_key]) ? array_map('absint', $_POST[$field_prefix][$meta_key]) : [];
        file_put_contents(SPECIALTY_REBRAND_DIR . 'data/post_data.json', json_encode($post_data, JSON_PRETTY_PRINT));
        if (isset($_POST[$field_prefix])) {
           
            update_post_meta($post_id, $meta_key, $ids);
        }
    }

    /**
     * Helper method to save URL mappings
     * 
     * @param int $post_id The post ID
     */
    public static function save_url_mappings($post_id) {
        if (!isset($_POST['specialty_url_mappings'])) {
            return;
        }

        $mappings = array_map(function($mapping) {
            return array(
                'url' => esc_url_raw($mapping['url']),
                'label' => sanitize_text_field($mapping['label'])
            );
        }, $_POST['specialty_url_mappings']);

        update_post_meta($post_id, '_specialty_url_mappings', $mappings);
    }

    /**
     * Helper method to save meta fields with proper sanitization
     *
     * @param int $post_id The post ID
     * @param string $field_name The field name without prefix
     * @param string $type The field type (text, textarea, or choice)
     */
    public static function save_meta_field($post_id, $field_name, $type = 'text') {
        $meta_key = '_specialty_' . $field_name;
        $post_key = 'specialty_' . $field_name;

         

        $value = $_POST[$post_key];

        switch ($type) {
            case 'textarea':

                $sanitized_value = sanitize_textarea_field($value);
                break;
            case 'choice':
                $sanitized_value = (bool) $value;
                break;
            case 'checkbox':
                // Debug checkbox values
                $debug_data = [
                    'timestamp' => current_time('mysql'),
                    'post_id' => $post_id,
                    'field_name' => $field_name,
                    'raw_value' => $value,
                    'is_empty_string' => $value === ''
                ];
                $value = ($value === '') ? 0 : $value;
                 
                $sanitized_value = !empty($value);
                break;
            case 'text':
            default:
                $sanitized_value = sanitize_text_field($value);
                break;
        }

         

        update_post_meta($post_id, $meta_key, $sanitized_value);
    }



    /**
     * Save all specialty fields
     *
     * @param int $post_id The ID of the post being saved
     */
    public static function save_fields($post_id) {
        
        $fields = self::get_simple_field_types();
        foreach ($fields as $field => $type) {
            self::save_meta_field($post_id, $field, $type);
        }

        $tier_orders = [
            'specialty_subspecialties' => '_specialty_tier_order_subspecialties',
            'specialty_physicians' => '_specialty_tier_order_physicians'
        ];

        foreach ($tier_orders as $source => $target) {
            self::save_tier_order($post_id, $source, $target);
        }
    }
}
