<?php
/**
 * Plugin Name: Fifty Shades of Admin
 * Plugin URI: https://iconick.io
 * Description: Turn your boring admin into a colorful masterpiece! Pick a color, we'll do the math. Warning: Side effects may include actually enjoying WordPress admin work.
 * Version: 1.0.0
 * Author: Iconick
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FiftyShadesOfAdmin {
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_color_scheme'));
        add_action('show_user_profile', array($this, 'add_custom_controls'));
        add_action('edit_user_profile', array($this, 'add_custom_controls'));
        add_action('personal_options_update', array($this, 'save_custom_color_scheme'));
        add_action('edit_user_profile_update', array($this, 'save_custom_color_scheme'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Handle AJAX color updates
        add_action('wp_ajax_fifty_shades_update_color', array($this, 'ajax_update_color'));
        
        // Use admin_head with high priority to override stubborn WordPress styles
        add_action('admin_head', array($this, 'force_color_overrides'), 999);
    }
    
    /**
     * Register our custom color scheme with WordPress
     */
    public function register_color_scheme() {
        // Get current user settings or use defaults
        $user_id = get_current_user_id();
        $base_color = get_user_meta($user_id, 'fifty_shades_base_color', true);
        
        if (empty($base_color)) {
            $base_color = '#0073aa';
        }
        
        $colors = $this->generate_color_scheme($base_color);
        
        // Register the color scheme with WordPress
        wp_admin_css_color(
            'fifty_shades_custom',
            'Fifty Shades',
            '', // No external CSS file - we handle it inline
            array($colors['dark'], $colors['secondary'], $colors['primary'], $colors['accent']),
            array(
                'base' => '#a7aaad',
                'focus' => $colors['primary'],
                'current' => '#fff'
            )
        );
    }
    
    /**
     * Add custom controls to profile page
     */
    public function add_custom_controls($user) {
        $base_color = get_user_meta($user->ID, 'fifty_shades_base_color', true);
        $current_scheme = get_user_meta($user->ID, 'admin_color', true);
        
        if (empty($base_color)) {
            $base_color = '#0073aa';
        }
        
        $show_controls = ($current_scheme === 'fifty_shades_custom') ? '' : 'display: none;';
        ?>
        
        <script>
        jQuery(document).ready(function($) {
            // Move our section to the very top of the page
            var firstTable = $('table.form-table').first();
            if (firstTable.length) {
                $('#fifty-shades-section').insertBefore(firstTable);
                $('#fifty-shades-section').show();
            }
            
            // Show/hide controls based on selected color scheme
            $('input[name="admin_color"]').change(function() {
                if ($(this).val() === 'fifty_shades_custom') {
                    $('#fifty-shades-controls').show();
                } else {
                    $('#fifty-shades-controls').hide();
                }
            });
        });
            
            // Handle the Apply button click
            $('#fifty-shades-apply').click(function(e) {
                e.preventDefault();
                
                var selectedColor = $('#fifty-shades-base-color').val();
                var button = $(this);
                
                // Disable button and show loading
                button.prop('disabled', true).text('Applying...');
                
                // First, set the admin color scheme to our custom one
                $('input[name="admin_color"][value="fifty_shades_custom"]').prop('checked', true);
                
                // Send AJAX request to save the color
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'fifty_shades_update_color',
                        color: selectedColor,
                        admin_color: 'fifty_shades_custom',
                        nonce: '<?php echo wp_create_nonce('fifty_shades_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message without refreshing
                            button.text('Applied!').css('background-color', '#28a745');
                            
                            // Reset button after 2 seconds
                            setTimeout(function() {
                                button.prop('disabled', false).text('Apply Color').css('background-color', '');
                            }, 2000);
                        } else {
                            alert('Error saving color: ' + (response.data || 'Unknown error'));
                            button.prop('disabled', false).text('Apply Color');
                        }
                    },
                    error: function() {
                        alert('Error communicating with server');
                        button.prop('disabled', false).text('Apply Color');
                    }
                });
                    success: function(response) {
                        if (response.success) {
                            // Force page refresh
                            window.location.reload();
                        } else {
                            alert('Error saving color: ' + (response.data || 'Unknown error'));
                            button.prop('disabled', false).text('Apply Color');
                        }
                    },
                    error: function() {
                        alert('Error communicating with server');
                        button.prop('disabled', false).text('Apply Color');
                    }
                });
            });
        });
        </script>
        
        <div id="fifty-shades-section">
            <h2>Fifty Shades Color Customization</h2>
            <table class="form-table" role="presentation">
                <tr id="fifty-shades-controls">
                    <th scope="row">Choose Your Color</th>
                    <td>
                        <form method="post" id="fifty-shades-form" style="display: inline-block;">
                            <input type="hidden" name="admin_color" value="fifty_shades_custom">
                            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('update-user_' . $user->ID); ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                            
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                                <input type="color" name="fifty_shades_base_color" id="fifty-shades-base-color" value="<?php echo esc_attr($base_color); ?>" style="width: 60px; height: 40px; border: none; border-radius: 4px; cursor: pointer;">
                                
                                <input type="submit" value="Apply Color" class="button button-primary" style="height: 40px; padding: 0 20px;">
                            </div>
                        </form>
                        
                        <p class="description">
                            <strong>How it works:</strong> Pick any color you like, then click "Apply Color". 
                            The page will refresh (and show you an annoying error I can't do anything about) and show your new colorful admin theme.
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var firstTable = document.querySelector('table.form-table');
            var section = document.getElementById('fifty-shades-section');
            if (firstTable && section) {
                firstTable.parentNode.insertBefore(section, firstTable);
            }
            
            // Convert submit button to regular button for AJAX
            var submitButton = document.querySelector('#fifty-shades-form input[type="submit"]');
            if (submitButton) {
                // Change to regular button
                submitButton.type = 'button';
                
                submitButton.addEventListener('click', function() {
                    var colorValue = document.getElementById('fifty-shades-base-color').value;
                    
                    // Disable button
                    submitButton.disabled = true;
                    submitButton.value = 'Applying...';
                    
                    // Set the radio button first
                    var radioButton = document.querySelector('input[name="admin_color"][value="fifty_shades_custom"]');
                    if (radioButton) {
                        radioButton.checked = true;
                    }
                    
                    // Simple XMLHttpRequest
                    var xhr = new XMLHttpRequest();
                    var formData = new FormData();
                    
                    formData.append('action', 'fifty_shades_update_color');
                    formData.append('color', colorValue);
                    formData.append('admin_color', 'fifty_shades_custom');
                    formData.append('nonce', '<?php echo wp_create_nonce('fifty_shades_nonce'); ?>');
                    
                    xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>');
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    // Reset all form fields to their current values to clear "dirty" state
                                    var allInputs = document.querySelectorAll('input, select, textarea');
                                    for (var i = 0; i < allInputs.length; i++) {
                                        if (allInputs[i].type === 'checkbox' || allInputs[i].type === 'radio') {
                                            allInputs[i].defaultChecked = allInputs[i].checked;
                                        } else {
                                            allInputs[i].defaultValue = allInputs[i].value;
                                        }
                                    }
                                    
                                    // Clear any WordPress change tracking
                                    if (window.wp && window.wp.heartbeat) {
                                        window.wp.heartbeat.enqueue('wp_refresh_nonces', false);
                                    }
                                    
                                    // Force clear beforeunload
                                    window.onbeforeunload = null;
                                    
                                    // Show success message instead of refreshing
                                    applyButton.value = 'Applied!';
                                    applyButton.style.backgroundColor = '#28a745';
                                    
                                    // Reset button after 2 seconds
                                    setTimeout(function() {
                                        applyButton.disabled = false;
                                        applyButton.value = 'Apply Color';
                                        applyButton.style.backgroundColor = '';
                                    }, 2000);
                                } else {
                                    alert('Error: ' + (response.data || 'Unknown error'));
                                    applyButton.disabled = false;
                                    applyButton.value = 'Apply Color';
                                }
                            } catch (e) {
                                alert('Invalid response from server');
                                applyButton.disabled = false;
                                applyButton.value = 'Apply Color';
                            }
                        } else {
                            alert('Server error: ' + xhr.status);
                            applyButton.disabled = false;
                            applyButton.value = 'Apply Color';
                        }
                    };
                    
                    xhr.onerror = function() {
                        alert('Network error');
                        applyButton.disabled = false;
                        applyButton.value = 'Apply Color';
                    };
                    
                    xhr.send(formData);
                });
            }
        });
        </script>
        
        <?php
    }
    
    /**
     * Handle AJAX color update
     */
    public function ajax_update_color() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'fifty_shades_nonce')) {
            wp_die('Security check failed');
        }
        
        $user_id = get_current_user_id();
        
        if (!current_user_can('edit_user', $user_id)) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $color = sanitize_hex_color($_POST['color']);
        $admin_color = sanitize_text_field($_POST['admin_color']);
        
        if (!$color) {
            wp_send_json_error('Invalid color format');
            return;
        }
        
        // Save the color
        update_user_meta($user_id, 'fifty_shades_base_color', $color);
        
        // Set the admin color scheme
        update_user_meta($user_id, 'admin_color', $admin_color);
        
        wp_send_json_success('Color updated successfully');
    }
    
    /**
     * Save custom color scheme settings
     */
    public function save_custom_color_scheme($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        
        if (isset($_POST['fifty_shades_base_color'])) {
            update_user_meta($user_id, 'fifty_shades_base_color', sanitize_hex_color($_POST['fifty_shades_base_color']));
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'profile.php' && $hook !== 'user-edit.php') {
            return;
        }
        wp_enqueue_script('jquery');
    }
    
    /**
     * Force color overrides using the research-backed approach
     */
    public function force_color_overrides() {
        $user_id = get_current_user_id();
        $current_scheme = get_user_meta($user_id, 'admin_color', true);
        
        if ($current_scheme !== 'fifty_shades_custom') {
            return;
        }
        
        $base_color = get_user_meta($user_id, 'fifty_shades_base_color', true);
        
        if (empty($base_color)) {
            $base_color = '#0073aa';
        }
        
        $colors = $this->generate_color_scheme($base_color);
        
        // Use admin_head to inject styles that load AFTER all other stylesheets
        // Use high specificity selectors as identified in the research
        echo '<style id="fifty-shades-force-override">';
        echo $this->generate_high_specificity_css($colors);
        echo '</style>';
    }
    
    /**
     * Generate color scheme using simple, readable approach
     */
    private function generate_color_scheme($base_color) {
        $rgb = $this->hex_to_rgb($base_color);
        
        // Create a simple, readable scheme based on lightness variations
        $colors = array();
        $colors['primary'] = $base_color;
        
        // Always use safe, readable combinations
        $colors['dark'] = $this->darken_color($base_color, 40);      // Much darker for contrast
        $colors['secondary'] = $this->darken_color($base_color, 20); // Slightly darker
        $colors['accent'] = $this->lighten_color($base_color, 15);   // Slightly lighter
        $colors['light'] = $this->lighten_color($base_color, 30);    // Much lighter
        
        // Always use high contrast text colors
        $colors['text'] = '#ffffff';           // Always white text
        $colors['dark_text'] = '#1e1e1e';     // Dark text for light backgrounds
        
        return $colors;
    }
    
    /**
     * Generate CSS with maximum specificity to override WordPress defaults
     * Based on research findings about WordPress CSS specificity
     */
    private function generate_high_specificity_css($colors) {
        return "
        /* Fifty Shades of Admin - Maximum Specificity Overrides */
        
        /* Use ID duplication for 2-0-0+ specificity as recommended in research */
        
        /* Admin Bar - High specificity override */
        #wpadminbar#wpadminbar {
            background: {$colors['primary']} !important;
        }
        
        #wpadminbar#wpadminbar .ab-item, 
        #wpadminbar#wpadminbar a.ab-item,
        #wpadminbar#wpadminbar > #wp-toolbar span.ab-label,
        #wpadminbar#wpadminbar > #wp-toolbar span.noticon {
            color: {$colors['text']} !important;
        }
        
        #wpadminbar#wpadminbar .ab-top-menu > li:hover > .ab-item,
        #wpadminbar#wpadminbar .ab-top-menu > li.hover > .ab-item {
            background: {$colors['secondary']} !important;
            color: {$colors['text']} !important;
        }
        
        /* Main Admin Menu with maximum specificity */
        #adminmenu#adminmenu, 
        #adminmenuback#adminmenuback, 
        #adminmenuwrap#adminmenuwrap {
            background: {$colors['secondary']} !important;
        }
        
        /* Force override the problematic selectors identified in research */
        /* These have 1-3-0+ specificity requirements */
        #adminmenu#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head {
            background: {$colors['dark']} !important;
            color: {$colors['text']} !important;
        }
        
        /* These have 1-2-2+ specificity requirements */
        #adminmenu#adminmenu li.current a.menu-top {
            background: {$colors['primary']} !important;
            color: {$colors['text']} !important;
        }
        
        #adminmenu#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu {
            background: {$colors['primary']} !important;
            color: {$colors['text']} !important;
        }
        
        /* Comprehensive menu overrides with doubled ID specificity */
        #adminmenu#adminmenu a {
            color: {$colors['text']} !important;
        }
        
        #adminmenu#adminmenu div.wp-menu-name {
            color: {$colors['text']} !important;
        }
        
        #adminmenu#adminmenu li.menu-top:hover,
        #adminmenu#adminmenu li.opensub > a.menu-top,
        #adminmenu#adminmenu li > a.menu-top:focus {
            background-color: {$colors['accent']} !important;
            color: {$colors['text']} !important;
        }
        
        #adminmenu#adminmenu li.wp-has-current-submenu .wp-submenu-head {
            background: {$colors['dark']} !important;
            color: {$colors['text']} !important;
        }
        
        /* Submenu Areas with maximum override power */
        #adminmenu#adminmenu .wp-submenu,
        #adminmenu#adminmenu .wp-has-current-submenu .wp-submenu,
        #adminmenu#adminmenu a.wp-has-current-submenu:focus + .wp-submenu,
        #adminmenu#adminmenu .wp-has-submenu.wp-has-current-submenu.wp-menu-open .wp-submenu {
            background: {$colors['dark']} !important;
        }
        
        /* Submenu Links */
        #adminmenu#adminmenu .wp-submenu a {
            color: {$colors['text']} !important;
        }
        
        #adminmenu#adminmenu .wp-submenu a:hover,
        #adminmenu#adminmenu .wp-submenu a:focus {
            background: {$colors['accent']} !important;
            color: {$colors['text']} !important;
        }
        
        #adminmenu#adminmenu .wp-submenu li.current a,
        #adminmenu#adminmenu .wp-submenu li.current a:hover,
        #adminmenu#adminmenu .wp-submenu li.current a:focus {
            background: {$colors['primary']} !important;
            color: {$colors['text']} !important;
        }
        
        /* Buttons with high specificity */
        body.wp-admin .wp-core-ui .button-primary {
            background: {$colors['primary']} !important;
            border-color: {$colors['dark']} !important;
            color: {$colors['text']} !important;
        }
        
        body.wp-admin .wp-core-ui .button-primary:hover,
        body.wp-admin .wp-core-ui .button-primary:focus {
            background: {$colors['accent']} !important;
            border-color: {$colors['secondary']} !important;
            color: {$colors['text']} !important;
        }
        
        /* Menu Icons */
        #adminmenu#adminmenu .wp-menu-image:before {
            color: {$colors['text']} !important;
        }
        
        #adminmenu#adminmenu li.current .wp-menu-image:before,
        #adminmenu#adminmenu li.wp-has-current-submenu .wp-menu-image:before {
            color: {$colors['text']} !important;
        }
        
        /* Parent containers that might interfere */
        #adminmenu#adminmenu li.wp-has-current-submenu {
            background: {$colors['secondary']} !important;
        }
        
        #adminmenu#adminmenu li.wp-has-current-submenu > a {
            background: {$colors['primary']} !important;
            color: {$colors['text']} !important;
        }
        
        /* Additional WordPress-specific selectors that commonly cause issues */
        #adminmenu#adminmenu .wp-has-current-submenu .wp-submenu-head {
            background: {$colors['dark']} !important;
            color: {$colors['text']} !important;
        }
        
        #adminmenu#adminmenu .wp-menu-arrow {
            color: {$colors['primary']} !important;
        }
        
        #adminmenu#adminmenu .wp-menu-arrow div {
            background: {$colors['primary']} !important;
        }
        ";
    }
    
    /**
     * Darken a color by a percentage
     */
    private function darken_color($hex, $percent) {
        $rgb = $this->hex_to_rgb($hex);
        $factor = (100 - $percent) / 100;
        
        return $this->rgb_to_hex(
            max(0, $rgb['r'] * $factor),
            max(0, $rgb['g'] * $factor),
            max(0, $rgb['b'] * $factor)
        );
    }
    
    /**
     * Lighten a color by a percentage
     */
    private function lighten_color($hex, $percent) {
        $rgb = $this->hex_to_rgb($hex);
        $factor = $percent / 100;
        
        return $this->rgb_to_hex(
            min(255, $rgb['r'] + (255 - $rgb['r']) * $factor),
            min(255, $rgb['g'] + (255 - $rgb['g']) * $factor),
            min(255, $rgb['b'] + (255 - $rgb['b']) * $factor)
        );
    }
    
    /**
     * Convert HEX to RGB
     */
    private function hex_to_rgb($hex) {
        $hex = str_replace('#', '', $hex);
        return array(
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        );
    }
    
    /**
     * Convert RGB to HEX
     */
    private function rgb_to_hex($r, $g, $b) {
        return sprintf("#%02x%02x%02x", round($r), round($g), round($b));
    }
}

// Initialize the plugin
new FiftyShadesOfAdmin();
?>