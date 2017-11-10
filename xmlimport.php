<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:       xmlimport
 * Plugin URI:        http://192.168.0.27
 * Description:       XML fájlokból importál termékeket 
 * Version:           1.0.0
 * Author:            Bela Papp
 * Author URI:        http://belapapp.xyz/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

add_action('admin_menu', 'xmlimport_plugin_setup_menu');
 
function xmlimport_plugin_setup_menu(){
        add_menu_page( 'xmlimport Plugin Page', 'xmlimport Plugin', 'manage_options', 'xmlimport-plugin', 'xmlimport_init' );
}
 
function xmlimport_init(){
        echo "<h1>XML import</h1>";
        //echo __FILE__.'<br/>';
        //echo var_dump(wp_upload_dir()).'<br/>';
        include dirname(__FILE__)."/index.php";
}
?>
