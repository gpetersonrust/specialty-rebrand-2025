<?php
require_once plugin_dir_path(__FILE__) . '../../data/class-specialty-rebrand-physician-query.php';

$service = new Specialty_Rebrand_Physician_Query();
$specialty = isset($_GET['specialty']) && !empty($_GET['specialty']) ? $_GET['specialty'] : null;
$term = $specialty ? get_term_by('slug', $specialty, 'specialty_area') : null;

$location = isset($_GET['location']) && !empty($_GET['location']) ? $_GET['location'] : null;

$physician_posts = $service->get_physicians_by_term_slug($specialty);
$term_children_objects = $term && $term->parent != 0
    ? $service->get_child_terms_with_physicians($term->term_id)
    : [];
?>

<?php require_once plugin_dir_path(__FILE__) . './components/physician-sub-specialty-filters.php'; ?>
 
 
<div id="expert-grid-container"> 
<?php if (!empty($physician_posts)) : ?>
  <div class="expert-grid">
    <?php foreach ($physician_posts as $physician) : ?>
          <?php require plugin_dir_path(__FILE__) . './components/physician-sub-specialty-card.php'; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
 

 
</div>