<?php

/**
 *
 */
add_action( 'wp_enqueue_scripts', 'kl_child_scripts',11 );
function kl_child_scripts() {

	/**
	 * Load the child theme's style.css *after* the parent theme's one.
	 */
	wp_deregister_style( 'kallyas-styles' );
    wp_enqueue_style( 'kallyas-styles', get_template_directory_uri().'/style.css', '' , ZN_FW_VERSION );
    wp_enqueue_style( 'kallyas-child', get_stylesheet_uri(), array('kallyas-styles') , ZN_FW_VERSION );


	/**
	 * Load a custom JavaScript file.
	 * To use, please uncomment by removing the: "//"
	 */

	// wp_enqueue_script( 'zn_script_child', get_stylesheet_directory_uri() .'/js/zn_script_child.js' , '' , ZN_FW_VERSION , true );
}

/**
 * Load child theme's textdomain.
 */
add_action( 'after_setup_theme', 'kallyasChildLoadTextDomain' );
function kallyasChildLoadTextDomain(){
   load_child_theme_textdomain( 'zn_framework', get_stylesheet_directory().'/languages' );
}