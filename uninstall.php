<?php
/**
 * Uninstall script for Fifty Shades of Admin
 * 
 * This file is executed when the plugin is deleted from WordPress admin.
 * It cleans up all plugin data from the database.
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all user meta data created by the plugin
global $wpdb;

// Get all user IDs that have our custom color meta
$user_ids = $wpdb->get_col(
    "SELECT user_id FROM {$wpdb->usermeta} 
     WHERE meta_key = 'fifty_shades_base_color' 
     OR meta_key = 'admin_color' AND meta_value = 'fifty_shades_custom'"
);

// Remove the custom color meta from all users
foreach ($user_ids as $user_id) {
    delete_user_meta($user_id, 'fifty_shades_base_color');
    
    // Reset admin color to default if it was set to our custom scheme
    $admin_color = get_user_meta($user_id, 'admin_color', true);
    if ($admin_color === 'fifty_shades_custom') {
        update_user_meta($user_id, 'admin_color', 'fresh');
    }
}

// Clear any transients or options if any
delete_transient('fifty_shades_color_cache');

// Log the cleanup (optional - for debugging)
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Fifty Shades of Admin: Plugin data cleaned up successfully');
}
