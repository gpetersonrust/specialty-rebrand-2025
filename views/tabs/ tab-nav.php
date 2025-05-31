<ul class="specialty-tab-nav">
    <?php $i = 0; foreach ($tabs as $slug => $tab): ?>
        <li>
            <a href="#tab-<?php echo esc_attr($slug); ?>" class="tab-link <?php echo $i === 0 ? 'active' : ''; ?>">
                <?php echo esc_html($tab['label']); ?>
            </a>
        </li>
    <?php $i++; endforeach; ?>
</ul>