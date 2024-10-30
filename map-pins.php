<?php
/**
 * Plugin Name: map-pins
 * Plugin URI: http://mappins.innovader.nl
 * Text Domain: map-pins
 * Description: Show pins on a map from an admin defined table
 * Version: 1.29
 * Author: Innovader BV
 * Author URI: http://innovader.nl
 * License: GPL2
 */ 
if ( !defined('ABSPATH') ) die('-1');	// no direct access
define('MAPPINS_PLUGIN_DIR', WP_PLUGIN_DIR."/".dirname(plugin_basename(__FILE__)));
define('MAPPINS_PLUGIN_URL', WP_PLUGIN_URL."/".dirname(plugin_basename(__FILE__)));
define('MAPPINS_PICS_URL', MAPPINS_PLUGIN_URL.'/pics');
define('MAPPINS_PICS_DIR', MAPPINS_PLUGIN_DIR.'/pics');
define('MINIMAL_PHP_VERSION','5.2.4');

require (MAPPINS_PLUGIN_DIR.'/include/xcos.php');		// helpers
require (MAPPINS_PLUGIN_DIR.'/include/xcos_country.php');		// country helper
require (MAPPINS_PLUGIN_DIR.'/include/marker.php');		// marker model
require (MAPPINS_PLUGIN_DIR.'/include/mappins.php');	// main object

// Create  a CPM object that contain main plugin logic
add_action( 'init', 'mappins_init');
add_action( 'admin_init', 'mappins_admin_init' );


function mappins_init(){
	global $mappins_obj;
	$mappins_obj = new mappins;

    load_plugin_textdomain('mappins', false, basename(dirname(__FILE__))."/languages/");
	
	// buffer output if we need to do a redirect later
	if (XCos::ifset($_REQUEST['needredirect'])=='Y')
		ob_start();
	
	add_shortcode('mappins-map', array(&$mappins_obj, 'replace_shortcode'));
	
}

function mappins_admin_init(){
	global $mappins_obj;
    load_plugin_textdomain('mappins', false, basename(dirname(__FILE__))."/languages/");
}

add_action('admin_enqueue_scripts', array(&$mappins_obj, 'load_admin_resources'), 1);
add_action('wp_footer', array(&$mappins_obj, 'load_footer_resources'), 1);
add_action('admin_menu',  array(&$mappins_obj, 'mappins_settings'));
register_activation_hook( __FILE__, 'mappins_install' );

function mappins_install() {
 	global $wpdb;
	
	if ( version_compare( PHP_VERSION, MINIMAL_PHP_VERSION, '<' ) ) {
	    deactivate_plugins( basename( __FILE__ ) );
	    wp_die('<p>The <strong>map-pins</strong> plugin requires PHP version '.MINIMAL_PHP_VERSION.' or greater.</p>','Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
		return;
	}

	$table_name = $wpdb->prefix . "mappins_markers";
    $sql = "
        CREATE TABLE `".$table_name."` (
			id int(11) NOT NULL AUTO_INCREMENT  PRIMARY KEY,
			address varchar(700) NOT NULL,
			zip  varchar(10) NOT NULL,
			city varchar(60) NOT NULL,
			country varchar(2) default 'NL' NOT NULL,
			description mediumtext NOT NULL,
			link varchar(700) NOT NULL,
			icon varchar(700) NOT NULL,
			lat varchar(25) NOT NULL,
			lng varchar(25) NOT NULL,
			category varchar(12) NOT NULL,
			tel varchar(22) NOT NULL,
			remarks varchar(255) NOT NULL,
			opentimes text not null,
			afterhoursdesc varchar(700) not null
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
	
}
