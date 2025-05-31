<input type="checkbox" 
    name="<?php echo esc_attr($field['name']); ?>"
    id="<?php echo esc_attr($field['id']); ?>"
    value="1"
    <?php checked($field['value'] ?? false, true); ?>>
<label for="<?php echo esc_attr($field['id']); ?>">
    <?php echo esc_html($field['label']); ?>
</label>
