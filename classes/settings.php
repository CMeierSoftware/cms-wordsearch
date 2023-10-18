<?php

class Cmsws_Settings {

    public static function register()
    {
        register_setting('cmsws_settings_group', 'cmsws_allowed_chars');
        register_setting('cmsws_settings_group', 'cmsws_allowed_directions');
        register_setting('cmsws_settings_group', 'cmsws_game_size');
        register_setting('cmsws_settings_group', 'cmsws_instructions');
        register_setting('cmsws_settings_group', 'cmsws_congrats');

        add_settings_section(
            'cmsws_game_settings',
            __('Global Game Settings', 'cms-wordsearch'),
            function(){echo sprintf(
                    '<p>%s</p>',
                    esc_html__( "Define the behavior when generating new wordsearchs.", 'cms-wordsearch' )
                );
            },
            'cmsws_settings_group'
        );

        add_settings_field(
            'cmsws_allowed_chars',
            __('Allowed Characters', 'cms-wordsearch'),
            array(self::class, 'display_field_allowed_character'),
            'cmsws_settings_group',
            'cmsws_game_settings',
            array(
                'label_for' => 'cmsws_allowed_chars',
                'class' => '',
            )
        );

        add_settings_field(
            'cmsws_allowed_directions',
            __('Allowed Directions', 'cms-wordsearch'),
            array(self::class, 'display_field_allowed_directions'),
            'cmsws_settings_group',
            'cmsws_game_settings',
            array(
                'label_for' => 'cmsws_allowed_directions',
                'class' => '',
            )
        );

        add_settings_field(
            'cmsws_game_size',
            __('Game size', 'cms-wordsearch'),
            array(self::class, 'display_field_dropdown'),
            'cmsws_settings_group',
            'cmsws_game_settings',
            array(
                'label_for' => 'cmsws_game_size',
                'class' => '',
                'values' => range(5, 25),
                'default' => 10
            )
        );

        add_settings_section(
            'cmsws_messages',
            __('Messages', 'cms-wordsearch'),
            function () {
                echo sprintf(
                    '<p>%s</p>',
                    esc_html__('Create Messages to improve the gameplay.', 'cms-wordsearch')
                );
            },
            'cmsws_settings_group'
        );

        add_settings_field(
            'cmsws_instructions',
            __('Tutorial (How to play)', 'cms-wordsearch'),
            array(self::class, 'display_field_tinymce'),
            'cmsws_settings_group',
            'cmsws_messages',
            array(
                'label_for' => 'cmsws_instructions',
                'class' => '',
            )
        );

        add_settings_field(
            'cmsws_congrats',
            __('Congratulations', 'cms-wordsearch'),
            array(self::class, 'display_field_tinymce'),
            'cmsws_settings_group',
            'cmsws_messages',
            array(
                'label_for' => 'cmsws_congrats',
                'class' => '',
            )
        );
    }

    public static function display_field_allowed_character(array $args)
    {
        $value = get_option($args['label_for'], implode('', array_merge(range('A', 'Z'), range('a', 'z'))));
        ?>
            <input type="text"
                class='large-text'
                value='<?php echo esc_html($value) ?>'
                name="<?php echo esc_html($args['label_for']) ?>">
            <p class="description">
                <?php esc_html_e( 'URL to the Prestashop License API', 'WebCabinet' ); ?>
            </p>
        <?php
    }

    public static function display_field_tinymce(array $args)
    {
        echo wp_editor(
            get_option($args['label_for'], ''),
            $args['label_for'],
            array(
                'wpautop' => true,
                'tinymce' => true
            )
        );
    }

    public static function display_field_allowed_directions(array $args)
    {
        $value = get_option($args['label_for'], array('south','west'));

        cmsws_get_template('compass.php', 'views/admin/', array('value' => $value, 'name' => $args['label_for']));
    }

    public static function display_field_dropdown(array $args)
    {
        $value = (int) get_option($args['label_for'], $args['default']);
        $dd_options = array();
        foreach ($args['values'] as $option) {
            $dd_options[] = sprintf(
                '<option %s value="%d">%d</option>',
                ($value === $option ? "selected" : ""),
                $option,
                $option
            );
        }
        ?>
            <select name="<?php echo esc_html($args['label_for']) ?>">
                <?php echo implode('', $dd_options);?>
            </select>
        <?php
    }

    public static function display_page()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // WordPress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {
            // add settings saved message with the class of "updated"
            add_settings_error('cmsws_messages', 'cmsws_message', __('Settings Saved', 'cms-wordsearch'), 'updated');
        }
        // show error/update messages
        settings_errors('cmsws_messages');
        ?>

            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                    <form action="options.php" method="post">
                    <?php
                        do_settings_sections('cmsws_settings_group');
                        settings_fields('cmsws_settings_group');
                        submit_button(__('Save Settings', 'cms-wordsearch'));
                    ?>
                </form>
            </div>
        <?php
    }
}