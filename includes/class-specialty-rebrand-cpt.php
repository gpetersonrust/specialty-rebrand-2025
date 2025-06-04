<?php

 

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

    // Register taxonomies for specialty types
    $loader->add_action('init', $this, 'register_taxonomies', 0);
    }

    /**
     * Register taxonomies for specialty types
     */
    public function register_taxonomies() {
    $labels = array(
        'name'              => _x('Specialty Types', 'taxonomy general name', 'specialty-rebrand'),
        'singular_name'     => _x('Specialty Type', 'taxonomy singular name', 'specialty-rebrand'),
        'search_items'      => __('Search Specialty Types', 'specialty-rebrand'),
        'all_items'         => __('All Specialty Types', 'specialty-rebrand'),
        'edit_item'         => __('Edit Specialty Type', 'specialty-rebrand'),
        'update_item'       => __('Update Specialty Type', 'specialty-rebrand'),
        'add_new_item'      => __('Add New Specialty Type', 'specialty-rebrand'),
        'new_item_name'     => __('New Specialty Type Name', 'specialty-rebrand'),
        'menu_name'         => __('Specialty Types', 'specialty-rebrand'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'specialty-type'),
        'show_in_rest'      => true,
    );

    register_taxonomy('specialty_type', array('specialty'), $args);
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
            'rewrite'            => array('slug' => 'experts'),
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
    wp_nonce_field('specialty_metabox', 'specialty_metabox_nonce');

    $post_id = $post->ID;

    // Fetch all defined fields grouped by tab
    $fields = Specialty_Rebrand_Field_Manager::get_fields($post_id);



    Specialty_Rebrand_Field_Manager::render_field_tabs($fields);
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

        // Define fields to save with their types
        $fields = [
            'display_label' => 'text',
            'description' => 'textarea',
            'show_in_breadcrumb' => 'checkbox'
        ];

        // Save meta fields
        foreach ($fields as $field => $type) {
            Specialty_Rebrand_Field_Manager::save_meta_field($post_id, $field, $type);
        }

        // Save tier orders using an array
        $tier_orders = [
            'specialty_subspecialties' => '_specialty_tier_order_subspecialties',
            'specialty_physicians' => '_specialty_tier_order_physicians'
        ];

        foreach ($tier_orders as $source => $target) {
            Specialty_Rebrand_Field_Manager::save_tier_order($post_id, $source, $target);
        }

        // Save URL mappings
        Specialty_Rebrand_Field_Manager::save_url_mappings($post_id);

        
         
    }

      
}