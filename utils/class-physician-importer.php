<?php

class Physician_Importer {
    private $dry_run = false;
    private $log = [];

    public function import_from_json($json_input, $dry_run = false) {
        $this->dry_run = $dry_run;
        $this->log = [];

        // Load JSON from path or string
        if (file_exists($json_input)) {
            $json_data = file_get_contents($json_input);
        } else {
            $json_data = $json_input;
        }

        $data = json_decode($json_data, true);
        if (!is_array($data)) {
            $this->log[] = "Invalid JSON data.";
            return $this->log;
        }

        foreach ($data as $specialty) {
            $this->create_or_update_specialty($specialty);
        }

        return $this->dry_run ? $this->log : true;
    }

    private function create_or_update_specialty($specialty_data, $parent_id = null) {
        $existing_id = $this->match_existing_specialty($specialty_data['id'], $specialty_data['title']);
        $is_new = !$existing_id;

        if ($this->dry_run) {
            $this->log[] = ($is_new ? "Would create" : "Would update") . " specialty: {$specialty_data['title']}";
        }

        $specialty_id = $is_new ? null : $existing_id;

        if (!$this->dry_run) {
            if ($is_new) {
                $specialty_id = wp_insert_post([
                    'post_type' => 'specialty',
                    'post_title' => $specialty_data['title'],
                    'post_status' => 'publish'
                ]);
            }

            // Update post meta
            update_post_meta($specialty_id, 'display_label', $specialty_data['display_label']);
            update_post_meta($specialty_id, 'description', $specialty_data['description']);
            update_post_meta($specialty_id, 'show_in_breadcrumb', $specialty_data['show_in_breadcrumb']);
        }

        // Recursively process subspecialties
        $sub_ids = [];
        if (!empty($specialty_data['subspecialties'])) {
            foreach ($specialty_data['subspecialties'] as $sub) {
                $sub_id = $this->create_or_update_specialty($sub, $specialty_id);
                if ($sub_id) {
                    $sub_ids[] = $sub_id;
                }
            }
        }

        if (!$this->dry_run) {
            update_post_meta($specialty_id, '_specialty_tier_order_subspecialties', $sub_ids);
        }

        // Handle physicians
        $physician_ids = [];
        if (!empty($specialty_data['physicians'])) {
            foreach ($specialty_data['physicians'] as $phys) {
                if ($this->validate_physician($phys)) {
                    $physician_ids[] = $phys['id'];
                } else if ($this->dry_run) {
                    $this->log[] = "Would skip invalid physician: {$phys['title']} (ID {$phys['id']})";
                }
            }
        }

        if (!$this->dry_run) {
            update_post_meta($specialty_id, '_specialty_tier_order_physicians', $physician_ids);
        }

        return $specialty_id ?? null;
    }

    private function match_existing_specialty($id, $title) {
        if (get_post_type($id) === 'specialty' && get_the_title($id) === $title) {
            return $id;
        }

        $post = get_page_by_title($title, OBJECT, 'specialty');
        return $post ? $post->ID : null;
    }

    private function validate_physician($phys) {
        $id_valid = get_post_type($phys['id']) === 'physician';
        $title_valid = get_the_title($phys['id']) === $phys['title'];
        $type_valid = $phys['post_type'] === 'physician';
        return $id_valid && $title_valid && $type_valid;
    }
}
