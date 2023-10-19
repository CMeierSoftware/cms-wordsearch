<?php

// if uninstall.php is not called by WordPress, die

if (!defined('WP_UNINSTALL_PLUGIN'))
    exit;

$prefix = 'cmsws_';

global $wpdb;
$options = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '{$prefix}%'");

foreach ($options as $option) {
    delete_option($option->option_name);
}

require_once CMSWS_PLUGIN_DIR . '/classes/post_type.php';
// Get all posts of the custom post type
$posts = get_posts(
    array(
        'post_type' => Cmsws_Post_Type::POST_TYPE,
        'numberposts' => -1,
        // Retrieve all posts
    )
);

if (!empty($posts)) {
    foreach ($posts as $post) {
        // Delete the post
        wp_delete_post($post->ID, true); // Set the second argument to true to bypass the trash
    }
}