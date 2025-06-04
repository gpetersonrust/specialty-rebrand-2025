<?php

class Specialty_Rebrand_Admin_Pages {
    /**
     * Hook into WordPress using the plugin's loader class.
     *
     * @param Specialty_Rebrand_Loader $loader The loader instance for managing hooks.
     */
    public function define_hooks($loader) {
        $loader->add_action('admin_menu', $this, 'register_admin_pages');
        $loader->add_action('admin_post_export_specialty_structure', $this, 'handle_export_specialty_structure_action');
    }

    /**
     * Register admin menu pages.
     */
    public function register_admin_pages() {
        add_menu_page(
            __('Specialties Admin', 'specialty-rebrand'),
            __('Specialties Admin', 'specialty-rebrand'),
            'manage_options',
            'specialties-admin',
            [$this,  'render_specialties_admin_page'],
            'dashicons-admin-generic',
            5
        );

        add_submenu_page(
            'specialties-admin',
            __('Specialty Posts', 'specialty-rebrand'),
            __('Specialty Posts', 'specialty-rebrand'),
            'manage_options',
            'edit.php?post_type=specialty'
        );
    }

    /**
     * Renders the admin page content (forms) and handles file upload and dry run input.
     */
    public function render_specialties_admin_page() {
        if (isset($_POST['submit_specialty_json']) && current_user_can('manage_options')) {
            if (!isset($_POST['specialty_json_nonce']) || !wp_verify_nonce($_POST['specialty_json_nonce'], 'specialty_json_import')) {
                echo '<div class="error"><p>Invalid nonce for import.</p></div>';
            } else {
                $file = $_FILES['specialty_json'];
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $json_content = file_get_contents($file['tmp_name']);
                    $data = json_decode($json_content, true);
                    if (is_array($data)) {
                        $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === '1';
                        $result = $this->handle_import_specialty_structure($json_content, $dry_run);

                        if ($dry_run && is_array($result)) {
                            echo '<div class="notice notice-info"><p><strong>Dry Run Results:</strong></p><pre>' . esc_html(print_r($result, true)) . '</pre></div>';
                        } elseif ($result === true) {
                            echo '<div class="updated"><p>Import complete.</p></div>';
                        } else {
                            echo '<div class="error"><p>Import encountered issues.</p></div>';
                        }
                    } else {
                        echo '<div class="error"><p>Invalid JSON format.</p></div>';
                    }
                } else {
                    echo '<div class="error"><p>File upload error: ' . esc_html($file['error']) . '</p></div>';
                }
            }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Specialty Structure Management', 'specialty-rebrand'); ?></h1>

            <h2><?php esc_html_e('Import Specialty Structure', 'specialty-rebrand'); ?></h2>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('specialty_json_import', 'specialty_json_nonce'); ?>
                <input type="file" name="specialty_json" accept=".json" required>
                <p>
                    <label>
                        <input type="checkbox" name="dry_run" value="1">
                        <?php esc_html_e('Dry run (simulate import without saving)', 'specialty-rebrand'); ?>
                    </label>
                </p>
                <input type="submit" class="button button-primary" name="submit_specialty_json" value="<?php esc_attr_e('Import', 'specialty-rebrand'); ?>">
            </form>

            <h2><?php esc_html_e('Export Specialty Structure', 'specialty-rebrand'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="export_specialty_structure">
                <?php wp_nonce_field('specialty_json_export_action', 'specialty_json_export_nonce'); ?>
                <input type="submit" class="button button-secondary" name="export_specialty_json_button" value="<?php esc_attr_e('Export', 'specialty-rebrand'); ?>">
            </form>
        </div>
        <?php
    }

    /**
     * Handles the actual export logic when admin-post.php is called.
     */
    public function handle_export_specialty_structure_action() {
        if (!isset($_POST['specialty_json_export_nonce']) || !wp_verify_nonce($_POST['specialty_json_export_nonce'], 'specialty_json_export_action')) {
            wp_die('Invalid nonce for export.', 'Security Check', ['response' => 403]);
        }

        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to export this data.', 'Permission Denied', ['response' => 403]);
        }

        require_once SPECIALTY_REBRAND_DIR . 'utils/koc-orhto-physician-exporter.php';
        $exporter = new Physician_Exporter();
        $exporter->export_and_download();
        exit;
    }

    /**
     * Handles specialty import from JSON string and returns result or dry run log.
     *
     * @param string $json_content Raw JSON string from upload.
     * @param bool $is_dry_run Whether to simulate the import only.
     * @return true|array Returns true on success, or array of dry run log entries.
     */
    public function handle_import_specialty_structure($json_content, $is_dry_run = false) {
        require_once SPECIALTY_REBRAND_DIR . 'utils/class-physician-importer.php';
        $importer = new Physician_Importer();
        return $importer->import_from_json($json_content, $is_dry_run);
    }
}
