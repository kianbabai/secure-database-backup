<?php
/*
Plugin Name: Secure Database Backup
Description: Secure database backup solution with access controls
Version: 1.0
Author: kian babaabady
Author URI: https://kianbabaabady.ir
*/

defined('ABSPATH') || exit;


add_action('admin_init', function () {
    register_setting('general', 'secure_backup_secret', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field'
    ]);

    add_settings_section(
        'secure_backup_section',
        'Secure Backup Settings',
        function () {
            echo '<p>Backup system configuration</p>';
        },
        'general'
    );

    add_settings_field(
        'secure_backup_url',
        'Backup URL',
        'secure_backup_url_field',
        'general',
        'secure_backup_section'
    );
});

// Add backup button to admin bar
add_action('admin_bar_menu', function ($admin_bar) {
    $secret = get_option('secure_backup_secret');
    $url = add_query_arg([
        'action' => 'backup',
        'secret' => $secret,
        '_wpnonce' => wp_create_nonce('secure_backup')
    ], home_url('/backup'));

    $admin_bar->add_menu([
        'id'    => 'secure_backup_button',
        'title' => 'Backup Now',
        'href'  => esc_url($url),
        'meta'  => [
            'title' => __('Backup Now'),
        ],
    ]);
}, 100);


// Add settings link to plugin actions
add_filter('plugin_action_links', function ($links, $plugin_file) {
    if (plugin_basename(__FILE__) === $plugin_file) {
        $settings_link = '<a href="' . admin_url('options-general.php#secure-database-backup-input') . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}, 10, 2);


// Generate backup URL field
function secure_backup_url_field()
{
    $secret = get_option('secure_backup_secret', '');
    $url = secure_backup_generate_url();
    echo '<input id="secure-database-backup-input" type="text" class="regular-text code" value="' . esc_url($url) . '" readonly>';
    echo '<p class="description">Bookmark this URL - it will not be displayed again</p>';
}

// Generate secure backup URL
function secure_backup_generate_url()
{
    $secret = get_option('secure_backup_secret');
    if (!$secret) {
        $secret = wp_generate_password(32, false);
        update_option('secure_backup_secret', $secret);
    }

    return add_query_arg([
        'action' => 'backup',
        'secret' => $secret,
        '_wpnonce' => wp_create_nonce('secure_backup')
    ], home_url('/backup'));
}

// Handle backup requests
add_action('init', function () {
    if (!isset($_GET['action']) || $_GET['action'] !== 'backup') return;

    // Validate request
    if (
        !wp_verify_nonce($_GET['_wpnonce'], 'secure_backup') ||
        $_GET['secret'] !== get_option('secure_backup_secret')
    ) {
        wp_die('Invalid request', 403);
    }

    // Load backup script
    require_once plugin_dir_path(__FILE__) . 'includes/backup-endpoint.php';
    exit;
});


// Show notice after backup generation
add_action('admin_notices', function () {
    if ($message = get_transient('secure_backup_notice')) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        delete_transient('secure_backup_notice');
    }
});
