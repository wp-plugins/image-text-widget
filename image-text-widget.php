<?php
/*
Plugin Name: Image & Text Widget
Description: Image & Text Widget is an easy to use plugin that uses the native WordPress media manager to add image & text widgets to your site.
Version: 1.0.3
Author: dFactory
Author URI: http://www.dfactory.eu/
Plugin URI: http://www.dfactory.eu/plugins/image-text-widget/
License: MIT License
License URI: http://opensource.org/licenses/MIT
Text Domain: image-text-widget
Domain Path: /languages

Image & Text Widget
Copyright (C) 2013-2015, Digital Factory - info@digitalfactory.pl

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

define( 'IMAGE_TEXT_WIDGET_URL', plugins_url( '', __FILE__ ) );
define( 'IMAGE_TEXT_WIDGET_PATH', plugin_dir_path( __FILE__ ) );

new Image_Text_Widget_Plugin();

class Image_Text_Widget_Plugin {

	private $defaults = array(
		'version' => '1.0.3'
	);

	public function __construct() {
		register_activation_hook( __FILE__, array( &$this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivation' ) );

		// update plugin version
		update_option( 'image_text_widget_version', $this->defaults['version'], '', 'no' );

		// actions
		add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts_styles' ) );
		add_action( 'widgets_init', array( &$this, 'register_widget' ) );
	}

	/**
	 * 
	 */
	public function register_widget() {
		include_once( IMAGE_TEXT_WIDGET_PATH . 'includes/widget.php' );

		register_widget( 'Image_Text_Widget' );
	}

	/**
	 * Plugin activation function
	 */
	public function activation() {
		add_option( 'image_text_widget_version', $this->defaults['version'], '', 'no' );
	}

	/**
	 * Plugin deactivation function
	 */
	public function deactivation() {
		delete_option( 'image_text_widget_version' );
	}

	/**
	 * Load textdomain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'image-text-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Admin scripts and styles
	 */
	public function admin_scripts_styles( $page ) {
		if ( $page === 'widgets.php' ) {
			wp_enqueue_media();

			wp_register_script(
				'image-text-widget-admin', IMAGE_TEXT_WIDGET_URL . '/js/admin.js', array( 'jquery' )
			);

			wp_enqueue_script( 'image-text-widget-admin' );

			wp_localize_script(
				'image-text-widget-admin', 'itwArgs', array(
				'title'		=> __( 'Select image', 'image-text-widget' ),
				'button'	=> array( 'text' => __( 'Add image', 'image-text-widget' ) ),
				'frame'		=> 'select',
				'multiple'	=> false
				)
			);

			wp_register_style(
				'image-text-widget-admin', IMAGE_TEXT_WIDGET_URL . '/css/admin.css'
			);

			wp_enqueue_style( 'image-text-widget-admin' );
		}
	}

}
