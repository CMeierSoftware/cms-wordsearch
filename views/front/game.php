<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<h2>Hallo</h2>
<?php
    if (!empty($instructions)) {
        ?>
        <p class="instructions"><?php echo wp_kses($instructions, 'post'); ?></p>
        <?php
    }
?>

<div class="cms-wordsearch" id="cms_wordsearch_container"></div>

<!-- Modal -->
<div class="cmsws-modal" id="" style="display:none;">
    <div class="cmsws-modal-overlay"></div>
    <div class="cmsws-modal-wrap">
        <div class="cmsws-modal-header">
            <a href="#" class="cmsws-btn-close cmsws-closemodale">&times;</a>
        </div>
        <div class="cmsws-modal-body">
            <?php echo wp_kses($congrats, 'post'); ?>
        </div>
    </div>
</div>

<input type="hidden" id="cmsws_custom_words" value="<?php echo esc_html($custom_words); ?>">
<input type="hidden" id="cmsws_allowed_chars" value="<?php echo esc_html($allowed_chars); ?>">
<input type="hidden" id="cmsws_directions" value="<?php echo esc_html($directions); ?>">
<input type="hidden" id="cmsws_field_size" value="<?php echo esc_html($size); ?>">
<input type="hidden" id="cmsws_field_word_list_position" value="<?php echo esc_html($word_list_position); ?>">
