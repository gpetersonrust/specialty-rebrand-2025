<?php 
$specialties_array = array();
$menu_items = wp_get_nav_menu_items(81); // Using the menu ID 81

// PRODUCTION NOTE: Replace menu ID 81 with the actual production menu ID for specialties


if ($menu_items) {
  foreach ($menu_items as $item) {
    // Look for the "Specialties" parent item
    if ($item->title == "Specialties" && $item->menu_item_parent == 0) {
      // Get its children
      foreach ($menu_items as $child) {
        if ($child->menu_item_parent == $item->ID) {
          $specialties_array[] = array(
            'title' => $child->title,
            'url' => $child->url
          );
        }
      }
    }
  }
}
$location = $_GET['expert_location'] ?? '';
$location = strtolower(str_replace(' ', '-', $location));

 
$name = $_GET['expert_name'] ?? '';
$current_uri = $_SERVER['REQUEST_URI'];

$parts = explode('/', $current_uri);
$parts = array_filter($parts);
$parts = array_values($parts);
$current_slug = strpos(end($parts), '?') !== false ? prev($parts) : end($parts);
 
?>
<div class="filters">
<div class="expert-filter-container">
  <select id="specialty-dropdown" class="expert-filter" data-filter-group="specialty">
    <?php
  
    // Debugging line to check the current URL
    $current_url = trailingslashit($current_url); // Ensure trailing slash for consistency
    ?>

    <option class="button" data-filter="."
      value="<?php echo esc_url(home_url('/experts/physicians/')); ?>"
      <?php selected($current_url, trailingslashit(home_url('/experts/physicians/'))); ?>>
      All Specialties
    </option>

    <?php foreach ($specialties_array as $specialty): ?>
      <option
        data-filter=".<?php echo esc_attr(sanitize_title($specialty['title'])); ?>"
        value="<?php echo esc_url($specialty['url']); ?>"
        <?php selected(sanitize_title($specialty['title']), $current_slug); ?>>
        <?php echo esc_html($specialty['title']); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>


  <!-- 👇 LOCATION filter remains as <select> -->
  <div class="expert-filter-container">
    <select class="button-group expert-filter" data-filter-group="location" id="location-dropdown">
      <option class="button" data-filter="." <?php echo empty($location) ? 'selected' : ''; ?>>All Locations</option>
      <option class="button" data-filter=".harriman" <?php echo strtolower($location) === 'harriman' ? 'selected' : ''; ?>>Harriman</option>
      <option class="button" data-filter=".lakeway" <?php echo strtolower($location) === 'lakeway' ? 'selected' : ''; ?>>Lakeway</option>
      <option class="button" data-filter=".maryville" <?php echo strtolower($location) === 'maryville' ? 'selected' : ''; ?>>Maryville</option>
      <option class="button" data-filter=".oak_ridge" <?php echo strtolower($location) === 'oak-ridge' ? 'selected' : ''; ?>>Oak Ridge</option>
      <option class="button" data-filter=".powell" <?php echo strtolower($location) === 'powell' ? 'selected' : ''; ?>>Powell</option>
      <option class="button" data-filter=".sevierville" <?php echo strtolower($location) === 'sevierville' ? 'selected' : ''; ?>>Sevierville</option>
      <option class="button" data-filter=".turkey_creek" <?php echo strtolower($location) === 'turkey-creek' ? 'selected' : ''; ?>>Turkey Creek</option>
      <option class="button" data-filter=".university" <?php echo strtolower($location) === 'university' ? 'selected' : ''; ?>>University</option>
      <option class="button" data-filter=".weisgarber" <?php echo strtolower($location) === 'weisgarber' ? 'selected' : ''; ?>>Weisgarber</option>
      <option class="button" data-filter=".west" <?php echo strtolower($location) === 'west' ? 'selected' : ''; ?>>West</option>
    </select>
  </div>

  <!-- 👇 SEARCH BY NAME -->
  <div class="expert-filter-container">
    <div class="button-group" data-filter-group="search">
      <input class="expert-filter-search-box" placeholder="Search By Name" value="<?php echo esc_attr($name); ?>">
    </div>
  </div>
</div>
 