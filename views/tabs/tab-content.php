<?php $i = 0; foreach ($tabs as $slug => $tab): ?>
    <div id="tab-<?php echo esc_attr($slug); ?>" class="specialty-tab-content <?php echo $i === 0 ? 'active' : ''; ?>">
        <?php foreach ($tab['fields'] as $field): ?>
            <h3><?php echo esc_html($field['label'] ?? ''); ?></h3>
            <?php Specialty_Rebrand_Field_Manager::render_field($field); ?>
        <?php endforeach; ?>
    </div>
<?php $i++; endforeach; ?>