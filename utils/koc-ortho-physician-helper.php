<?php

class PhysicianHelper {
     private $expert_location;
    private $expert_name;

    /**
     * Constructor that initializes filter values from GET parameters
     */
    public function __construct() {
        $this->expert_location = isset($_GET['expert_location']) ? sanitize_text_field($_GET['expert_location']) : '';
        $this->expert_name = isset($_GET['expert_name']) ? sanitize_text_field($_GET['expert_name']) : '';
    }

   
    /**
     * Sort callback function for sorting physicians by last name
     * 
     * @param array $a First physician array to compare
     * @param array $b Second physician array to compare
     * @return int Comparison result
     */
    private function sort_by_last_name($a, $b) {
        $parse = function ($name) {
            $parts = explode(' ', $name);
            $suffixes = ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
            $last = array_pop($parts);
            if (in_array($last, $suffixes)) $last = array_pop($parts);
            return ['last' => $last, 'first' => implode(' ', $parts)];
        };

        $nameA = $parse($a['name']);
        $nameB = $parse($b['name']);

        $cmp = strcasecmp($nameA['last'], $nameB['last']);
        return $cmp === 0 ? strcasecmp($nameA['first'], $nameB['first']) : $cmp;
    }

    /**
     * Returns all physicians, with optional filtering by location and name.
     *
     * @param array $filters Optional associative array with 'location' and 'name' keys.
     * @return array Filtered physician data.
     */
    public function get_all_physicians($filters = []) {
        $location = isset($filters['location']) ? strtolower(trim($filters['location'])) : ''; 
        $name     = isset($filters['name']) ? strtolower(trim($filters['name'])) : '';

        $args = [
            'post_type'      => 'physician',
            'posts_per_page' => -1,
        ];

        $query = new WP_Query($args);
        $posts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $physician = [
                    'name'           => get_the_title(),
                    'job_title'      => get_field('title'),
                    'featured_image' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                    'permalink'      => get_permalink(),
                    'specialties'    => wp_get_post_terms(get_the_ID(), 'specialty_area', ['fields' => 'names']),
                    'id'             => get_the_ID(),
                    'locations'      => trim(preg_replace('/\s+/', ' ', wp_strip_all_tags(get_field('office_location')))),
                ];

                $match = true;

                // Apply location filter
                if (!empty($location)) {
                    $phys_location = strtolower($physician['locations']);
                    $match = $match && strpos($phys_location, $location) !== false;
                }

                // Apply name filter
                if (!empty($name)) {
                    $match = $match && stripos($physician['name'], $name) !== false;
                }

                if ($match) {
                    $posts[] = $physician;
                }
            }

            wp_reset_postdata();
        }

        usort($posts, [$this, 'sort_by_last_name']);
        return $posts;
    }


    /**
     * Determines if a physician should be hidden based on filtering criteria
     *
     * @param array $physician Physician data array
     * @param string $expert_location Location filter value
     * @param string $expert_name Name filter value 
     * @return string CSS class name ('hidden' or empty string)
     */
    public function should_hide_physician($physician) {
        // Normalize inputs
        $name_filter_active = !empty($this->expert_name);
        $location_filter_active = !empty($this->expert_location);

        $location_match = true; 
        $name_match = true;

        // Check location match if filter is active
        if ($location_filter_active) {
            $physician_location = strtolower($physician['locations']);
            $location_match = strpos($physician_location, strtolower($this->expert_location)) !== false;
        }

        // Check name match if filter is active
        if ($name_filter_active) {
            $name = strtolower($physician['name']);
            $name_match = strpos($name, strtolower($this->expert_name)) !== false;
        }

        // Return class if any active filter does not match
        if (($location_filter_active && !$location_match) || ($name_filter_active && !$name_match)) {
            return 'hidden';
        }

        return 'active'; // Return 'active' if no filters are applied or all match
    }
}