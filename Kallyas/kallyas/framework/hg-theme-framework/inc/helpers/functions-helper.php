<?php if(! defined('ABSPATH')){ return; }

/*--------------------------------------------------------------------------------------------------
	Get option - This function will return the option
	@option : if specified, returns the option value , if not, returns the full list of category options
	@category : returns the saved options category
--------------------------------------------------------------------------------------------------*/
	global $saved_options;
	$saved_options = '';

	function zget_option( $option, $category = false , $all = false , $default = false ) {

		global $saved_options;

		if ( empty( $saved_options ) ) {
			$saved_options = get_option( ZNHGTFW()->getThemeDbId() );
		}

		if ( $all ){
			return $saved_options;
		}

		if ( !empty($saved_options[$category][$option]) || ( isset($saved_options[$category][$option]) && $saved_options[$category][$option] === '0' ) ) {
			$return = $saved_options[$category][$option];
		}
		elseif( isset( $default ) ){
			$return = $default;
		}
		else {
			$return = false;
		}

		return $return;
	}


	/**
	 * Returns the value of an option saved as metabox or theme option
	 * @param  string  $option   The option id
	 * @param  string  $category The category the option belongs to in theme options
	 * @param  boolean $all      If all the options should be returned
	 * @param  mixed  $default  The default value that should be returned in case the option is not saved
	 */
	function zget_meta_option( $option, $category, $all = false, $default = null ){
		if( is_singular() ){
			$postId = get_the_ID();
			$savedValue = get_post_meta( $postId, $option, true );
			if( ! empty( $savedValue ) || $savedValue === '0' ){
				return $savedValue;
			}
		}

		return zget_option( $option, $category, $all, $default );

	}

	global $zn_current_post_id;
	function zn_get_the_id() {
		global $zn_current_post_id;

		if ( isset( $zn_current_post_id ) ) {
			$id = $zn_current_post_id;
		}
		else{
			if( isset( $_POST['post_id'] ) ){
				$id = $zn_current_post_id = $_POST['post_id'];
			}
			else{
				$post = get_post();
				if(isset( $post->ID) ) {
					$id = $zn_current_post_id = get_queried_object_id();
				}
				else{
					$id = $zn_current_post_id = false;
				}

			}
		}

		$id = apply_filters('zn_get_the_id', $id);

		return $id;

	}


/*--------------------------------------------------------------------------------------------------
	Sanitize string for widgets
--------------------------------------------------------------------------------------------------*/
function zn_sanitize_widget_id($id){
	$id = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $id );
	return str_replace(' ','_',strtolower($id) );
}


/* CUSTOM WP_FOOTER FUNCTION
	Fixes problems with next gen gallery
*/
function zn_footer(){
	do_action('zn_footer');
}

/**
 * Checks if a plugin is installed. The $plugin variable should contain the plugin name and main file ( for example zn_framework/zn_framework.php )
 * @param type $plugin
 * @return bool
 */
function zn_is_plugin_installed( $plugin ){
	if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
		return true;
	} else {
		return false;
	}
}

function zn_is_plugin_active( $plugin_path = '' ) {
	/**
	 * Detect plugin. For use on Front End only.
	 * eg: zn_is_plugin_active( 'plugin-directory/plugin-file.php' );
	 */
	if(!function_exists('is_plugin_active')){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	// check for plugin using plugin name
	return is_plugin_active( $plugin_path );
}

/**
 * Verify whether or not the WooCommerce plugin is installed and active.
 * On some web hosts, like godaddy, the check for WooCommerce using is_plugin_active returns true even if the plugin
 * is not installed or active.
 */
function znfw_is_woocommerce_active(){
	return class_exists('WooCommerce');
}

/**
 * Refresh permalinks
 */
add_action( 'znhgtfw_flush_rewrite_rules', 'znhgtfw_flush_rewrite_rules' );
function znhgtfw_flush_rewrite_rules(){
	flush_rewrite_rules();
}

if ( !function_exists( 'zn_schema_markup' ) )
{
	/**
	 * Schema.org additions
	 * @param 	string 	$type of the element
	 * @param 	string 	$echo if teh result should be echoed
	 * @return  string  Attribute
	 */
	function zn_schema_markup($type, $echo = false) {

		if (empty($type)) return false;

		$disable = apply_filters('zn_schema_markup_disable', false);

		if($disable == true) {
			return false;
		}

		$attributes = '';
		$attr = array();

		switch ($type) {
			case 'body':
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/WebPage';
				break;

			case 'header':
				$attr['role'] = 'banner';
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/WPHeader';
				break;

			case 'nav':
				$attr['role'] = 'navigation';
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/SiteNavigationElement';
				break;

			case 'title':
				$attr['itemprop'] = 'headline';
				break;

			case 'subtitle':
				$attr['itemprop'] = 'alternativeHeadline';
				break;

			case 'sidebar':
				$attr['role'] = 'complementary';
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/WPSideBar';
				break;

			case 'footer':
				$attr['role'] = 'contentinfo';
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/WPFooter';
				break;

			case 'main':
				$attr['role'] = 'main';
				$attr['itemprop'] = 'mainContentOfPage';
				if (is_search()) {
					$attr['itemtype'] = 'https://schema.org/SearchResultsPage';
				}

				break;

			case 'author':
				$attr['itemprop'] = 'author';
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/Person';
				break;

			case 'person':
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/Person';
				break;

			case 'comment':
				$attr['itemprop'] = 'comment';
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/UserComments';
				break;

			case 'comment_author':
				$attr['itemprop'] = 'creator';
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/Person';
				break;

			case 'comment_author_link':
				$attr['itemprop'] = 'creator';
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/Person';
				$attr['rel'] = 'external nofollow';
				break;

			case 'comment_time':
				$attr['itemprop'] = 'commentTime';
				$attr['itemscope'] = 'itemscope';
				$attr['datetime'] = get_the_time('c');
				break;

			case 'comment_text':
				$attr['itemprop'] = 'commentText';
				break;

			case 'author_box':
				$attr['itemprop'] = 'author';
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/Person';
				break;

			case 'video':
				$attr['itemprop'] = 'video';
				$attr['itemtype'] = 'https://schema.org/VideoObject';
				break;

			case 'audio':
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/AudioObject';
				break;

			case 'blog':
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/Blog';
				break;

			case 'blogpost':
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/Blog';
				break;

			case 'name':
				$attr['itemprop'] = 'name';
				break;

			case 'url':
				$attr['itemprop'] = 'url';
				break;

			case 'email':
				$attr['itemprop'] = 'email';
				break;

			case 'post_time':
				$attr['itemprop'] = 'datePublished';
				break;

			case 'post_content':
				$attr['itemprop'] = 'text';
				break;

			case 'creative_work':
				$attr['itemscope'] = 'itemscope';
				$attr['itemtype'] = 'https://schema.org/CreativeWork';
				break;

			case 'logo':
				$attr['itemprop'] = 'logo';
				break;
		}

		/**
		 * Filter to override or append attributes
		 * @var array
		 */
		$attr = apply_filters('zn_schema_markup_attributes', $attr);

		foreach ($attr as $key => $value) {
			$attributes.= $key . '="' . $value . '" ';
		}

		if ($echo) {
			echo $attributes;
		}
		return $attributes;
	}
}

//<editor-fold desc=":: General Options > ReCaptcha Options (v4.11) ::">

/**
* Check to see whether or not ReCaptcha is enabled on General options > ReCaptcha options
* @return bool
*/
function znhgReCaptchaEnabled() {
	return ( 'yes' == zget_option( 'recaptcha_register', 'general_options', false, 'no' ) );
}

 /**
 * Retrieve the style to use when displaying ReCaptcha
 * @return string
 */
function znhgReCaptchaStyle() {
	return zget_option( 'rec_theme', 'general_options', false, 'light' );
}

/**
* Retrieve the Site Key to use for ReCaptcha
* @return string
*/
function znhgReCaptchaSiteKey() {
	return zget_option( 'rec_pub_key', 'general_options' );
}

/**
* Retrieve the Secret for ReCaptcha
* @return string
*/
function znhgReCaptchaSecret() {
	return zget_option( 'rec_priv_key', 'general_options' );
}

/**
* Retrieve the Language to use when displaying ReCaptcha
* @return string
*/
function znhgReCaptchaLanguage() {
	return zget_option( 'recaptcha_lang', 'general_options', false, 'en' );
}

/*
* Load ReCaptcha script if enabled
*/
add_action( 'wp_head', 'znhgReCaptchaScripts', 800 );
function znhgReCaptchaScripts(){
	//#! should not be admin or logged in
	$wpUser = wp_get_current_user();
	if( $wpUser && ! empty($wpUser->ID)) {
		return;
	}

	if( znhgReCaptchaEnabled() ){
		wp_enqueue_script( 'jquery' );
		//#! Use the same script as the one from contact form, no need to duplicate
		if( ! wp_script_is('zn_recaptcha')) {
			$lang = znhgReCaptchaLanguage();
			if( ! empty($lang)){
				$lang = '&hl='.esc_attr( wp_strip_all_tags($lang,true));
			}
			wp_enqueue_script( 'zn_recaptcha', 'https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit' . $lang, array( 'jquery' ), false );
			wp_localize_script( 'zn_recaptcha', 'zn_contact_form', array(
				'captcha_not_filled' => __( 'Please complete the Captcha validation', 'zn_framework' ),
			) );
		}
	}
}

/**
* validate the ReCaptcha on the registration form
* @return array
*/
function znhgReCaptchaValidate(){
	$captcha_val = $_POST['g-recaptcha-response'];
	$pvKey = znhgReCaptchaSecret();
	$response = wp_remote_request( "https://www.google.com/recaptcha/api/siteverify?secret=" . trim( $pvKey ) . "&response=" . trim( $captcha_val ) );
	$errors = array();

	if ( is_wp_error($response) ) {
		error_log('[ZNHG] Register Form ReCaptcha error: '.$response->get_error_message() );
		$errors[] = __( 'An error occurred. Please try again in a few moments.', 'zn_framework' );
	}
	elseif( !isset($response['body']) || empty( $response['body'] ) ){
		$errors[] = __( 'An error occurred. Please try again in a few moments.', 'zn_framework' );
	}
	else {
		$response = json_decode( $response['body'], true );
	}

	if( !is_array($response) || ! isset($response["success"]) ){
		$errors[] = __( 'An error occurred. Please try again in a few moments.', 'zn_framework' );
		$response = array(
			'success' => false,
		);
	}

	if ( $response["success"] !== true ) {
		if ( ! empty( $response['error-codes'] ) && is_array( $response['error-codes'] ) ) {
			foreach ( $response['error-codes'] as $key => $value ) {
				if ( $value == 'missing-input-secret' ) {
					$errors[] = __( 'The secret parameter is missing.', 'zn_framework' );
					continue;
				}
				if ( $value == 'invalid-input-secret' ) {
					$errors[] = __( 'The secret parameter is invalid or malformed.', 'zn_framework' );
					continue;
				}
				if ( $value == 'missing-input-response' ) {
					$errors[] = __( 'Please complete the captcha validation', 'zn_framework' );
					continue;
				}
				if ( $value == 'invalid-input-response' ) {
					$errors[] = __( 'The response parameter is invalid or malformed.', 'zn_framework' );
					continue;
				}
			}
		}
		// response == false, no error codes retrieved, meaning the ReCaptcha is not properly configured
		else {
			$errors[] = __( 'Error: ReCaptcha is not properly configured.', 'zn_framework' );
		}
	}
	return $errors;
}
 //</editor-fold desc=":: General Options > ReCaptcha Options (v4.11) ::">
