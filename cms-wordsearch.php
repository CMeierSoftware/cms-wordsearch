<?php

declare(strict_types=1);

/**
 * @version 1.0.4
 */

/*
 * Plugin Name: Wordsearch
 * Plugin URI: https://github.com/CMeierSoftware/cms-wordsearch.git
 * Description: A Wordsearch, word find, word seek, word sleuth or mystery word puzzle is a word game that consists of the letters of words placed in a grid, which usually has a rectangular or square shape.
 * Author: CMeier Software
 * Version: 1.0.4
 * Author URI: https://cmeier-software.com/
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Text Domain: cms-wordsearch
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('CMSWS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CMSWS_PLUGIN_URL', plugins_url('', __FILE__));
define('CMSWS_VERSION', '1.0.4');

require_once CMSWS_PLUGIN_DIR . '/classes/settings.php';

require_once CMSWS_PLUGIN_DIR . '/classes/post-type.php';

register_activation_hook(__FILE__, 'cmsws_activate_plugin');
register_deactivation_hook(__FILE__, 'cmsws_deactivate_plugin');

add_action('init', [CmswsPostType::class, 'register']);
add_action('add_meta_boxes', [CmswsPostType::class, 'add_meta_boxes'], 1);
add_action('admin_enqueue_scripts', [CmswsPostType::class, 'enqueue_admin_scripts']);
add_action('wp_enqueue_scripts', [CmswsPostType::class, 'enqueue_front_scripts']);
add_action('save_post', [CmswsPostType::class, 'save_post_meta']);

add_shortcode(CmswsPostType::SHORTCODE, [CmswsPostType::class, 'do_shortcode']);

add_action('admin_init', [CmswsSettings::class, 'register']);

add_action('admin_menu', 'cmsws_create_admin_menu');
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'cmsws_plugin_action_links');


function cmsws_activate_plugin() {}

function cmsws_deactivate_plugin() {}

function cmsws_create_admin_menu()
{
    add_submenu_page(
        'edit.php?post_type=' . CmswsPostType::POST_TYPE,
        __('Global Wordsearch Options', 'cms-wordsearch'),
        __('Global Settings', 'cms-wordsearch'),
        'edit_published_posts',
        'cms-wordsearch-settings',
        [CmswsSettings::class, 'display_page']
    );
}

/**
 * Get other templates (e.g. my account) passing attributes and including the file.
 *
 * @param string $template_name template Name
 * @param string $template_path path of template provided (default: '')
 * @param array $args extra arguments(default: array())
 */
function cmsws_get_template(string $template_name, string $template_path, array $args = [])
{
    if (!empty($args)) {
        extract($args);
    }

    $located = CMSWS_PLUGIN_DIR . $template_path . $template_name;

    if (!file_exists($located)) {
        echo $located;
        _doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', esc_html($located)), '1.0');

        return;
    }

    include $located;
}

function cmsws_plugin_action_links(array $links): array
{
    $url = admin_url('edit.php?post_type='.CmswsPostType::POST_TYPE.'&page=cms-wordsearch-settings');
    $settings_link = '<a href="'.esc_url($url).'">'. __('Settings', 'cms-wordsearch') .'</a>';
    array_unshift($links, $settings_link);
    return $links;
}