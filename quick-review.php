 <?php

  public function get_physicians_by_specialty($request) {
        $specialty_slug = sanitize_text_field($request->get_param('specialty'));


     
        $location = sanitize_text_field($request->get_param('location'));
        $name = sanitize_text_field($request->get_param('name'));
    
        // Build a unique cache key based on filters
        $cache_key = 'physicians_' . md5("spec={$specialty_slug}&loc={$location}&title={$name}");
        $cached = get_transient($cache_key);
    
        // if ($cached !== false) {
       
        //     return rest_ensure_response($cached);
        // }


    
        // Load the query helper class
        require_once plugin_dir_path(__FILE__) . '../data/class-specialty-rebrand-physician-query.php';
        $service = new Specialty_Rebrand_Physician_Query();
    
        // Get all physicians for the specialty
        $physicians = $service->get_physicians_by_term_slug($specialty_slug);

        
    
        // Apply additional filters
        $filtered = array_filter($physicians, function($physician) use ($location, $name) {
            $match = true;
    
            // Normalize and match location
            if (!empty($location)) {
                $phys_location = strtolower(strip_tags($physician['locations']));
                $match = $match && strpos($phys_location, strtolower($location)) !== false;
            }
    
            // Match by name/title
            if (!empty($name)) {
                $match = $match && stripos($physician['name'], $name) !== false;
            }
    
            return $match;
        });
    
        // Get tiered children if needed
        $term = get_term_by('slug', $specialty_slug, 'specialty_area');

        print_r([
            'term' => $term,
            'filtered' => $filtered,
            'location' => $location,
            'name' => $name,
            'physicians' => $physicians
        ]);

       
   
        $children = [];

        $top_level_allowed_to_have_children = [67, 69];
        $is_allowed_top_level = $term ? in_array($term->term_id, $top_level_allowed_to_have_children) : false;
 
        if ($term &&($term->parent != 0 || $is_allowed_top_level)) { //  IF the term is a child or a top level term that is allowed to have children
            
            $children = $service->get_child_terms_with_physicians($term->term_id);
        }

      
        $filtered_children = [];
        foreach ($children as $child) {
           
           $filtered_child_posts = [] ;
           $child_posts = $child['posts'];
              foreach ($child_posts as $child_post) {
                 $child_match = true;
                 if (!empty($location)) {
                      $phys_location = strtolower(strip_tags($child_post['locations']));
                      $child_match = $child_match && strpos($phys_location, strtolower($location)) !== false;
                 }
     
                 // Match by name/title
                 if (!empty($name)) {
                      $child_match = $child_match && stripos($child_post['name'], $name) !== false;
                 }
     
                 if ($child_match) {
                      $filtered_child_posts[] = $child_post;
                 }
                }
                // replace post with filtered post
                $child['posts'] = $filtered_child_posts;
                // add to filtered children
                if (!empty($filtered_child_posts)) {
                    $filtered_children[] = $child;
                }
                  

        }
        
    
        $data = [
            'physicians'    => array_values($filtered),
            'term_children' =>  $filtered_children
        ];

         // Check if a description exists for the term or its ancestors
         $descriptions = get_option('specialty_brand_descriptions_data', []);
         $page_subtitle = '';

         $top_level_term = $this->find_top_level_term($term->term_id);

 
 
         if (is_array($descriptions)) {
             foreach ($descriptions as $description) {
                 if (isset($description['term'], $description['text']) && $description['term'] === $top_level_term->name) {
                     $page_subtitle = $description['text'];
                     break; // Exit the loop when a match is found
                 }
             }
         }
 
         // Add the subtitle to the response data if it exists
         if (!empty($page_subtitle)) {
             $data['page_subtitle'] = $page_subtitle;
         }
    
        $hour_in_seconds = 3600;
        // Cache the result for 1 hour
        set_transient($cache_key, $data,  $hour_in_seconds);
    
        return rest_ensure_response($data);
    }