<?php

class Cmsws_Settings
{

    public static function register(): void
    {
        register_setting('cmsws_settings_group', 'cmsws_allowed_chars');
        register_setting('cmsws_settings_group', 'cmsws_allowed_directions');
        register_setting('cmsws_settings_group', 'cmsws_game_size');
        register_setting('cmsws_settings_group', 'cmsws_instructions');
        register_setting('cmsws_settings_group', 'cmsws_congrats');

        add_settings_section(
            'cmsws_game_settings',
            __('Global Game Settings', 'cms-wordsearch'),
            function () {
                echo sprintf(
                    '<p>%s</p>',
                    esc_html__("Define the behavior when generating new wordsearchs.", 'cms-wordsearch')
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
                'value' => self::get_allowed_chars(),
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
                'value' => self::get_allowed_directions(),
                'class' => '',
            )
        );

        add_settings_field(
            'cmsws_game_size',
            __('Default Game size', 'cms-wordsearch'),
            array(self::class, 'display_field_dropdown'),
            'cmsws_settings_group',
            'cmsws_game_settings',
            array(
                'label_for' => 'cmsws_game_size',
                'value' => self::get_game_size(),
                'values' => array_combine(range(5, 25), range(5, 25)),
                'class' => '',
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
                'value'=> self::get_instructions(),
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
                'value'=> self::get_congrats(),
                'class' => '',
            )
        );
    }

    public static function display_field_allowed_character(array $args): void
    {
        ?>
            <input type="text" class='large-text' value='<?php echo esc_html($args['value']) ?>'
                name="<?php echo esc_html($args['label_for']) ?>">
            <p class="description">
                <?php esc_html_e('Select allowed Characters', 'cms-wordsearch'); ?>
            </p>
        <?php
    }

    public static function display_field_tinymce(array $args): void
    {
        echo wp_editor(
            $args['value'],
            $args['label_for'],
            array(
                'wpautop' => true,
                'tinymce' => true
            )
        );
    }

    public static function display_field_allowed_directions(array $args): void
    {
        $args = array('value' => $args['value'], 'name' => $args['label_for']);

        cmsws_get_template('compass.php', 'views/admin/', $args);
    }

    public static function display_field_dropdown(array $args): void
    {
        $args = array('value' => $args['value'], 'name' => $args['label_for'], 'values' => $args['values']);

        cmsws_get_template('dropdown.php', 'views/admin/', $args);
    }

    public static function display_page(): void
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
            <h1>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>
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

    public static function get_allowed_chars(): string
    {
        return get_option('cmsws_allowed_chars', implode(array_merge(range('A', 'Z'), range('a', 'z'))));
    }

    public static function get_allowed_directions(): array
    {
        return get_option('cmsws_allowed_directions', array('north', 'northwest', 'south', 'southeast', 'east', 'northeast'));
    }

    public static function get_game_size(): int
    {
        return get_option('cmsws_game_size', 10);
    }

    public static function get_instructions(): string
    {
        return get_option('cmsws_instructions', __('Find the words in the puzzle.', 'cws-wordsearch'));
    }

    public static function get_congrats(): string
    {
        return get_option('cmsws_congrats', __('Congratulations! You have completed the puzzle.', 'cws-wordsearch'));
    }

}