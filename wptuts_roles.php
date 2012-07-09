<?php
/*
  Plugin Name: WordPress Roles Plugin
  Plugin URI: https://github.com/omarabid/WordPress-Roles-Plugin
  Description: A WordPress Roles Plugin 
  Author: Abid Omar
  Author URI: http://omarabid.com
  Version: 1.0
 */

// Add an Admin user menu to the WordPress Dashboard
add_action('admin_menu', 'wptuts_admin_menu');
function wptuts_admin_menu()
{
  add_menu_page('Admin Access', 'Admin Access', 'manage_options', 'wptuts-admin', 'wptuts_admin_page');
}
function wptuts_admin_page()
{
  echo 'Admin Page';
}

// Add a client user menu to the WordPress Dashboard
add_action('admin_menu', 'wptuts_client_menu');
function wptuts_client_menu()
{
  add_menu_page('Client Access', 'Client Access', 'wptuts_client', 'wptuts-client', 'wptuts_client_page');
}
function wptuts_client_page()
{
  echo 'Client Page';
}

// Loads and initialize the the roles class
add_action('init', 'wptuts_start_plugin');
function wptuts_start_plugin()
{
  require_once('roles_class.php');
  $all = false;
  $roles = array('subscriber');
  $users = array(3);
  new wpttuts_roles($all, $roles, $users);
}