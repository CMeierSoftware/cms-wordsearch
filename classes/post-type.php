<?php

class Cmsws_Post_Type
{
    public const POST_TYPE = 'cms_wordsearch';
    public static function enqueue_admin_scripts($hook_suffix)
    {
        //wp_enqueue_style('wha-wordsearch-style-admin', plugins_url('res/admin/wordsearch-admin.css', __FILE__));
        wp_enqueue_script('cmsws-edit-post-script', CMSWS_PLUGIN_URL . '/assets/js/admin/edit-post.js', array(), false, true);
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
        $wordsearch = get_post_meta($post->ID, $args['id'], true);
        $items = json_decode($wordsearch, true);
    }

    public static function display_meta_box_settings($post, $args)
    {
        $size = (int)get_post_meta($post->ID, 'cmsws_size');
        $size_options = array();
        for ($i=5; $i < 25; $i++) {
            $size_options[] = sprintf(
                '<option %s value="%d">%d*%d</option>',
                ($size === $i ? "selected" : ""), $i, $i, $i
            );
        }

        $show_instructions = (int) get_post_meta($post->ID, 'cmsws_show_instructions');

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
                    <?php esc_html_e('Size', 'cms-wordsearch');?>
                </h3>
                <p>
                    <?php esc_html_e('Choose which size to display', 'cms-wordsearch'); ?>
                </p>
            </div>
        <?php
    }

    public static function display_meta_box_shortcode($post, $args)
    {
        $shortcode = '[game-wordsearch id="' . $post->ID . '" ]';
        ?>
            <div style="cursor: pointer">
                <span class="cmsws-shortcode"><?php echo esc_html($shortcode); ?></span>
                <textarea class="js-copytextarea" style="visibility:hidden"><?php echo esc_html($shortcode); ?></textarea>
                <span class="cmsws-tooltip-copy" style="visibility:hidden"><?php esc_html_e('Copied!', 'cms-wordsearch');?></span>
            </div>
        <?php
    }
}