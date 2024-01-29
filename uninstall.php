<?php

declare(strict_types=1);

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$prefix = 'cmsws_';

global $wpdb;
$options = $wpdb->get_results("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '{$prefix}%'");

foreach ($options as $option) {
    delete_option($option->option_name);
}

require_once CMSWS_PLUGIN_DIR . '/classes/post_type.php';
$posts = get_posts(
    [
        'post_type' => CmswsPostType::POST_TYPE,
        'numberposts' => -1,
    ]
);

if (!empty($posts)) {
    foreach ($posts as $post) {
        wp_delete_post($post->ID, true); // Set the second argument to true to bypass the trash
    }
}
