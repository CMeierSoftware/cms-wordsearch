<?php

/**
 * @package CMS Wordsearch
 * @version 0.0.1
 */

/*
 * Plugin Name: CMS Wordsearch
 * Plugin URI:
 * Description:
 * Author: CMeier Software
 * Version: 0.0.1
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


require_once(CMSWS_PLUGIN_DIR . '/classes/settings.php');
require_once(CMSWS_PLUGIN_DIR . '/classes/post-type.php');

register_activation_hook(__FILE__, 'cmsws_activate_plugin');
register_deactivation_hook(__FILE__, 'cmsws_deactivate_plugin');

add_action('init', array(Cmsws_Post_Type::class, 'register'));
add_action('add_meta_boxes', array(Cmsws_Post_Type::class, 'add_meta_boxes'), 1);
add_action('admin_enqueue_scripts', array(Cmsws_Post_Type::class, 'enqueue_admin_scripts'));
add_action('save_post', array(Cmsws_Post_Type::class, 'save_post_meta'));

add_shortcode(Cmsws_Post_Type::SHORTCODE, array(Cmsws_Post_Type::class, 'do_shortcode'));

add_action('admin_init', array(Cmsws_Settings::class, 'register'));

add_action('admin_menu', 'cmsws_create_admin_menu');


function cmsws_activate_plugin()
{

}

function cmsws_deactivate_plugin()
{

}

function cmsws_create_admin_menu()
{
    add_submenu_page(
        'edit.php?post_type=' . Cmsws_Post_Type::POST_TYPE,
        __('Global Wordsearch Options', 'cms-wordsearch'),
        __('Global Settings', 'cms-wordsearch'),
        'edit_published_posts',
        'cms-wordsearch-settings',
        array(Cmsws_Settings::class, 'display_page')
    );
}

/**
 * Get other templates (e.g. my account) passing attributes and including the file.
 *
 * @param string $template_name Template Name.
 * @param array  $args Extra arguments(default: array()).
 * @param string $template_path Path of template provided (default: '').
 * @param string $default_path  Default path of template provided(default: '').
 */
function cmsws_get_template($template_name, $template_path, $args = array())
{
    if (!empty($args) && is_array($args)) {
        extract($args); // phpcs:ignore
    }

    $located = CMSWS_PLUGIN_DIR . $template_path . $template_name;

    if (!file_exists($located)) {
        echo ($located);
        _doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', esc_html($located)), '1.0');

        return;
    }

    include $located;
}