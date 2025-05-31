<?php
class Specialty_Rebrand_Admin_Pages {

    /**
     * Hook into WordPress using the plugin's loader class.
     *
     * @param Specialty_Rebrand_Loader $loader The loader instance for managing hooks.
     */
    public function define_hooks($loader) {
        // Use 'init' to create the page early in the WP lifecycle
        $loader->add_action('init', $this, 'register_admin_pages');

        
    }

    /**
     * Create the /specialties-admin page programmatically if it doesn't already exist.
     * This ensures the route exists without requiring manual page creation.
     */
    public function register_admin_pages() {

       add_menu_page(
            __('Specialties Admin', 'specialty-rebrand'), // Page title
            __('Specialties Admin', 'specialty-rebrand'), // Menu title
            'manage_options', // Capability required to access this page
            'specialties-admin', // Menu slug
            [$this,  'handle_specialty_import'], // Callback function to render the page
            'dashicons-admin-generic', // Icon for the menu item
            5 // Position in the menu
        );
         
        add_submenu_page(
            'specialties-admin', // Parent menu slug
            __('Specialty Posts', 'specialty-rebrand'), // Page title
            __('Specialty Posts', 'specialty-rebrand'), // Menu title
            'manage_options', // Capability
            'edit.php?post_type=specialty' // Menu slug/link
        );
    }

    
    /**
     * Handle the specialty structure import form and processing.
     * Displays the import form and processes the JSON file upload.
     */
    public function handle_specialty_import() {
        ?>
        <h2>Import Specialty Structure</h2>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('specialty_json_import', 'specialty_json_nonce'); ?>
            <input type="file" name="specialty_json" accept=".json" required>
            <input type="submit" class="button button-primary" name="submit_specialty_json" value="Import">
        </form>
        <?php

        if (isset($_POST['submit_specialty_json']) && current_user_can('manage_options')) {
            if (!isset($_POST['specialty_json_nonce']) || !wp_verify_nonce($_POST['specialty_json_nonce'], 'specialty_json_import')) {
                echo '<div class="error"><p>Invalid nonce.</p></div>';
                return;
            }

            $file = $_FILES['specialty_json'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $json = file_get_contents($file['tmp_name']);
                $data = json_decode($json, true);
                if (is_array($data)) {
                    $this->specialty_import_structure($data);
                    echo '<div class="updated"><p>Import complete.</p></div>';
                } else {
                    echo '<div class="error"><p>Invalid JSON format.</p></div>';
                }
            }
        }
    }



    function specialty_import_structure(array $data) {
    foreach ($data as $specialty_name => $tiers) {
        // Check if top-level specialty post exists
        $parent_post = get_page_by_title($specialty_name, OBJECT, 'specialty');
        $prefix_name = $specialty_name . ' - ';
        if (!$parent_post) {
            $parent_id = wp_insert_post([
                'post_type' => 'specialty',
                'post_status' => 'publish',
                'post_title' => $specialty_name,
            ]);
        } else {
            $parent_id = $parent_post->ID;
        }

        foreach ($tiers as $tier_name => $physician_ids) {
            // Check if tier already exists (by title & parent match)
            $tier_post = get_page_by_title($prefix_name . $tier_name, OBJECT, 'specialty');
            if (!$tier_post) {
                $tier_id = wp_insert_post([
                    'post_type' => 'specialty',
                    'post_status' => 'publish',
                    'post_title' =>  $prefix_name . $tier_name,
                    'post_parent' => $parent_id
                ]);
            } else {
                $tier_id = $tier_post->ID;
                // Update parent in case structure changed
                wp_update_post([
                    'ID' => $tier_id,
                    'post_parent' => $parent_id
                ]);
            }

            // Validate physician IDs
            $valid_physicians = array_filter($physician_ids, function ($pid) {
                return get_post_type((int) $pid) === 'physician';
            });

            // Save relationship via known meta key
            $meta_key = '_specialty_tier_order_physicians';
            update_post_meta($tier_id, $meta_key, array_map('absint', $valid_physicians));

            // Get existing subspecialties from parent and append this tier
            $parent_subspecialties = get_post_meta($parent_id, '_specialty_tier_order_subspecialties', true);
            $parent_subspecialties = is_array($parent_subspecialties) ? $parent_subspecialties : array();
            if (!in_array($tier_id, $parent_subspecialties)) {
                $parent_subspecialties[] = $tier_id;
                update_post_meta($parent_id, '_specialty_tier_order_subspecialties', array_map('absint', $parent_subspecialties));
            }
        }
    }
}
}
?>
