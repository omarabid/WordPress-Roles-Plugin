<?php
/*
  Plugin Name: WordPress Roles Plugin
  Plugin URI: https://github.com/omarabid/WordPress-Roles-Plugin
  Description: A WordPress Roles Plugin 
  Author: Abid Omar
  Author URI: http://omarabid.com
  Version: 2.0
 */

// Don't load directly
if (!defined('ABSPATH')) {
    die('-1');
}

// Registers a new settings form
add_action('admin_init', 'wptuts_settings_form');
function wptuts_settings_form()
{
    register_setting('wptuts_settings', 'wptuts_settings');

    add_settings_section('wptuts_settings', 'General Settings', function()
    {
        return null;
    }, 'general_settings_form', 'Client Access');

    add_settings_field('client_roles', 'Client Roles', 'wptuts_roles_check', 'general_settings_form', 'wptuts_settings', array('client_roles', 'wptuts_settings'));
}

/**
 * Generates the roles checkboxes form
 *
 * @param array $param
 */
function wptuts_roles_check($param)
{
    var_dump(get_option('wptuts_settings'));
    // Roles list
    $settings = get_option($param[1]);
    if (isset($settings[$param[0]])) {
        $val = $settings[$param[0]];
    } else {
        $val = '';
    }

    // Generate HTML Code
    // Get WP Roles
    global $wp_roles;
    $roles = $wp_roles->get_names();
    unset($roles['administrator']);
    // Generate HTML code
    if ($val['all'] === 'on') {
        echo '<input type="checkbox" name="' . $param[1] . '[' . $param[0] . '][all]" id="' . $param[0] . '[all]" checked/>  All<br />';
    } else {
        echo '<input type="checkbox" name="' . $param[1] . '[' . $param[0] . '][all]" id="' . $param[0] . '[all]" />  All<br />';
    }

    foreach ($roles as $key => $value) {
        if ($val[$key] === 'on') {
            echo '<input type="checkbox" name="' . $param[1] . '[' . $param[0] . '][' . $key . ']" id="' . $param[0] . '[' . $key . ']" checked />  ' . $value . '<br />';
        } else {
            echo '<input type="checkbox" name="' . $param[1] . '[' . $param[0] . '][' . $key . ']" id="' . $param[0] . '[' . $key . ']" />  ' . $value . '<br />';
        }

    }
}

// Add an Admin user menu to the WordPress Dashboard
add_action('admin_menu', 'wptuts_admin_menu');
function wptuts_admin_menu()
{
    add_menu_page('Admin Access', 'Admin Access', 'manage_options', 'wptuts-admin', 'wptuts_admin_page');
}

function wptuts_admin_page()
{
    ?>
<div class="wrap">
    <h2>Admin Page</h2>

    <form action="options.php" method="POST">
        <?php
        // Display the Settings form
        settings_fields('wptuts_settings');
        do_settings_sections('general_settings_form');
        ?>
        <input type="hidden" name="_wp_http_referer" value="<?php echo admin_url('admin.php?page=wptuts-admin') ?>"/>

        <p class="submit">
            <input name="Submit" type="submit" class="button-primary" value="Save Changes"/>
        </p>
    </form>
</div>
<?php
}

// Add a client user menu to the WordPress Dashboard
add_action('admin_menu', 'wptuts_client_menu');
function wptuts_client_menu()
{
    add_menu_page('Client Access', 'Client Access', 'wptuts_client', 'wptuts-client', 'wptuts_client_page');
}

function wptuts_client_page()
{
    ?>
<div class="wrap">
    <h2>Client Page</h2>
</div>
<?php
}

// Loads and initialize the the roles class
add_action('init', 'wptuts_start_plugin');
function wptuts_start_plugin()
{
    require_once('roles_class.php');
    new wpttuts_roles();
}