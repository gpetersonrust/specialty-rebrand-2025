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