<?php

/*
Plugin Name: Pometa REST API Cache
Plugin URI: http://www.lapometa.com/plugins/pometarest
Description: Gestió Cache API REST Pometa
Version: 1.1
Author: Jordi Fonfreda
Email: suport@lapometa.com
Author URI: http://www.lapometa.com/author/jordi-fonfreda
Text Domain: pometarestltd
GitHub Plugin URI: La-Pometa/pometa-wp-rest-cache
Primary Branch: main
*/


	/* DEFINES */

	define("POMETAREST_LTD","pometarestltd");
  	define('POMETAREST_PLUGIN_SERVER_PATH', plugin_dir_path( __FILE__ ));
  	define('POMETAREST_PLUGIN_SERVER_URL', plugin_dir_url( __FILE__ ));


	/* ENQUEUE FILES CSS & JS */
	add_action( 'init', 'pometarest_load_textdomain' );
	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	function pometarest_load_textdomain() {
		load_plugin_textdomain( 'pometarestltd', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}


	add_action("wp_head","pometarest_wp_header_css_js");
	add_action( 'admin_enqueue_scripts', 'pometarest_admin_header_css_js' );

	function pometarest_wp_header_css_js() {
	}
	function pometarest_admin_header_css_js() {
		wp_register_style("adminpometarestcss",plugins_url('assets/css/pometarest.admin.css', __FILE__));
		wp_enqueue_style("adminpometarestcss");
	}


	/* INCLUDES */

	require_once("includes/common.php");
    require_once("includes/lib.php");
    require_once("includes/clean.php");

	//TODO: Solament si estic a l'administrador
    require_once("includes/settings.php");

	//TODO: Solament si estic amb REST
	require_once("includes/runtime.php");

	require_once("general.php");


    

