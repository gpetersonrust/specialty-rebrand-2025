<?php
$post_title = get_the_title();

 

if ($post_title === 'Physicians') {
    include plugin_dir_path(__FILE__) . 'koc-ortho-subspecialty-archive.php';
} else {
    include plugin_dir_path(__FILE__) . 'koc-ortho-subspecialty-children.php';
}
?>
