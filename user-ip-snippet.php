<?php
/**
 * Plugin Name: Show User IP in Admin Users Grid
 * Description: Stores registration IP and last login IP, then displays them in the WordPress Users admin table.
 */

if (!defined('ABSPATH')) exit;

/**
 * Get visitor IP.
 * Supports Cloudflare using CF-Connecting-IP.
 */
function zw_get_user_real_ip() {
    $ip = '';

    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

/**
 * Save registration IP.
 */
add_action('user_register', function ($user_id) {
    $ip = zw_get_user_real_ip();

    if ($ip) {
        update_user_meta($user_id, 'registration_ip', $ip);
    }
});

/**
 * Save last login IP.
 */
add_action('wp_login', function ($user_login, $user) {
    $ip = zw_get_user_real_ip();

    if ($ip && !empty($user->ID)) {
        update_user_meta($user->ID, 'last_login_ip', $ip);
        update_user_meta($user->ID, 'last_login_time', current_time('mysql'));
    }
}, 10, 2);

/**
 * Add columns to Users grid.
 */
add_filter('manage_users_columns', function ($columns) {
    $columns['registration_ip'] = 'Registration IP';
    $columns['last_login_ip'] = 'Last Login IP';
    $columns['last_login_time'] = 'Last Login';
    return $columns;
});

/**
 * Display column data.
 */
add_filter('manage_users_custom_column', function ($value, $column_name, $user_id) {
    if ($column_name === 'registration_ip') {
        return esc_html(get_user_meta($user_id, 'registration_ip', true) ?: '-');
    }

    if ($column_name === 'last_login_ip') {
        return esc_html(get_user_meta($user_id, 'last_login_ip', true) ?: '-');
    }

    if ($column_name === 'last_login_time') {
        return esc_html(get_user_meta($user_id, 'last_login_time', true) ?: '-');
    }

    return $value;
}, 10, 3);