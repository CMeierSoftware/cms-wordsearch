<?php 
declare(strict_types=1);

class CmswsPostType
{
    public const POST_TYPE = 'cms_wordsearch';
    public const SHORTCODE = 'wordsearch-game';
    private const WORD_SEPARATOR = ',';

    public static function enqueue_admin_scripts(string $hook_suffix): void
    {
        global $post_type;
        if (self::POST_TYPE === $post_type && ('post.php' === $hook_suffix || 'post-new.php' === $hook_suffix)) {
            wp_enqueue_script('cmsws-edit-post-script', CMSWS_PLUGIN_URL . '/assets/js/admin/edit-post.js', ['jquery'], CMSWS_VERSION, true);

            $args = [
                'text_word_already_in_list' => __('Word is already in the list.', 'cms-wordsearch'),
                'text_title_required' => __('A title is required', 'cms-wordsearch'),
                'text_word_length' => __('The word is too long for this size of wordsearch.', 'cms-wordsearch'),
                'text_word_contains_forbidden_char' => __('The word contains forbidden character.', 'cms-wordsearch'),
                'word_separator' => self::WORD_SEPARATOR,
            ];

            wp_localize_script('cmsws-edit-post-script', 'cmsws_admin_args', $args);
        }
    }

    public static function enqueue_front_scripts()
    {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, self::SHORTCODE)) {
            $minify = current_user_can('administrator') ? '' : '.min';
            wp_enqueue_script('cmsws-wordsearch-script', CMSWS_PLUGIN_URL . "/assets/js/front/wordsearch{$minify}.js", ['jquery'], CMSWS_VERSION, true);
            wp_enqueue_style('cms-wordsearch-style', CMSWS_PLUGIN_URL . "/assets/css/front/wordsearch{$minify}.css", [], CMSWS_VERSION);

            $args = [
                'word_separator' => self::WORD_SEPARATOR,
            ];

            wp_localize_script('cmsws-wordsearch-script', 'cmsws_front_args', $args);
        }
    }

    public static function register(): WP_Error|WP_Post_Type
    {
        $labels = [
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
        ];

        $args = [
            'labels' => $labels,
            'description' => __('Holds the wordsearch and their data.', 'cms-wordsearch'),
            'menu_position' => 5,
            'menu_icon' => 'dashicons-games',
            'public' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => ['title', 'author'],
        ];

        return register_post_type(self::POST_TYPE, $args);
    }

    public static function add_meta_boxes(): void
    {
        if (current_user_can('edit_posts')) {
            add_meta_box(
                'cmsws_post_words',
                __('Words', 'cms-wordsearch'),
                [self::class, 'display_meta_box_words'],
                self::POST_TYPE,
                'normal',
                'core'
            );

            add_meta_box(
                'cmsws_post_settings',
                __('Settings', 'cms-wordsearch'),
                [self::class, 'display_meta_box_settings'],
                self::POST_TYPE,
                'normal',
                'core'
            );
            add_meta_box(
                'cmsws_post_shortcode',
                __('Shortcode', 'cms-wordsearch'),
                [self::class, 'display_meta_box_shortcode'],
                self::POST_TYPE,
                'side',
                'high'
            );
        }
    }

    public static function display_meta_box_words(WP_Post $post, array $args): void
    {
        // /todo: add check for length and allowed words
        $custom_words = get_post_meta($post->ID, 'cmsws_post_word_collection', true);
        if (!is_array($custom_words)) {
            $custom_words = [];
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

    public static function display_meta_box_settings(WP_Post $post, array $args): void
    {
        $word_size = (empty($customSize = get_post_meta($post->ID, 'cmsws_size', true))) ? CmswsSettings::get_game_size() : (int) $customSize;
        $possible_sizes = array_combine(range(5, 25), range(5, 25));

        $show_instructions = wp_validate_boolean(get_post_meta($post->ID, 'cmsws_show_instructions', true));

        $word_position = (empty($custom_pos = get_post_meta($post->ID, 'cmsws_word_position', true))) ? 'bottom' : $custom_pos;
        $possible_positions = [
            'top' => __('Top', 'cms-wordsearch'),
            'right' => __('Right', 'cms-wordsearch'),
            'bottom' => __('Bottom', 'cms-wordsearch'),
            'left' => __('Left', 'cms-wordsearch'),
        ];

        $overwrite_global_settings = wp_validate_boolean(get_post_meta($post->ID, 'cmsws_overwrite_global_settings', true));
        $word_direction = (empty($tmp = get_post_meta($post->ID, 'cmsws_direction', true))) ? CmswsSettings::get_allowed_directions() : $tmp;
        $allowed_chars = (empty($tmp = get_post_meta($post->ID, 'cmsws_character', true))) ? CmswsSettings::get_allowed_chars() : $tmp;

        ?>
            <div style="width:33%; display: inline-block;">
                <h3><?php esc_html_e('Display Instructions', 'cms-wordsearch'); ?></h3>
                <p><?php esc_html_e('You can edit the instructions in the global settings.', 'cms-wordsearch'); ?></p>
                <input type="checkbox" name="cmsws_show_instructions" id="cmsws_show_instructions" <?php checked($show_instructions, 1); ?>>
                <label for="cmsws_show_instructions"><?php esc_html_e('Show instructions', 'cms-wordsearch'); ?></label>
            </div>
            <div style="width:33%; display: inline-block;">
                <h3><?php esc_html_e('Game Size', 'cms-wordsearch'); ?></h3>
                <p><?php esc_html_e('Choose the size of the square playing field.', 'cms-wordsearch'); ?></p>
                <?php cmsws_get_template('dropdown.php', 'views/admin/', ['value' => $word_size, 'name' => 'cmsws_size', 'values' => $possible_sizes]); ?>
                <p style="color:red;"><?php esc_html_e('A field larger than 10 could cause display problems on mobile devices.', 'cms-wordsearch'); ?></p>

            </div>
            <div style="width:33%; display: inline-block;">
                <h3><?php esc_html_e('Word Position', 'cms-wordsearch'); ?></h3>
                <p><?php esc_html_e('Where should the words appear?', 'cms-wordsearch'); ?></p>
                <?php cmsws_get_template('dropdown.php', 'views/admin/', ['value' => $word_position, 'name' => 'cmsws_word_position', 'values' => $possible_positions]); ?>
            </div>
            <hr>
            <div>
                <h3><?php esc_html_e('Use Global settings', 'cms-wordsearch'); ?></h3>
                <p><?php esc_html_e('If you change the global settings and this wordsearch does not overwrite the settings, this wordsearch will change due to the global changes.', 'cms-wordsearch'); ?></p>
                <input type="checkbox" name="cmsws_overwrite_global_settings" id="cmsws_overwrite_global_settings" <?php checked($overwrite_global_settings); ?>>
                <label for="cmsws_overwrite_global_settings"><?php esc_html_e('Overwrite global settings', 'cms-wordsearch'); ?></label>
                <div id="globalSettingsOverwrite">
                    <div style="width:33%; display: inline-block;">
                        <h4><?php esc_html_e('Direction', 'cms-wordsearch'); ?></h4>
                        <p><?php esc_html_e('Choose direction of the words', 'cms-wordsearch'); ?></p>
                        <?php cmsws_get_template('compass.php', 'views/admin/', ['value' => $word_direction, 'name' => 'cmsws_direction']); ?>
                    </div>
                    <div style="width:66%; display: inline-block;">
                        <h4><?php esc_html_e('Alphabet', 'cms-wordsearch'); ?></h4>
                        <p><?php esc_html_e('Select allowed Character', 'cms-wordsearch'); ?></p>
                        <input type="text" class='large-text' value='<?php echo esc_html($allowed_chars); ?>' name="cmsws_character">
                    </div>
                </div>
            </div>

            <style>
                #globalSettingsOverwrite {
                    display: none;
                }
                #cmsws_overwrite_global_settings:checked + label + #globalSettingsOverwrite {
                    display: block;
                }


            </style>
        <?php
    }

    public static function display_meta_box_shortcode(WP_Post $post, array $args): void
    {
        $shortcode = '[' . self::SHORTCODE . ' id="' . $post->ID . '"]';
        ?>
            <div>
                <span class="cmsws-shortcode cmsws-tooltip"><?php echo esc_html($shortcode); ?></span>
                <textarea class="js-copytextarea" style="visibility:hidden"><?php echo esc_html($shortcode); ?></textarea>
                <span class="cmsws-tooltip-copy"><?php esc_html_e('Copied!', 'cms-wordsearch'); ?></span>
            </div>
            <style>
                .cmsws-tooltip {
                    position: relative;
                    display: inline-block;
                    cursor: pointer;
                    border-bottom: 1px dotted black;
                }

                .cmsws-tooltip-copy {
                    visibility: hidden;
                    width: 120px;
                    background-color: #555;
                    color: #fff;
                    text-align: center;
                    padding: 5px 0;
                    border-radius: 6px;

                    /* Position the tooltip text */
                    position: absolute;
                    z-index: 1;
                    top: 25px;
                    left: 50%;
                    margin-left: -60px;

                    /* Fade in tooltip */
                    opacity: 0;
                    transition: opacity 0.3s;
                }

                /* Tooltip arrow */
                .cmsws-tooltip-copy::after {
                    content: "";
                    position: absolute;
                    top: -10px;
                    left: 50%;
                    margin-left: -5px;
                    border-width: 5px;
                    border-style: solid;
                    border-color: transparent transparent #555 transparent;
                }

            </style>
        <?php
    }

    public static function save_post_meta($post_id): void
    {
        $value = isset($_POST['cmsws_show_instructions']);
        update_post_meta($post_id, 'cmsws_show_instructions', $value ? 'true' : 'false');

        $value = isset($_POST['cmsws_overwrite_global_settings']);
        update_post_meta($post_id, 'cmsws_overwrite_global_settings', $value ? 'true' : 'false');

        if (isset($_POST['cmsws_word_position'])) {
            $position = sanitize_text_field($_POST['cmsws_word_position']);
            update_post_meta($post_id, 'cmsws_word_position', $position);
        }

        if (isset($_POST['cmsws_post_word_collection'])) {
            $words = explode(self::WORD_SEPARATOR, sanitize_text_field($_POST['cmsws_post_word_collection']));
            // Filter out empty and whitespace elements
            $words = array_filter($words, static fn ($word) => '' !== trim($word));
            update_post_meta($post_id, 'cmsws_post_word_collection', $words);
        }

        if (isset($_POST['cmsws_size'])) {
            $value = sanitize_text_field($_POST['cmsws_size']);
            update_post_meta($post_id, 'cmsws_size', $value);
        }
        if (isset($_POST['cmsws_overwrite_global_settings'])) {
            if (isset($_POST['cmsws_direction'])) {
                $value = $_POST['cmsws_direction'];
                update_post_meta($post_id, 'cmsws_direction', $value);
            }
            if (isset($_POST['cmsws_character'])) {
                $value = sanitize_text_field($_POST['cmsws_character']);
                update_post_meta($post_id, 'cmsws_character', $value);
            }
        }
    }

    public static function do_shortcode(array $args)
    {
        $post = get_post($args['id']);

        $settings['custom_words'] = implode(self::WORD_SEPARATOR, get_post_meta($post->ID, 'cmsws_post_word_collection', true));

        if (wp_validate_boolean(get_post_meta($post->ID, 'cmsws_overwrite_global_settings', true))) {
            $settings['allowed_chars'] = get_post_meta($post->ID, 'cmsws_character', true);
            $settings['directions'] = get_post_meta($post->ID, 'cmsws_direction', true);
        } else {
            $settings['allowed_chars'] = CmswsSettings::get_allowed_chars();
            $settings['directions'] = CmswsSettings::get_allowed_directions();
        }
        $settings['directions'] = implode(self::WORD_SEPARATOR, $settings['directions']);
        $settings['size'] = get_post_meta($post->ID, 'cmsws_size', true);
        $settings['word_list_position'] = get_post_meta($post->ID, 'cmsws_word_position', true);
        $settings['instructions'] = get_post_meta($post->ID, 'cmsws_show_instructions', true) ? CmswsSettings::get_instructions() : '';
        $settings['congrats'] = CmswsSettings::get_congrats();
        $settings['post_id'] = $post->ID;

        ob_start();
        cmsws_get_template('game.php', 'views/front/', $settings);

        return ob_get_clean();
    }
}
