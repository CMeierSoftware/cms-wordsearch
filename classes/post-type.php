<?php

class Cmsws_Post_Type
{
    public const POST_TYPE = 'cms_wordsearch';
    public const SHORTCODE = 'wordsearch_game';
    private const WORD_SEPERATOR = ',';
    public static function enqueue_admin_scripts($hook_suffix)
    {
        global $post_type, $pagenow;

        // Check if we are on the edit screen of your custom post type
        if ($post_type === self::POST_TYPE && ($pagenow === 'post.php' ||$pagenow === 'post-new.php')) {
            //wp_enqueue_style('wha-wordsearch-style-admin', plugins_url('res/admin/wordsearch-admin.css', __FILE__));
            wp_enqueue_script('cmsws-edit-post-script', CMSWS_PLUGIN_URL . '/assets/js/admin/edit-post.js', array('jquery'), false, true);

            $args = array(
                'text_word_already_in_list' => __('Word is already in the list.','cms-wordsearch'),
                'text_title_required' => __('A title is required', 'cms-wordsearch'),
                'text_word_contains_forbidden_char' => __('The word contains forbidden character.', 'cms-wordsearch'),
                'word_seperator'=> self::WORD_SEPERATOR,
            );

            wp_localize_script('cmsws-edit-post-script', 'cmsws_admin_args', $args);
        }
    }

    public static function register()
    {
        $labels = array(
            'name' => __('Wordsearch', 'cms-wordsearch'),
            'menu_name' => __('My Wordsearch', 'cms-wordsearch'),
            'singular_name' => __('Wordsearch', 'cms-wordsearch'),
            'name_admin_bar' => _x('Wordsearch', 'name admin bar', 'cms-wordsearch'),
            'all_items' => __('All  wordsearch', 'cms-wordsearch'),
            'search_items' => __('Search  wordsearch', 'cms-wordsearch'),
            'add_new' => _x('Add New', 'cms-wordsearch', 'cms-wordsearch'),
            'add_new_item' => __('Add New wordsearch', 'cms-wordsearch'),
            'new_item' => __('New  wordsearch', 'cms-wordsearch'),
            'view_item' => __('View  wordsearch', 'cms-wordsearch'),
            'edit_item' => __('Edit  wordsearch', 'cms-wordsearch'),
            'not_found' => __('No  wordsearch Found.', 'cms-wordsearch'),
            'not_found_in_trash' => __('Wordsearch not found in Trash.', 'cms-wordsearch'),
            'parent_item_colon' => __('Parent wordsearch', 'cms-wordsearch'),
        );

        $args = array(
            'labels' => $labels,
            'description' => __('Holds the wordsearch and their data.', 'cms-wordsearch'),
            'menu_position' => 5,
            'menu_icon' => 'dashicons-editor-help',
            'public' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => array('title', 'author'),
        );

        register_post_type(self::POST_TYPE, $args);
    }

    public static function add_meta_boxes()
    {
        if (current_user_can('edit_posts')) {
            add_meta_box(
                'cmsws_post_words',
                __('Words', 'cms-wordsearch'),
                array(self::class, 'display_meta_box_words'),
                array(self::POST_TYPE),
                'normal',
                'core'
            );

            add_meta_box(
                'cmsws_post_settings',
                __('Settings', 'cms-wordsearch'),
                array(self::class, 'display_meta_box_settings'),
                array(self::POST_TYPE),
                'normal',
                'core'
            );
            add_meta_box(
                'cmsws_post_shortcode',
                __('Shortcode', 'cms-wordsearch'),
                array(self::class, 'display_meta_box_shortcode'),
                array(self::POST_TYPE),
                'side',
                'high'
            );
        }
    }

    public static function display_meta_box_words($post, $args)
    {
        ///todo: add check for length and allowed words
        $custom_words = get_post_meta($post->ID, 'cmsws_post_word_collection', true);
        if (!is_array($custom_words)) {
            $custom_words = array();
        }
        ?>
            <label for="cmsws_post_word"><?php esc_html_e('Enter Words:', 'cms-wordsearch'); ?></label>
            <input type="text" id="cmsws_post_word" name="cmsws_post_word">
            <button type="button" id="add_word"><?php esc_html_e('Add Word', 'cms-wordsearch'); ?></button>
            <p><?php esc_html_e('Included words:', 'cms-wordsearch'); ?></p>
            <ol id="entered_words"></ol>
            <input type="hidden" name="cmsws_post_word_collection" id="cmsws_post_word_collection" value="<?php echo esc_attr(implode(',', $custom_words)); ?>">
        <?php
    }

    public static function display_meta_box_settings($post, $args)
    {
        $size = (empty($customSize = get_post_meta($post->ID, 'cmsws_size', true))) ? (int) get_option('cmsws_default_size') : (int) $customSize;
        $size_options = array();
        for ($i=5; $i < 25; $i++) {
            $size_options[] = sprintf(
                '<option %s value="%d">%d*%d</option>',
                selected($size, $i, false), esc_attr($i), esc_html($i), esc_html($i)
            );
        }

        $show_instructions = (int) get_post_meta($post->ID, 'cmsws_show_instructions');

        $word_position = (empty($custom_pos = get_post_meta($post->ID, 'cmsws_word_position', true))) ? 'bottom' : $custom_pos;
        $positions = [
            'top' => __('Top', 'cms-wordsearch'),
            'right' => __('Right', 'cms-wordsearch'),
            'bottom' => __('Bottom', 'cms-wordsearch'),
            'left' => __('Left', 'cms-wordsearch')];
        foreach ($positions as $pos => $label) {
            $position_options[] = sprintf(
                '<label style="padding:0 10px;"><input type="radio" name="cmsws_word_position" %s value="%s">%s</label>',
                checked($word_position, $pos, false), esc_attr($pos), esc_html($label)
            );
        }

        ?>
            <div style="width:33%; display: inline-block;">
                <h3>
                    <?php esc_html_e('Size', 'cms-wordsearch');?>
                </h3>
                <p>
                    <?php esc_html_e('Choose which size to display', 'cms-wordsearch'); ?>
                </p>
                <select name="cmsws_size">
                    <?php echo implode('', $size_options);?>
                </select>
            </div>
            <div style="width:33%; display: inline-block;">
                <h3>
                    <?php esc_html_e('Display Instructions', 'cms-wordsearch');?>
                </h3>
                <p>
                    <?php esc_html_e('You can edit the instructions in the global settings.', 'cms-wordsearch'); ?>
                </p>
                <input type="checkbox" name="cmsws_show_instructions" id="cmsws_show_instructions" value="1" <?php checked($show_instructions, 1); ?>>
                <label for="cmsws_show_instructions"><?php esc_html_e('Show instructions', 'cms-wordsearch') ?></label>
            </div>
            <div style="width:33%; display: inline-block;">
                <h3>
                    <?php esc_html_e('Word Position', 'cms-wordsearch');?>
                </h3>
                <p>
                    <?php esc_html_e('Where should the words appear?', 'cms-wordsearch'); ?>
                </p>
                <?php echo implode('', $position_options); ?>
            </div>
        <?php
    }

    public static function display_meta_box_shortcode($post, $args)
    {
        $shortcode = '['.self::SHORTCODE.' id="' . $post->ID . '" ]';
        ?>
            <div style="cursor: pointer">
                <span class="cmsws-shortcode"><?php echo esc_html($shortcode); ?></span>
                <textarea class="js-copytextarea" style="visibility:hidden"><?php echo esc_html($shortcode); ?></textarea>
                <span class="cmsws-tooltip-copy" style="visibility:hidden"><?php esc_html_e('Copied!', 'cms-wordsearch');?></span>
            </div>
        <?php
    }

    public static function save_post_meta($post_id)
    {
        if (isset($_POST['cmsws_size'])) {
            $size = sanitize_text_field($_POST['cmsws_size']);
            update_post_meta($post_id, 'cmsws_size', $size);
        }
        if (isset($_POST['cmsws_word_position'])) {
            $position = sanitize_text_field($_POST['cmsws_word_position']);
            update_post_meta($post_id, 'cmsws_word_position', $position);
        }
        if (isset($_POST['cmsws_post_word_collection'])) {
            $words = explode(self::WORD_SEPERATOR, sanitize_text_field($_POST['cmsws_post_word_collection']));
            // Filter out empty and whitespace elements
            $words = array_filter($words, fn($word) => trim($word) !== '');
            update_post_meta($post_id, 'cmsws_post_word_collection', $words);
        }
        if (isset($_POST['cmsws_show_instructions'])) {
            $value = sanitize_text_field($_POST['cmsws_show_instructions']);
            update_post_meta($post_id, 'cmsws_show_instructions', $value);
        }
    }

}