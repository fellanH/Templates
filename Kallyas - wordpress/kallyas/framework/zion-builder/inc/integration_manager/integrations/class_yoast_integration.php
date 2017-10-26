<?php

class Znb_Yoast_Integration extends Znb_Integration{

	/**
	 * Check if we can load this integration or not
	 * @return [type] [description]
	 */
	static public function can_load(){
		return defined( 'WPSEO_VERSION' ) && is_admin();
	}

	function initialize(){
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts') );
		add_action( 'admin_footer', array( $this, 'wpseo_znpb_data_footer'), 100 );
	}

	/**
	 * Load the js file that will add the PB content to Yoast
	 * @param  sctring $hook The current page hook
	 * @since 1.0.0
	 */
	function load_scripts( $hook ){
		if ( $hook == 'post.php' || $hook == 'post-new.php' ) {
			wp_enqueue_script( 'znb-yoast-integration-js', ZNB()->assetsUrl('js/admin') .'yoast-integration.js', 'jquery', ZNB()->version, true );
		}
	}

	function wpseo_znpb_data_footer() {
		$screen = get_current_screen();
		if( $screen->base && ( 'post' == $screen->base ) ) {
			$metaValue = get_post_meta( get_the_ID(), 'zn_page_builder_els', 1 );
			echo '<div id="wpseo_zn_page_builder_els__wrapper" style="display:none !important; width:0 !important; height:0 !important;">'.$this->get_pb_content_data( $metaValue ).'</div>';
		}
	}

	/**
	 * Returns the pagebuilder content data as a string
	 *
	 * @access public
	 * @var $content string
	 * @return string
	 */
	public function get_pb_content_data( $content ){
		$flat = '';
		$skipped = array(
			'object',
			'uid',
			'width',
		);
		if ( empty( $content ) || ! is_array( $content ) ) {
			return $flat;
		}
		if ( is_array( $content ) ) {
			foreach ( $content as $key => $value ) {
				if( in_array( $key, $skipped, true ) ){
					continue;
				}
				if ( is_array( $value ) ) {
					$flat .= $this->get_pb_content_data( $value );
				}
				else {
					$flat .= ' ' . $value;
				}
			}
		}
		else {
			return ' ' . $content;
		}
		return $flat;
	}

}
