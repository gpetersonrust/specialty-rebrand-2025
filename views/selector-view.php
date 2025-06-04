<?php
/**
 * Shared view for selecting and ordering related posts.
 *
 * @param string $label          - Section label (e.g., 'Physicians', 'Sub Specialties')
 * @param string $post_type      - Post type to pull (e.g., 'physician')
 * @param string $meta_key       - Meta key where active IDs are stored (e.g., '_specialty_tier_order_sports')
 * @param int    $post_id        - Current specialty post ID
 * @param string $field_prefix   - Prefix to use for name attributes
 */

if (!isset($label, $post_type, $meta_key, $post_id, $field_prefix)) {
    return;
}

$active_ids = get_post_meta($post_id, $meta_key, true);
if (!is_array($active_ids)) {
    $active_ids = [];
}

$all_items = get_posts([
    'post_type'      => $post_type,
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'post__not_in'   => [$post_id], // Exclude current post
]);

$inactive_items = array_filter($all_items, function ($item) use ($active_ids) {
    return !in_array($item->ID, $active_ids);
});
$active_items_unsorted = array_filter($all_items, function ($item) use ($active_ids) {
    return in_array($item->ID, $active_ids);
});

 


// Rebuild $active_items in saved order or alphabetically
$active_items = [];
if (!empty($is_alphabetical) && $is_alphabetical) {
    $active_items = $active_items_unsorted;
    
    // Sort by last name, then first name
    usort($active_items, function($a, $b) {
        // Remove suffixes
        $suffixes = ['jr', 'sr', 'ii', 'iii', 'iv'];
        $name_a = strtolower(str_replace('.', '', $a->post_title));
        $name_b = strtolower(str_replace('.', '', $b->post_title));
        
        foreach ($suffixes as $suffix) {
            $name_a = trim(preg_replace('/\s+' . $suffix . '\s*$/i', '', $name_a));
            $name_b = trim(preg_replace('/\s+' . $suffix . '\s*$/i', '', $name_b));
        }
        
        // Split names into parts
        $parts_a = array_values(array_filter(explode(' ', $name_a)));
        $parts_b = array_values(array_filter(explode(' ', $name_b)));
        
        // Compare last names
        $last_a = end($parts_a);
        $last_b = end($parts_b);
        
        if ($last_a !== $last_b) {
            return $last_a <=> $last_b;
        }
        
        // If last names are equal, compare first names
        return $parts_a[0] <=> $parts_b[0];
    });
} else {
    $active_map = [];
foreach ($active_items_unsorted as $item) {
    $active_map[$item->ID] = $item;
}
    // Use saved order
    foreach ($active_ids as $id) {
        if (isset($active_map[$id])) {
            $active_items[] = $active_map[$id];
        }
    }

}
?>

<div 
  class="sr-selector-container" data-meta-key="<?php echo esc_attr($meta_key); ?>"
  data-meta-key="<?php echo esc_attr($meta_key); ?>"
  data-field-prefix="<?php echo esc_attr($field_prefix); ?>">
  
    <h4><?php echo esc_html($label); ?></h4>
    <div class="sr-selector-filters">
        <input type="text" class="sr-filter-input" placeholder="<?php esc_attr_e('Filter items...', 'specialty-rebrand'); ?>">
    </div>
    <div class="sr-selector-columns">
        <div class="sr-selector-column inactive">
            <h5><?php _e('Inactive', 'specialty-rebrand'); ?></h5>
            <div class="sr-items sortable" data-status="inactive">
                <?php foreach ($inactive_items as $item): ?>
                    <div class="sr-item" data-id="<?php echo esc_attr($item->ID); ?>">
                        <?php echo esc_html($item->post_title); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="sr-selector-column active">
            <h5><?php _e('Active', 'specialty-rebrand'); ?></h5>
            <div class="sr-items sortable sr-active" data-status="active">
                <?php foreach ($active_items as $item): ?>
                    <div class="sr-item" data-id="<?php echo esc_attr($item->ID); ?>">
                        <?php echo esc_html($item->post_title); ?>
                        <input type="hidden" name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($meta_key); ?>][]" value="<?php echo esc_attr($item->ID); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.sr-selector-filters {
  margin-bottom: 10px;
}

.sr-filter-input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}
</style>

<script>
// document.addEventListener('DOMContentLoaded', function () {
//   const filterInputs = document.querySelectorAll('.sr-filter-input');
//   filterInputs.forEach(input => {
//     input.addEventListener('input', function () {
//       const query = this.value.toLowerCase();
//       const container = this.closest('.sr-selector-container');
//       const items = container.querySelectorAll('.sr-item');

//       items.forEach(item => {
//         const text = item.textContent.toLowerCase();
//         item.style.display = text.includes(query) ? '' : 'none';
//       });
//     });
//   });
// });
</script>
