<?php

class Physician_Exporter {
    private $data_dir;

    public function __construct() {
        $this->data_dir = plugin_dir_path(__FILE__) . '../data/';
        
        // Create data directory if it doesn't exist
        if (!file_exists($this->data_dir)) {
            mkdir($this->data_dir, 0755, true);
        }
    }

    public function export_and_download() {
        $all_specialties = $this->gather_specialty_data();
        $json_data = json_encode($all_specialties, JSON_PRETTY_PRINT);

        // Set headers for download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="specialties-export-' . date('Y-m-d') . '.json"');
        header('Pragma: no-cache');
        
        // Output the JSON data
        echo $json_data;
        exit;
    }

    private function gather_specialty_data() {
        // Set up query arguments
        $args = array(
            'post_type' => 'specialty',
            'tax_query' => array(
                array(
                    'taxonomy' => 'specialty_type',
                    'field'    => 'slug',
                    'terms'    => 'top-level-specialties'
                )
            ),
            'posts_per_page' => -1
        );

        // Run the query
        $query = new WP_Query($args);
        $all_specialties = array();

        // Loop through posts
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $specialty_data = $this->get_specialty_data(get_the_ID());
                $all_specialties[] = $specialty_data;
            }
        }

        // Reset post data
        wp_reset_postdata();

        return $all_specialties;
    }

    private function get_specialty_data($specialty_id) {
        $specialty_data = array(
            'id' => $specialty_id,
            'title' => get_the_title($specialty_id),
            'display_label' => get_post_meta($specialty_id, 'display_label', true),
            'description' => get_post_meta($specialty_id, 'description', true),
            'show_in_breadcrumb' => get_post_meta($specialty_id, 'show_in_breadcrumb', true),
            'subspecialties' => array(),
            'physicians' => array()
        );

        // Get subspecialties from tier order
        $subspecialties = get_post_meta($specialty_id, '_specialty_tier_order_subspecialties', true);
        if (!empty($subspecialties)) {
            foreach ($subspecialties as $sub_id) {
                $specialty_data['subspecialties'][] = $this->get_specialty_data($sub_id);
            }
        }

        // Get physicians from tier order
        $physicians = get_post_meta($specialty_id, '_specialty_tier_order_physicians', true);
        if (!empty($physicians)) {
            foreach ($physicians as $physician_id) {
                $specialty_data['physicians'][] = array(
                    'id' => $physician_id,
                    'title' => get_the_title($physician_id),
                    'post_type' => get_post_type($physician_id)
                );
            }
        }

        return $specialty_data;
    }
}
