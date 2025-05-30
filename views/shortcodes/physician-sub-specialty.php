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


<div class="filters">
  <!-- 👇 CUSTOM DROPDOWN: Specialty -->
  <div class="expert-filter-container">
    <div id="specialty-filter-wrapper" class="custom-dropdown">
      <div class="action-wrapper">
      <button id="specialty-trigger" class="dropdown-trigger">All Specialties
 

      </button>
        <svg 
    id="specialty-arrow-down"
   
   fill="#000000" xmlns="http://www.w3.org/2000/svg" 
	 width="800px" height="800px" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve">
<path d="M8.3,14h35.4c1,0,1.7,1.3,0.9,2.2L27.3,37.4c-0.6,0.8-1.9,0.8-2.5,0L7.3,16.2C6.6,15.3,7.2,14,8.3,14z"/>
</svg>
</div>
      <div id="specialty-menu" class="dropdown-menu hidden">
       
        <ul id="specialty-options" class="dropdown-options">
          <li class="specialty-option all-specialties" data-filter=".">All Specialties</li>
          <li class="specialty-option cardiology" data-filter=".cardiology">Cardiology
            <ul class="subspecialty-list">
              <li class="subspecialty-option interventional" data-filter=".interventional">Interventional Cardiology</li>
              <li class="subspecialty-option electrophysiology" data-filter=".electrophysiology">Cardiac Electrophysiology</li>
            </ul>
          </li>
          <li class="specialty-option orthopedics" data-filter=".orthopedics">Orthopedics
            <ul class="subspecialty-list">
              <li class="subspecialty-option sports-medicine" data-filter=".sports-medicine">Sports Medicine</li>
              <li class="subspecialty-option joint-replacement" data-filter=".joint-replacement">Joint Replacement</li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- 👇 LOCATION filter remains as <select> -->
  <div class="expert-filter-container">
    <select class="button-group expert-filter" data-filter-group="location" id="location-dropdown">
      <option class="button" data-filter="." selected>All Locations</option>
      <option class="button" data-filter=".harriman">Harriman</option>
      <option class="button" data-filter=".lakeway">Lakeway</option>
      <option class="button" data-filter=".maryville">Maryville</option>
      <option class="button" data-filter=".oak_ridge">Oak Ridge</option>
      <option class="button" data-filter=".powell">Powell</option>
      <option class="button" data-filter=".sevierville">Sevierville</option>
      <option class="button" data-filter=".turkey_creek">Turkey Creek</option>
      <option class="button" data-filter=".university">University</option>
      <option class="button" data-filter=".weisgarber">Weisgarber</option>
      <option class="button" data-filter=".west">West</option>
    </select>
  </div>

  <!-- 👇 SEARCH BY NAME -->
  <div class="expert-filter-container">
    <div class="button-group" data-filter-group="search">
      <input class="expert-filter-search-box" placeholder="Search By Name">
    </div>
  </div>
</div>

<!-- <div id="expert-loader" class="expert-loader" style="display: none;">
  <div class="spinner"></div>
</div> -->
<div id="expert-loader" class="expert-loader" style="display: none;">
  <div class="spinner"></div>
</div>

<div id="expert-grid-container"> 
<?php if (!empty($physician_posts)) : ?>
  <div class="expert-grid">
    <?php foreach ($physician_posts as $physician) : 
       
        ?>
      <div
        data-location="<?php echo esc_attr(sanitize_title(str_replace("\n", " ", $physician['locations']))); ?>"
        data-specialties="<?php echo esc_attr(implode(' ', array_map('sanitize_title', $physician['specialties']))); ?>"  
      class="expert-card">
        <a href="<?php echo esc_url($physician['permalink']); ?>">
          <img src="<?php echo esc_url($physician['featured_image']); ?>" alt="<?php echo esc_attr($physician['name']); ?>">
          <div class="expert-grid-title">
            <?php echo esc_html($physician['name']); ?><br>
            <?php echo esc_html($physician['job_title']); ?>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($term_children_objects)) : ?>
  <?php foreach ($term_children_objects as $group) : 
      $group_name = $group['term']->name;
      $posts = $group['posts'];
  ?>
    <?php if (!empty($posts)) : ?>
      <h3 class="expert-section-heading"><?php echo esc_html($group_name); ?></h3>
      <div class="expert-grid">
        <?php foreach ($posts as $physician) : ?>
          <div 
          
          data-location="<?php echo esc_attr(sanitize_title(str_replace("\n", " ", $physician['locations']))); ?>"
          data-specialty="<?php echo esc_attr(implode(' ', array_map('sanitize_title', $physician['specialties']))); ?>"
          class="expert-card">
            <a href="<?php echo esc_url($physician['permalink']); ?>">
              <img src="<?php echo esc_url($physician['featured_image']); ?>" alt="<?php echo esc_attr($physician['name']); ?>">
              <div class="expert-grid-title">
                <?php echo esc_html($physician['name']); ?><br>
                <?php echo esc_html($physician['job_title']); ?>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
<?php endif; ?>


 
</div>