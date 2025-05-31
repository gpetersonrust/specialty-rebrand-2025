<?php

$post_title = get_the_title();

$subspecialties = get_post_meta(get_the_ID(), '_specialty_tier_order_subspecialties', true);

if (!is_array($subspecialties)) {
    $subspecialties = array();
}

$formatted_subspecialties = array();
foreach ($subspecialties as $subspecialty_id) {
    $subspecialty_display_label = get_post_meta($subspecialty_id, '_specialty_display_label', true);
    $subspecialty_title = !empty($subspecialty_display_label) ? $subspecialty_display_label : get_the_title($subspecialty_id);
    
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
            'featured_image' => get_the_post_thumbnail_url($physician_id, 'full')
        );
    }

    $formatted_subspecialties[] = array(
        'title' => $subspecialty_title,
        'physicians' => $formatted_physicians
    );
}
?>


<?php require_once plugin_dir_path(__FILE__) . './components/physician-sub-specialty-filters.php'; ?>

<div class="subspecialty-filters">
    <?php if (!empty($formatted_subspecialties)) : ?>
        <button class="subspecialty-filter-button active" data-subspecialty="all">All</button>
        <?php foreach ($formatted_subspecialties as $subspecialty) : ?>
            <?php if ($subspecialty['title'] !== $post_title && stripos($subspecialty['title'], 'non-surgical') === false) : ?>
                <button class="subspecialty-filter-button" data-subspecialty="<?php echo esc_attr(sanitize_title($subspecialty['title'])); ?>">
                    <?php echo esc_html($subspecialty['title']); ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="expert-grid-container">
    <?php foreach ($formatted_subspecialties as $subspecialty) : ?>
        <h3 class="expert-section-heading"><?php echo esc_html($subspecialty['title']); ?></h3>
        
        <?php if (!empty($subspecialty['physicians'])) : ?>
            <div class="expert-grid">
                <?php foreach ($subspecialty['physicians'] as $physician) : ?>
                    <?php
                    // Load physician card template
                    include SPECIALTY_REBRAND_DIR . 'views/shortcodes/components/physician-sub-specialty-card.php';
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
