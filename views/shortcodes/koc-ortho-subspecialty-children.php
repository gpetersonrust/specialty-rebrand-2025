<?php
 
 


$subspecialties = get_post_meta(get_the_ID(), '_specialty_tier_order_subspecialties', true);

$disable_parent = get_post_meta(get_the_ID(), '_specialty_disable_parent', true);

if (!is_array($subspecialties)) {
    $subspecialties = array();
}

$formatted_subspecialties = array();
$formatted_tabs = array();
function process_subspecialty($subspecialty_id, &$formatted_subspecialties, &$formatted_tabs, $level = 0, $parent_title = '') {
    $subspecialty_display_label = get_post_meta($subspecialty_id, '_specialty_display_label', true);
    $subspecialty_title = !empty($subspecialty_display_label) ? $subspecialty_display_label : get_the_title($subspecialty_id);
    $parent_title = !empty($parent_title) ? $parent_title : null;

   
    // Get button label
    $button_label = get_post_meta($subspecialty_id, '_specialty_button_label', true);
    if (empty($button_label)) {
        $button_label = $subspecialty_title;
    }

 
    
    // Get direct physicians
    $physicians = get_post_meta($subspecialty_id, '_specialty_tier_order_physicians', true);
    if (!is_array($physicians)) {
        $physicians = array();
    }

    $formatted_physicians = array();
    foreach ($physicians as $physician_id) {
        $formatted_physicians[] = array(
            'name' => get_the_title($physician_id),
            'job_title' => get_field('title', $physician_id),
            'specialties' => get_field('specialty', $physician_id),
            'locations' => get_field('office_location', $physician_id),
            'permalink' => get_permalink($physician_id),
            'featured_image' => get_the_post_thumbnail_url($physician_id, 'full'), 
         
        );
    }
  $formatted_subspecialties[] = array(
        'title' => $subspecialty_title,
        'physicians' => $formatted_physicians, 
        'level' => $level,
        'parent_title' => $parent_title, 
        'button_label' => $button_label,
    );
    // Get child subspecialties
    $child_subspecialties = get_post_meta($subspecialty_id, '_specialty_tier_order_subspecialties', true);
    if (is_array($child_subspecialties)) {
        foreach ($child_subspecialties as $child_id) {
            process_subspecialty($child_id, $formatted_subspecialties, $formatted_tabs, $level + 1, $subspecialty_title);
        }
    }

 return $level;

  
}

$formatted_subspecialties = array();
foreach ($subspecialties as $subspecialty_id) {
   $level  =  process_subspecialty($subspecialty_id, $formatted_subspecialties, $formatted_tabs);


if ($level === 0) {
    $formatted_tabs[] = array(
        'title' => !empty(get_post_meta($subspecialty_id, '_specialty_display_label', true)) ? get_post_meta($subspecialty_id, '_specialty_display_label', true) : get_the_title($subspecialty_id),
        'id' => $subspecialty_id, 
        'button_label' => get_post_meta($subspecialty_id, '_specialty_button_label', true) ?:get_post_meta($subspecialty_id, '_specialty_display_label', true) ,
    );
}

}

 
?>


<?php require_once plugin_dir_path(__FILE__) . './components/physician-sub-specialty-filters.php'; ?>

<div class="subspecialty-filters">
    <?php 
    $other_non_surgeon_tabs = array_filter($formatted_tabs, function($tab) {
        
        $title = strtolower($tab['title']);
        $isSurgical = stripos($title, 'non-surgical') == false || stripos($title, 'non-surgeon') == false;
        
        return  $isSurgical;
    });

 
    if (!empty($formatted_tabs) && count($other_non_surgeon_tabs) > 2) : 
    ?>
        <button class="subspecialty-filter-button active" data-subspecialty="all">All</button>
        <?php foreach ($formatted_tabs as $subspecialty) : ?>
            <?php if ($subspecialty['title'] !== $post_title && stripos($subspecialty['title'], 'non-surgical') === false) : ?>
                <button class="subspecialty-filter-button" data-subspecialty="<?php echo esc_attr(sanitize_title($subspecialty['title'])); ?>">
                    <?= esc_html($subspecialty['button_label']); ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div 
id="expert-grid-container"
>
    <?php foreach ($formatted_subspecialties as $subspecialty) :
        
        $parent_title = !empty($subspecialty['parent_title']) ? $subspecialty['parent_title'] : '';
        $subspecialtiy_title;
        $subspecialtiy_title = !empty($parent_title) && !$disable_parent ? $parent_title . ' > ' . $subspecialty['title'] : (!empty($parent_title) ? '> ' . $subspecialty['title'] : $subspecialty['title']);
      
        ?>
        <div
        <?php if (!empty($parent_title)) : ?>data-parent-tier="<?php echo esc_attr(sanitize_title($parent_title)); ?>"<?php endif; ?>
        <?php if (!empty($subspecialty['title'])) : ?>id="<?php echo esc_attr(sanitize_title($subspecialty['title'])); ?>"<?php endif; ?>
        class="subspecialty-section
        <?php echo ($subspecialty['level'] === 1) ? ' subtier' : ''; ?>
        " data-subspecialty="<?php echo esc_attr(sanitize_title($subspecialty['title'])); ?>">
            <h3 class="expert-section-heading">
                <?php echo esc_html($subspecialtiy_title); ?>
            </h3>
            
            <?php if (!empty($subspecialty['physicians'])) : ?>
                <div class="expert-grid">
                    <?php foreach ($subspecialty['physicians'] as $physician) : ?>
                        <?php
                        $physician_helper = new PhysicianHelper();
                        $hide_class = $physician_helper->should_hide_physician($physician);
                        // Load physician card template
                        include SPECIALTY_REBRAND_DIR . 'views/shortcodes/components/physician-sub-specialty-card.php';
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php
$specialty_description = get_post_meta(get_the_ID(), '_specialty_description', true);

// Display the description if it exists
if (!empty($specialty_description)) : ?>
    <h2 class="physician-subtitle"><?php echo wp_kses_post($specialty_description); ?></h2>
<?php endif; ?>
 