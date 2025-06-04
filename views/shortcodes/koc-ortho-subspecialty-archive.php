<?php
/**
 * Display all physician cards in alphabetical order
 */

// Include the subspecialty filters component
require_once SPECIALTY_REBRAND_DIR . 'views/shortcodes/components/physician-sub-specialty-filters.php';
require_once SPECIALTY_REBRAND_DIR . 'utils/koc-ortho-physician-helper.php';

// Initialize PhysicianHelper
$physician_helper = new PhysicianHelper();

// Get all physicians using the helper
$physicians = $physician_helper->get_all_physicians();

if (!empty($physicians)) : ?>
    <div id="expert-grid-container">
        <div class="expert-grid ">
        <?php foreach ($physicians as $physician) : 
            include plugin_dir_path(__FILE__) . './components/physician-sub-specialty-card.php';
        endforeach; ?>
        </div>
    </div>
<?php else : ?>
    <p>No physicians found.</p>
<?php endif; ?>