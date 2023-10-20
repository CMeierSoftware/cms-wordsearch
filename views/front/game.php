<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!empty($instructions)) {
    ?>
    <p class="instructions"><?php echo wp_kses($instructions, 'post'); ?></p>
    <?php
}
?>

<div class="cmsws_container <?php echo esc_attr('cmsws-pos-' . $word_list_position); ?>"
        id="cmsws_container_<?php echo esc_attr($post_id); ?>">

    <!-- Modal -->
    <div class="cmsws-modal" id="cmws-modal" style="display:none;">
        <div class="cmsws-modal-overlay"></div>
        <div class="cmsws-modal-wrap">
            <div class="cmsws-modal-header">
                <div class="cmsws-btn-close" id="cmsws-closemodale"><span class="dashicons dashicons-no"></span></div>
            </div>
            <div class="cmsws-modal-body">
                <?php echo wp_kses($congrats, 'post'); ?>
            </div>
            <div class="cmsws-modal-footer">
                <button id="btn-new-game"><?php esc_html_e('New Game', 'cms-wordsearch');?></button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="cmsws_custom_words_<?php echo esc_attr($post_id); ?>" value="<?php echo esc_html($custom_words); ?>">
<input type="hidden" id="cmsws_allowed_chars_<?php echo esc_attr($post_id); ?>" value="<?php echo esc_html($allowed_chars); ?>">
<input type="hidden" id="cmsws_directions_<?php echo esc_attr($post_id); ?>" value="<?php echo esc_html($directions); ?>">
<input type="hidden" id="cmsws_field_size_<?php echo esc_attr($post_id); ?>" value="<?php echo esc_html($size); ?>">
