<?php
  
class Specialty_Rebrand_Specialties_Editor_Page {

    public function define_hooks($loader) {
        $loader->add_action('admin_menu', $this, 'register_editor_submenu');
        $loader->add_action('admin_post_specialty_editor_save', $this, 'handle_editor_save');
    }

    public function register_editor_submenu() {
        add_submenu_page(
            'specialties-admin',
            __('Specialties Editor', 'specialty-rebrand'),
            __('Editor', 'specialty-rebrand'),
            'manage_options',
            'specialties-editor',
            [$this, 'render_editor_page']
        );
    }

 public function render_editor_page() {
    $selected_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Specialties Editor', 'specialty-rebrand') . '</h1>';

    // Fetch all specialties
    $specialties = get_posts([
        'post_type' => 'specialty',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    // Searchable filter box
    echo '<div class="specialty-filter-box" style="margin-bottom: 1.5em;">';
    echo '<label for="specialty-search" style="font-weight: bold;">' . esc_html__('Search Specialties:', 'specialty-rebrand') . '</label><br>';
    echo '<input type="text" id="specialty-search" placeholder="Type to filter..." style="width: 100%; max-width: 400px; padding: 6px; margin-top: 4px;">';
    echo '<ul id="specialty-search-results" style="margin-top: 10px; list-style: none; padding-left: 0;">';

    foreach ($specialties as $specialty) {
        $link = add_query_arg([
            'page' => 'specialties-editor',
            'post_id' => $specialty->ID
        ], admin_url('admin.php'));

        echo '<li class="specialty-item" style="margin-bottom: 4px;">';
        echo '<a href="' . esc_url($link) . '" style="text-decoration: none;">' . esc_html($specialty->post_title) . '</a>';
        echo '</li>';
    }

    echo '</ul></div>';

    // JS to handle filtering
    echo <<<JS
    <script>
    document.getElementById('specialty-search').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const items = document.querySelectorAll('#specialty-search-results .specialty-item');

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    });
    </script>
    JS;

    // Display form if specialty is selected
    if ($selected_id) {
        $fields = Specialty_Rebrand_Field_Manager::get_fields($selected_id);
        echo '<form method="POST" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="specialty_editor_save">';
        echo '<input type="hidden" name="post_id" value="' . esc_attr($selected_id) . '">';
        wp_nonce_field('specialty_editor_save_action', 'specialty_editor_nonce');

        // Display title of selected specialty
        $specialty = get_post($selected_id);
        echo '<h2>' . esc_html($specialty->post_title) . '</h2>';

        Specialty_Rebrand_Field_Manager::render_field_tabs($fields);

        echo '<p><button type="submit" class="button button-primary">' . esc_html__('Save Changes', 'specialty-rebrand') . '</button></p>';
        echo '</form>';

        // Add "View Specialty" button
        $permalink = get_permalink($selected_id);
        if ($permalink) {
            echo '<a href="' . esc_url($permalink) . '" target="_blank" class="button" style="margin-left: 10px;">' . 
                 esc_html__('View Specialty', 'specialty-rebrand') . '</a>';
        }
    } else {
        echo '<p><em>' . esc_html__('Please select a specialty to begin.', 'specialty-rebrand') . '</em></p>';
    }

    echo '</div>'; // .wrap
}


    public function handle_editor_save() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized user', 'specialty-rebrand'));
        }

        if (!isset($_POST['specialty_editor_nonce']) || 
            !wp_verify_nonce($_POST['specialty_editor_nonce'], 'specialty_editor_save_action')) {
            wp_die(__('Nonce check failed', 'specialty-rebrand'));
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_die(__('Invalid post ID', 'specialty-rebrand'));
        }

        // Save meta fields
        $fields = [
            'display_label' => 'text',
            'description' => 'textarea',
            'show_in_breadcrumb' => 'choice'
        ];

        foreach ($fields as $field => $type) {
            Specialty_Rebrand_Field_Manager::save_meta_field($post_id, $field, $type);
        }

        // Save tier assignments
        $tier_orders = [
            'specialty_subspecialties' => '_specialty_tier_order_subspecialties',
            'specialty_physicians' => '_specialty_tier_order_physicians'
        ];

        foreach ($tier_orders as $prefix => $meta_key) {
            Specialty_Rebrand_Field_Manager::save_tier_order($post_id, $prefix, $meta_key);
        }

        // Save URL mappings
        Specialty_Rebrand_Field_Manager::save_url_mappings($post_id);

        wp_redirect(add_query_arg(['page' => 'specialties-editor', 'post_id' => $post_id, 'saved' => 1], admin_url('admin.php')));
        exit;
    }
}
