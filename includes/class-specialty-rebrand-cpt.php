<?php

namespace Specialty_Rebrand;

if (!defined('ABSPATH')) {
    exit;
}

require_once SPECIALTY_REBRAND_DIR. 'includes/specialty-rebrand-field-manager.php';
/**
 * Register and manage the Specialty custom post type
 */
class Specialty_Rebrand_Specialty_CPT {

    /**
     * Register all hooks for this class
     *
     * @param Specialty_Rebrand_Loader $loader
     */
    public function define_hooks($loader) {
        $loader->add_action('init', $this, 'register_post_type', 0); // Priority 0 ensures it runs before other post types

        $loader->add_action('add_meta_boxes', $this, 'register_metaboxes');
        $loader->add_action('save_post_specialty', $this, 'save_metaboxes'); // Fixed: hook to save_metaboxes method
     }

    /**
     * Register the Specialty custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Specialties', 'post type general name', 'specialty-rebrand'),
            'singular_name'      => _x('Specialty', 'post type singular name', 'specialty-rebrand'),
            'menu_name'          => _x('Specialties', 'admin menu', 'specialty-rebrand'),
            'name_admin_bar'     => _x('Specialty', 'add new on admin bar', 'specialty-rebrand'),
            'add_new'            => _x('Add New', 'specialty', 'specialty-rebrand'),
            'add_new_item'       => __('Add New Specialty', 'specialty-rebrand'),
            'new_item'           => __('New Specialty', 'specialty-rebrand'),
            'edit_item'          => __('Edit Specialty', 'specialty-rebrand'),
            'view_item'          => __('View Specialty', 'specialty-rebrand'),
            'all_items'          => __('All Specialties', 'specialty-rebrand'),
            'search_items'       => __('Search Specialties', 'specialty-rebrand'),
            'not_found'          => __('No specialties found.', 'specialty-rebrand'),
            'not_found_in_trash' => __('No specialties found in Trash.', 'specialty-rebrand')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'specialty'),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 25,
            'supports'           => array('title', 'editor', 'page-attributes'),
            'show_in_rest'       => true,
        );

        register_post_type('specialty', $args);
    }

    /**
     * Register metaboxes for the specialty post type
     */
    public function register_metaboxes() {
        // Using single comprehensive metabox with tabs
        add_meta_box(
            'specialty_settings',
            __('Specialty Settings', 'specialty-rebrand'),
            array($this, 'render_comprehensive_metabox'),
            'specialty',
            'normal',
            'high'
        );
    }

    
    /**
     * Render comprehensive metabox with tabs (alternative approach)
     */
   public function render_comprehensive_metabox($post) {
    // wp_nonce_field('specialty_metabox', 'specialty_metabox_nonce');

    // $post_id = $post->ID;

    // // Fetch all defined fields grouped by tab
    // $fields = Specialty_Rebrand_Field_Manager::get_fields($post_id);

    // Specialty_Rebrand_Field_Manager::render_field_tabs($fields);
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
    private function save_tier_order($post_id, $field_prefix) {
        if (isset($_POST[$field_prefix])) {
            foreach ($_POST[$field_prefix] as $meta_key => $ids) {
                if (is_array($ids)) {
                    $ids = array_map('absint', $ids);
                    update_post_meta($post_id, $meta_key, $ids);
                }
            }
        }
    }

    /**
     * Save metabox data
     *
     * @param int $post_id The ID of the post being saved
     */
    public function save_metaboxes($post_id) {
        // Verify nonce
        if (!isset($_POST['specialty_metabox_nonce']) || 
            !wp_verify_nonce($_POST['specialty_metabox_nonce'], 'specialty_metabox')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

       

            // In save_metaboxes, replace the existing code with:
            $this->save_meta_field($post_id, 'display_label', 'text');
            $this->save_meta_field($post_id, 'description', 'textarea');
            $this->save_meta_field($post_id, 'is_visible', 'choice');

            // Save specialty tier orders using helper method
            $this->save_tier_order($post_id, 'specialty_subspecialties');
            $this->save_tier_order($post_id, 'specialty_physicians');
           
        // Save specialty tier orders using helper method
        $this->save_tier_order($post_id, 'specialty_subspecialties');
        $this->save_tier_order($post_id, 'specialty_physicians');

        // Save URL mappings
        if (isset($_POST['specialty_url_mappings'])) {
            $mappings = array_map(function($mapping) {
                return array(
                    'url' => esc_url_raw($mapping['url']),
                    'label' => sanitize_text_field($mapping['label'])
                );
            }, $_POST['specialty_url_mappings']);
            update_post_meta($post_id, '_specialty_url_mappings', $mappings);
        }
    }

     /**
         * Helper method to save meta fields with proper sanitization
         *
         * @param int $post_id The post ID
         * @param string $field_name The field name without prefix
         * @param string $type The field type (text, textarea, or choice)
         */
        private function save_meta_field($post_id, $field_name, $type = 'text') {
            $meta_key = '_specialty_' . $field_name;
            $post_key = 'specialty_' . $field_name;

            if (!isset($_POST[$post_key])) {
                return;
            }

            $value = $_POST[$post_key];

            switch ($type) {
                case 'textarea':
                    $sanitized_value = sanitize_textarea_field($value);
                    break;
                case 'choice':
                    $sanitized_value = (bool) $value;
                    break;
                case 'text':
                default:
                    $sanitized_value = sanitize_text_field($value);
                    break;
            }

            update_post_meta($post_id, $meta_key, $sanitized_value);
        }
}