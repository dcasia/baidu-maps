<?php
/*
Plugin Name: Baidu Maps Plugin
Plugin URI: http://www.digitalcreative.asia/
Description: A Wordpress Plugin to easily integrate baidu maps
Version: 1.0
Author: Digital Creative
Author URI: http://www.digitalcreative.asia/
License:

  Copyright 2013 Samuel Jesse (samueljesse@digitalcreative.asia)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


require_once 'include/baidu-maps-admin.php';
require_once 'include/baidu-maps-api.php';
require_once 'include/baidu-maps-settings.php';


class Baidu_Maps {

	protected $plugin_path;
	protected $plugin_url;

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	public function __construct() {

		// Set the plugin path
		$this->plugin_path = dirname( __FILE__ );

		// Load all the settings for Baidu Maps
		$this->settings = get_option( 'baidu_maps_settings' );

		// Set the plugin url
		$this->plugin_url = WP_PLUGIN_URL . DIRECTORY_SEPARATOR . plugin_basename( __DIR__ ) . DIRECTORY_SEPARATOR;

		load_plugin_textdomain( 'baidu-maps', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );


		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );


		// Register the shortcode
		add_shortcode( 'bmap', array( $this, 'shortcode' ) );


		// Initialize the admin interface
		$admin = new Baidu_Maps_Admin($this->plugin_url);

		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

	}


	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/

	/**
	 * Upon activation, create and show the options_page with default options.
	 */
	public function activate() {

	}

	/**
	 * Upon deactivation, removes the options page.
	 */
	public function deactivate() {

	}

	/**
	 * Registers and enqueues admin-specific minified JavaScript.
	 */
	public function register_scripts() {
		// Enqueue Baidu Maps Script
		wp_register_script( 'baidu-maps-script-api', 'http://api.map.baidu.com/api?v=2.0&ak=' . $this->settings['api_key'], false, true );
		wp_enqueue_script( 'baidu-maps-script-api' );


		// Enqueue Plugin's Frontend Styles
		wp_register_style( 'baidu-maps-style-admin', $this->plugin_url . 'css/map.css' );
		wp_enqueue_style( 'baidu-maps-style-admin' );

		// Enqueue Plugin's Frontend Script
		wp_register_script( 'baidu-maps-script-map', $this->plugin_url . 'js/map.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'baidu-maps-script-map' );
	}

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {
		// Enqueue Baidu Maps Script
		wp_register_script( 'baidu-maps-script-api', 'http://api.map.baidu.com/api?v=2.0&ak=' . $this->settings['api_key'], false, true );
		wp_enqueue_script( 'baidu-maps-script-api' );

		wp_register_style( 'baidu-maps-style-admin', $this->plugin_url . 'css/admin.css' );
		wp_enqueue_style( 'baidu-maps-style-admin' );

		wp_register_script( 'baidu-maps-script-admin', $this->plugin_url . 'js/admin.js', array( 'jquery', 'wp-color-picker' ), false, true );
		wp_enqueue_script( 'baidu-maps-script-admin' );

		wp_localize_script( 'baidu-maps-script-admin', 'pluginUrl', $this->plugin_url );
		wp_localize_script( 'baidu-maps-script-admin', 'ajaxurl', admin_url( 'admin-ajax.php' ) );

		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		wp_enqueue_script( 'media-upload' );

		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Creates the shortcode to use with themes
	 */
	public function shortcode( $atts ) {

		extract( shortcode_atts( array(
			'id'     => 0,
			'zoom'   => 13,
			'lat'    => '39.915',
			'lng'    => '116.404',
			'width'  => 500,
			'height' => 300,
		), $atts ) );

		return $id > 0 ? $this->makeMapWithID( $id ) : $this->makeMap( $zoom, $lat, $lng, $width, $height );
	}

	/**
	 * Returns a Map Element (div) created with the parameters provided below.
	 *
	 * @param $zoom
	 * @param $lat
	 * @param $lng
	 * @param $width
	 * @param $height
	 *
	 * @return $map_element
	 */
	public function makeMap( $zoom, $lat, $lng, $width, $height ) {
		$id             = uniqid();
		$baidu_maps_api = new Baidu_Maps_API();
		$map_element    = $baidu_maps_api->createMapElement( $id, $width, $height, false );
		$map            = $baidu_maps_api->createMap( $id, $zoom, $lat, $lng );

		return $map_element;
	}

	/**
	 * Returns a Map Element (div) created with the id of the map, created with the baidu maps admin.
	 *
	 * @param $id
	 *
	 * @return $map_element
	 */
	public function makeMapWithID( $id ) {
		$baidu_maps_api = new Baidu_Maps_API();
		$map_element    = $baidu_maps_api->createMapWithID( $id, $zoom, $lat, $lng );

		return $map_element;
	}

	public function display_admin_notices() {
		// Check if API Key is entered
		if ( $this->settings['api_key'] == '' && current_user_can( 'manage_options' ) ) {
			global $post_type;
			if ( $post_type == 'bmap' ) {
				$notice[] = "<div class='error'>";
				$notice[] = "<p>" . __( "You have not entered your Baidu Developers API Key", 'baidu-maps' ) . " : " . "<a href='" . admin_url( "edit.php?post_type=bmap&page=baidu-maps-admin" ) . "'>" . __( "Click Here to enter it", 'baidu-maps' ) . "</a></p> </div>";

				echo implode( "\n", $notice );
			}
		}
	}

}

new Baidu_Maps;