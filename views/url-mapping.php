<?php
$mappings = get_post_meta($post->ID, '_specialty_url_mappings', true);
if (!is_array($mappings)) {
    $mappings = [];
}
?>

<div id="sr-url-mapping-wrapper">
    <div id="sr-url-mapping-list">
        <?php foreach ($mappings as $index => $mapping): ?>
            <div class="sr-url-mapping-row">
                <input type="url" name="specialty_url_mappings[<?php echo $index; ?>][url]" value="<?php echo esc_attr($mapping['url']); ?>" placeholder="https://example.com" style="width: 45%">
                <input type="text" name="specialty_url_mappings[<?php echo $index; ?>][label]" value="<?php echo esc_attr($mapping['label']); ?>" placeholder="Label" style="width: 45%">
                <button type="button" class="sr-remove-mapping">×</button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="sr-add-url-mapping"><?php _e('Add URL Mapping', 'specialty-rebrand'); ?></button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.getElementById('sr-add-url-mapping');
    const list = document.getElementById('sr-url-mapping-list');

    addBtn.addEventListener('click', function () {
        const count = list.querySelectorAll('.sr-url-mapping-row').length;
        const wrapper = document.createElement('div');
        wrapper.classList.add('sr-url-mapping-row');

        wrapper.innerHTML = `
            <input type="url" name="specialty_url_mappings[${count}][url]" placeholder="https://example.com" style="width: 45%">
            <input type="text" name="specialty_url_mappings[${count}][label]" placeholder="Label" style="width: 45%">
            <button type="button" class="sr-remove-mapping">×</button>
        `;

        list.appendChild(wrapper);
    });

    list.addEventListener('click', function (e) {
        if (e.target.classList.contains('sr-remove-mapping')) {
            e.target.parentElement.remove();
        }
    });
});
</script>

<style>
.sr-url-mapping-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
    align-items: center;
}
.sr-remove-mapping {
    background: #e74c3c;
    color: white;
    border: none;
    font-size: 16px;
    line-height: 1;
    padding: 4px 8px;
    cursor: pointer;
}
#sr-add-url-mapping {
    margin-top: 10px;
    background: #0073aa;
    color: white;
    border: none;
    padding: 6px 12px;
    cursor: pointer;
}
</style>