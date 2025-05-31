<?php
/**
 * Admin page: Specialties Editor
 * Allows inline editing and drag-and-drop reassignment of specialty tiers
 */

class Specialty_Rebrand_Specialties_Editor_Page {

    public function define_hooks($loader) {
        $loader->add_action('admin_menu', $this, 'register_editor_submenu');
 
        $loader->add_action('wp_ajax_save_specialty_editor', $this, 'handle_editor_save');
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
        echo '<div class="wrap"><h1>' . esc_html__('Specialties Editor', 'specialty-rebrand') . '</h1>';

        $parents = get_posts([
            'post_type' => 'specialty',
            'post_status' => 'publish',
            'numberposts' => -1,
            'post_parent' => 0,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        echo '<div id="specialty-editor-container">';
        foreach ($parents as $parent) {
            $child_ids = get_post_meta($parent->ID, '_specialty_tier_order_subspecialties', true);
            $child_ids = is_array($child_ids) ? $child_ids : [];

            echo '<div class="specialty-group" data-parent-id="' . esc_attr($parent->ID) . '">';
            echo '<h2 contenteditable="true" class="editable-parent" data-id="' . esc_attr($parent->ID) . '">' . esc_html($parent->post_title) . '</h2>';
            echo '<ul class="tier-list" data-id="' . esc_attr($parent->ID) . '">';

            foreach ($child_ids as $cid) {
                $child = get_post($cid);
                if (!$child || $child->post_type !== 'specialty') continue;

                echo '<li class="tier-item" data-id="' . esc_attr($cid) . '">';
                echo '<span contenteditable="true" class="editable-tier" data-id="' . esc_attr($cid) . '">' . esc_html($child->post_title) . '</span>';
                echo '</li>';
            }

            echo '</ul></div>';
        }
        echo '</div>';
        echo '<button id="save-specialties" class="button button-primary">Save Changes</button>';
        echo '</div>';
    }

    public function handle_editor_save() {
        check_ajax_referer('specialties_editor_nonce');

        $data = $_POST['structure'] ?? [];
        if (!is_array($data)) wp_send_json_error('Invalid structure');

        foreach ($data as $parent_id => $child_ids) {
            $parent_id = absint($parent_id);
            $child_ids = array_map('absint', $child_ids);

            update_post_meta($parent_id, '_specialty_tier_order_subspecialties', $child_ids);

            foreach ($child_ids as $child_id) {
                wp_update_post([
                    'ID' => $child_id,
                    'post_parent' => $parent_id
                ]);
            }
        }

        if (isset($_POST['titles'])) {
            foreach ($_POST['titles'] as $id => $title) {
                wp_update_post([
                    'ID' => absint($id),
                    'post_title' => sanitize_text_field($title)
                ]);
            }
        }

        wp_send_json_success();
    }
}
