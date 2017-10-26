<?php if(! defined('ABSPATH')){ return; }

/**
 * Theme's service. Interacts with our demo server and retrieves the list of all available demos.
 * @requires valid user
 */
class ZN_HogashDashboard
{
	//<editor-fold desc="::: DASH CONSTANTS ::">
	const E_SUCCESS = 'x001';
	//</editor-fold desc="::: DASH CONSTANTS ::">

	//<editor-fold desc="::: THEME CONSTANTS ::">
	const THEME_CHECK_OPTION = 'hg_dash_theme_check';

	const THEME_DEMOS_TRANSIENT = 'hg_dash_theme_demos';

	const THEME_PLUGINS_TRANSIENT = 'hg_dash_plugins';

	const THEME_API_KEY_OPTION = 'hg_dash_api_key';
	//</editor-fold desc="::: THEME CONSTANTS ::">

	/**
	 * Whether or not is connected
	 * @var null
	 */
	static $isConnected = null;

	/**
	 * Whether or not this is GoDaddy hosting
	 * @see isGoDaddy()
	 * @var bool
	 */
	private static $_isGoDaddy = null;

	/**
	 * Stores the theme ID
	 * @var string
	 */
	private static $_themeID = '';

	public static function init()
	{
		add_action( 'dash_clear_cached_data', array( get_class(), 'clearCachedData' ), 0 );
		self::$_themeID = ZNHGTFW()->getThemeId();
	}

	//<editor-fold desc="::: DASHBOARD INTEGRATION">
	/**
	 * Connect the theme with the Hogash Dashboard
	 * @param $apiKey
	 * @return array String error message on request failure, array on success
	 */
	public static function connectTheme( $apiKey )
	{
		// Check if the API Key or the Connection Status has changed
		// Save a request to dash if nothing changed
		if( $apiKey == self::getApiKey() && self::isConnected() ) {
			return array( 'success' => true, 'code' => self::E_SUCCESS, 'message' => esc_html(__('The theme is connected with the Hogash Dashboard.', 'zn_framework')));
		}
		if( ! self::isValidApiKeyFormat($apiKey ) ) {
			return array(
				'success' => false,
				'code' => '',
				'message' => esc_html( __( 'Invalid API key format.', 'zn_framework' ) )
			);
		}

		$response = self::request( 'theme_register', array( 'api_key' => $apiKey ) );

		if ( is_wp_error( $response ) )
		{
			return array(
				'success' => false,
				'code' => '',
				'message' => $response->get_error_message()
			);
		}

		if( $response['code'] == self::E_SUCCESS ){
			self::updateApiKey( $apiKey );
			if( isset($response['data']['demos']) && ! empty($response['data']['demos']) ) {
				self::setThemeDemosTransient( $response[ 'data' ][ 'demos' ] );
			}
			if( isset($response['data']['plugins']) && ! empty($response['data']['plugins']) ) {
				self::setThemePluginsTransient( $response[ 'data' ][ 'plugins' ] );
			}
			return array(
				'success' => true,
				'code' => self::E_SUCCESS,
				'message' => $response['message']
			);
		}

		self::setThemeCheckOption( '0x' );
		return array(
			'success' => false,
			'code' => $response['code'],
			'message' => $response['message'],
		);
	}

	public static function unlinkTheme()
	{
		$response = self::request( 'theme_unlink' );
		if ( is_wp_error( $response ) )
		{
			return array( 'message' => $response->get_error_message() );
		}

		if( $response['code'] == self::E_SUCCESS ){
			do_action( 'dash_clear_cached_data' );
			return array( 'code' => self::E_SUCCESS, 'message' => $response['message'] );
		}

		error_log( '['.$response['code'].'] '.(isset($response['message']) ? $response['message'] : '') );
		return array( 'message' => $response['message'] );
	}

	/**
	 * Check to see whether or not the theme is connected with the Hogash Dashboard
	 * @return bool
	 */
	public static function isConnected()
	{
		if( ! is_null( self::$isConnected ) ) {
			return self::$isConnected;
		}

		//#! If to use the cached info
		$info = self::getThemeCheckOption();
		return self::$isConnected = ( '1x' == $info );
	}
	//</editor-fold desc="::: DASHBOARD INTEGRATION">

	//<editor-fold desc="::: THEME DEMOS">
	/**
	 * Retrieve the list of all demos
	 * @return array
	 */
	public static function getAllDemos()
	{
		// Check transient
		$cache = self::getThemeDemosTransient();
		if ( !empty( $cache ) )
		{
			return $cache;
		}

		$response = self::request( 'get_demos' );
		if ( is_wp_error( $response ) )
		{
			return array( 'error' => $response->get_error_message() );
		}

		if( $response['code'] == self::E_SUCCESS ) {
			if( empty($response['data'])){
				return array( 'error' => __( 'No demos retrieved.', 'zn_framework' ) );
			}
			self::setThemeDemosTransient($response['data']);
			return $response['data'];
		}

		do_action( 'dash_clear_cached_data' );
		error_log( '['.$response['code'].'] '.(isset($response['message']) ? $response['message'] : '') );
		return array( 'error' => '['.$response['code'].'] '.(isset($response['message']) ? $response['message'] : '') );
	}

	/**
	 * @param string $demoName
	 * @param string $savePath
	 * @return bool
	 */
	public static function getDemo( $demoName = '', $savePath = '' )
	{
		if ( empty( $demoName ) || empty( $savePath ) )
		{
			return false;
		}

		$response = self::request( 'get_demo_url', array( 'demo' => $demoName ) );
		if ( is_wp_error( $response ) )
		{
			error_log( '['.__METHOD__.']' . $response->get_error_message() );
			return false;
		}

		if( $response['code'] == self::E_SUCCESS ) {
			if( empty($response['data'])){
				error_log( '['.__METHOD__.'] Invalid data retrieved from server.' );
				return false;
			}
			$downloadURL = trim( $response[ 'data' ] );
			if( filter_var( $downloadURL, FILTER_VALIDATE_URL ) )
			{
				$response = wp_remote_get( $downloadURL, array( 'timeout' => 120 ) );

				if( is_array( $response ) && isset( $response['body'] ) ) {
					$content = $response['body'];

					$fs = ZNHGTFW()->getComponent( 'utility' )->getFileSystem();

					if( ! empty( $content ) ) {
						$r = $fs->put_contents( $savePath, $content, 0644 );
						if ( $r ) {
							return $savePath;
						}
					}
				}
			}
		}
		error_log( '['.$response['code'].'] '.(isset($response['message']) ? $response['message'] : '') );
		return false;
	}

	/**
	 * Retrieve the information about the theme from Dashboard
	 * @return bool|mixed
	 */
	public static function getThemeInfo()
	{
		/*
		 * #! When clicking the Force check in wp-admin/updates, this code is executed twice because WordPress has a registered
		 * hook too, and the first request is not triggering the "pre_set_site_transient_update_themes" filter, only the second
		 * request, so obviously, we'd want to ignore the first request.
		 */
		if( ! isset($GLOBALS['kallyas_check_ignore_first_load'])){
			$GLOBALS['kallyas_check_ignore_first_load'] = true;
			return false;
		}

		$response = self::request( 'get_theme_info' );
		if ( is_wp_error( $response ) )
		{
			error_log( '['.__METHOD__	.'] '. $response->get_error_message());
			return false;
		}

		if( $response['code'] != self::E_SUCCESS ){
			error_log( '['.__METHOD__.'] An error occurred while trying to retrieve the information for the theme: '. ( isset($response['message']) ? $response['message'] : '' ) );
			return false;
		}

		if ( empty( $response[ 'data' ] ) )
		{
			error_log( '['.__METHOD__.'] No information retrieved for the theme.');
			return false;
		}
		return $response[ 'data' ];
	}

	/**
	 * Retrieve the download URL for the theme update archive
	 * @return bool
	 */
	public static function getThemeDownloadUrl() {
		$response = self::request( 'get_theme_download_url' );
		if ( is_wp_error( $response ) )
		{
			return array(
				'url' => '',
				'message' => $response->get_error_message()
			);
		}

		if( $response['code'] == self::E_SUCCESS ) {
			return array(
				'url' => $response['data'],
				'message' => '',
			);
		}

		error_log( '['.__METHOD__.'] Error: ' . ( isset($response['message']) ? $response['message'] : '' ) );
		return array(
			'url' => '',
			'message' => ( isset($response['message']) ? $response['message'] : __( 'An unknown error occurred.', 'zn_framework' ) )
		);

	}

	/**
	 * Retrieve the demo information from the transient
	 * @param string $demoName
	 * @return null|array
	 */
	public static function __get_demo_install_info( $demoName )
	{
		$transData = self::getThemeDemosTransient();
		if( empty($transData) ){
			return null;
		}
		foreach( $transData as $demo_name => $info )
		{
			if( strcasecmp( $demoName, $demo_name ) == 0 ){
				return $info;
			}
		}
		return null;
	}
	//</editor-fold desc="::: THEME DEMOS">

	//<editor-fold desc="::: THEME PLUGINS">
	public static function getAllPlugins()
	{
		//#! Check cache
		$cache = self::getThemePluginsTransient();
		if ( !empty( $cache ) )
		{
			return $cache;
		}

		$response = self::request( 'get_plugins' );
		if ( is_wp_error( $response ) )
		{
			return array( 'message' => $response->get_error_message() );
		}

		if( $response['code'] == self::E_SUCCESS ) {
			if( empty($response['data'])){
				return array( 'error' => __( 'No plugins retrieved.', 'zn_framework' ) );
			}
			self::setThemePluginsTransient($response['data']);
			return $response['data'];
		}

		error_log( '['.$response['code'].'] '.(isset($response['message']) ? $response['message'] : '') );
		return array( 'error' => '['.$response['code'].'] '.(isset($response['message']) ? $response['message'] : '') );
	}

	/**
	 * Retrieve the download url for the specified plugin
	 * @param string $source
	 * @return mixed String on success, false on error
	 */
	public static function getPluginDownloadUrl( $source = '' )
	{
		if ( empty( $source ) )
		{
			return false;
		}

		$response = self::request( 'get_plugin_download_url', array( 'source' => esc_attr( $source ) ) );
		if ( is_wp_error( $response ) )
		{
			error_log( '['.__METHOD__.'] Error: ' . $response->get_error_message() );
			return false;
		}

		if( $response['code'] == self::E_SUCCESS ){
			return $response['data'];
		}

		$m = ( isset( $response['message']) ? $response['message'] : '' );
		error_log( '['.$response['code'].'] ' . $m );
		return false;
	}
	//</editor-fold desc="::: THEME PLUGINS">

	//<editor-fold desc="::: UTILITY METHODS">
	/**
	 * This function will search in various places for any of the default GoDaddy files, and if any is found then we assume this is a GoDaddy hosting
	 * @return bool
	 */
	public static function isGoDaddy()
	{
		if( ! is_null(self::$_isGoDaddy)){
			return self::$_isGoDaddy;
		}

		$root = trailingslashit(ABSPATH);
		$pluginsDir = (defined('WP_CONTENT_DIR') ? trailingslashit(WP_CONTENT_DIR).'mu-plugins/' : $root.'wp-content/mu-plugins/');
		if( is_file( $root.'gd-config.php' )){
			self::$_isGoDaddy = true;
			return true;
		}
		elseif( is_dir($pluginsDir.'gd-system-plugin') || is_file($pluginsDir.'gd-system-plugin.php') ){
			self::$_isGoDaddy = true;
			return true;
		}
		elseif( class_exists('\WPaaS\Plugin') ){
			self::$_isGoDaddy = true;
			return true;
		}
		return false;
	}

	/**
	 * Execute an HTTP request
	 * @param string $action
	 * @param array $bodyArgs
	 * @param array $headerArgs
	 * @param array $requestArgs
	 * @return array|\WP_Error
	 */
	public static function request( $action, $bodyArgs = array(), $headerArgs = array(), $requestArgs = array() )
	{
		$serverUrl = ZNHGTFW()->getThemeServerUrl();
		if( empty($serverUrl) || ! filter_var( $serverUrl, FILTER_VALIDATE_URL) ) {
			return new WP_Error( 'hg_error', esc_html(__( 'Invalid Server URL provided.', 'zn_framework' )) );
		}

		//#! Can't be connected while requesting this action
		if ( !self::isConnected() && ( 'theme_register' != $action ) )
		{
			return new WP_Error( 'hg_error', esc_html(__( 'You need to connect the theme with the Hogash Dashboard in order to be able to perform this action.', 'zn_framework' )) );
		}

		if( ( isset( $bodyArgs['api_key'] ) && ! empty($bodyArgs['api_key']) ) && ( 'theme_register' == $action ) ) {
			$apiKey = $bodyArgs['api_key'];
		}
		else {
			$apiKey = self::getApiKey();
		}

		if( empty( $apiKey ) ) {
			return new WP_Error( 'hg_error', esc_html(__( 'The API Key is missing.', 'zn_framework' )) );
		}

		if( ! self::isValidApiKeyFormat( $apiKey ) ) {
			do_action( 'dash_clear_cached_data' );
			return new WP_Error( 'hg_error', esc_html(__( 'The API Key format is not valid.', 'zn_framework' )) );
		}

		//#! Fixes timeout issues when the value is set to less than 30 seconds
		$timeout = apply_filters( 'http_request_timeout', 30 );
		$timeout = ($timeout < 30) ? 30 : $timeout;

		//<editor-fold desc=":: REQUEST ARGS ::">
		$args = array(
			'timeout' => $timeout,
			'redirection' => apply_filters( 'http_request_redirection_count', 10 ),
			'sslverify' => false,
		);
		$args['body'] = array(
			'action' => $action
		);

		//#! Merge request args
		if( ! empty($requestArgs) ) {
			$args = wp_parse_args( $requestArgs, $args );
		}
		if( ! empty($bodyArgs) ) {
			$args['body'] = wp_parse_args( $bodyArgs, $args['body'] );
		}

		// theme version
		if(! isset($args['body']['version'])){
			$args['body']['version'] = ZNHGTFW()->getVersion();
		}
		//#! api version
		if(! isset($args['body']['api_version'])){
			$args['body']['api_version'] = ZNHGTFW()->getApiVersion();
		}
		//#! theme id
		if(! isset($args['body']['theme'])){
			$args['body']['theme'] = self::$_themeID;
		}
		//#! api key
		if(! isset($args['body']['api_key'])){
			$args['body']['api_key'] = $apiKey;
		}
		//#! site url
		if(! isset($args['body']['site_url'])){
			$args['body']['site_url'] = esc_url( network_home_url( '/' ) );
		}

		if( ! empty($headerArgs) ) {
			$args['headers'] = wp_parse_args( $headerArgs, array() );
		}
		//</editor-fold desc=":: REQUEST ARGS ::">

		/**
		 * @see wp-includes/class-http.php
		 * @see WP_Http::request()
		 */
		$request = wp_remote_post( ZNHGTFW()->getThemeServerUrl(), $args );

		//<editor-fold desc=":: VALIDATE REQUEST & RESPONSE ::">
		if ( is_wp_error( $request ) )
		{
			return $request;
		}

		if( isset( $request[ 'response' ][ 'code' ] ) ) {
			if ( !in_array( (int)$request[ 'response' ][ 'code' ], array( 200, 302, 304 ) ) )
			{
				$m = esc_html(
						sprintf(
							__( 'An error occurred while trying to contact %s. Please check with your hosting company and make sure they whitelist our domain. The response code was: %s', 'zn_framework' )
							,$serverUrl, $request[ 'response' ][ 'code' ] ) );
				return new WP_Error( 'hg_error', $m );
			}
		}

		$response = wp_remote_retrieve_body( $request );
		if( empty($response) ) {
			return new WP_Error( 'hg_error', esc_html( __('Invalid response from server.', 'zn_framework' ) ) );
		}
		//</editor-fold desc=":: VALIDATE REQUEST & RESPONSE ::">

		$data = json_decode( $response, true );

		if( ! isset($data['code']) ) {
			return new WP_Error( 'hg_error', esc_html( __( 'Invalid response from server.', 'zn_framework' ) ) );
		}

		return $data;
	}

	/**
	 * Check if the server can communicate with our server ZNHGTFW()->getThemeServerUrl()
	 * @return bool
	 */
	public static function checkConnection()
	{
		$response = wp_remote_post( ZNHGTFW()->getThemeServerUrl() );
		$response_code = wp_remote_retrieve_response_code( $response );

		//#! Temporary disable db cache so we can properly update the transient
		if( self::isGoDaddy() ){ wp_using_ext_object_cache( false ); }

		if ( !in_array( (int)$response_code, array( 200, 302, 304 ) ) )
		{
			set_transient( 'zn_server_connection_check', 'notok', 10 );
			return false;
		}
		set_transient( 'zn_server_connection_check', 'ok', YEAR_IN_SECONDS );
		return true;
	}

	/**
	 * Retrieve the saved API key
	 * @return string
	 */
	public static function getApiKey()
	{
		//[1] First check if managed
		if( self::isManagedApiKey() ) {
			return wp_strip_all_tags( self::getManagedApiKey() );
		}
		//[2] Secondly check the db option
		$apiKey = self::getThemeApiKeyOption();
		if( ! empty($apiKey) ) {
			return wp_strip_all_tags( $apiKey );
		}
		return '';

	}

	public static function updateApiKey( $apiKey = '' )
	{
		if ( empty( $apiKey ) )
		{
			return false;
		}
		self::setThemeCheckOption( '1x' );
		return self::setThemeApiKeyOption($apiKey);
	}

	public static function isWPMU()
	{
		return ( function_exists( 'is_multisite' ) && is_multisite() );
	}

	/**
	 * Delete the cached list of demos
	 */
	public static function clearDemosList()
	{
		self::deleteThemeDemosTransient();
	}

	/**
	 * Delete the cached list of plugins
	 */
	public static function clearPluginsList()
	{
		self::deleteThemePluginsTransient();
	}

	public static function clearCachedData()
	{
		self::clearPluginsList();
		self::clearDemosList();
		self::deleteThemeCheckOption();
		self::deleteThemeApiKeyOption();
	}

	/**
	 * Utility method that child themes can use to directly register the theme on a MultiSite installation and when the theme is not active on the main site
	 * @param string $apiKey The API Key to use for registration
	 * @since v4.9.1
	 */
	public static function directConnect( $apiKey )
	{
		if( ! function_exists('is_multisite') || ! is_multisite() ){
			error_log( __METHOD__.'() ERROR: This method can only be used on a MultiSite installation.' );
		}
		elseif( empty( $apiKey ) ) {
			error_log( __METHOD__.'() ERROR: Please provide an API Key.' );
		}
		elseif( ! self::isConnected() ) {
			$response = self::connectTheme( $apiKey );

			if( isset($response['code']) && $response[ 'code' ] == ZN_HogashDashboard::E_SUCCESS ){
				return;
			}
			else {
				error_log( __METHOD__.'() ERROR: '.var_export( $response, 1 ) );
			}
		}
	}

	//
	public static function getOptionsPrefix() {
		return self::$_themeID . '_';
	}

	public static function getThemeCheckOption() {
		if( self::isManagedApiKey() ){
			return get_site_option( self::getOptionsPrefix() . self::THEME_CHECK_OPTION );
		}
		return get_option( self::getOptionsPrefix() . self::THEME_CHECK_OPTION );
	}
	public static function getThemeDemosTransient() {
		//#! Temporary disable db cache so we can retrieve the transient
		if( self::isGoDaddy() ){ wp_using_ext_object_cache( false ); }
		return get_site_transient( self::getOptionsPrefix() . self::THEME_DEMOS_TRANSIENT );
	}
	public static function getThemePluginsTransient() {
		//#! Temporary disable db cache so we can retrieve the transient
		if( self::isGoDaddy() ){ wp_using_ext_object_cache( false ); }
		return get_site_transient( self::getOptionsPrefix() . self::THEME_PLUGINS_TRANSIENT );
	}
	public static function getThemeApiKeyOption() {
		if( self::isManagedApiKey() ){
			return get_site_option( self::getOptionsPrefix() . self::THEME_API_KEY_OPTION );
		}
		return get_option( self::getOptionsPrefix() . self::THEME_API_KEY_OPTION );
	}

	public static function setThemeCheckOption( $value ) {
		if( self::isManagedApiKey() ){
			return update_site_option( self::getOptionsPrefix() . self::THEME_CHECK_OPTION, $value );
		}
		return update_option( self::getOptionsPrefix() . self::THEME_CHECK_OPTION, $value );
	}
	public static function setThemeDemosTransient( $value ) {
		//#! Temporary disable db cache so we can retrieve the transient
		if( self::isGoDaddy() ){ wp_using_ext_object_cache( false ); }
		return set_site_transient( self::getOptionsPrefix() . self::THEME_DEMOS_TRANSIENT, $value, DAY_IN_SECONDS );
	}
	public static function setThemePluginsTransient( $value ) {
		//#! Temporary disable db cache so we can retrieve the transient
		if( self::isGoDaddy() ){ wp_using_ext_object_cache( false ); }
		return set_site_transient( self::getOptionsPrefix() . self::THEME_PLUGINS_TRANSIENT, $value, DAY_IN_SECONDS );
	}
	public static function setThemeApiKeyOption( $value ) {
		if( ! self::isValidApiKeyFormat( $value ) ){
			return false;
		}
		if( self::isManagedApiKey() ){
			return update_site_option( self::getOptionsPrefix() . self::THEME_API_KEY_OPTION, $value );
		}
		return update_option( self::getOptionsPrefix() . self::THEME_API_KEY_OPTION, $value );
	}

	public static function deleteThemeCheckOption() {
		delete_site_option( self::getOptionsPrefix() . self::THEME_CHECK_OPTION );
		return true;
	}
	public static function deleteThemeDemosTransient() {
		//#! Temporary disable db cache so we can retrieve the transient
		if( self::isGoDaddy() ){ wp_using_ext_object_cache( false ); }
		return delete_site_transient( self::getOptionsPrefix() . self::THEME_DEMOS_TRANSIENT );
	}
	public static function deleteThemePluginsTransient() {
		//#! Temporary disable db cache so we can retrieve the transient
		if( self::isGoDaddy() ){ wp_using_ext_object_cache( false ); }
		return delete_site_transient( self::getOptionsPrefix() . self::THEME_PLUGINS_TRANSIENT );
	}
	public static function deleteThemeApiKeyOption()
	{
		if ( self::isManagedApiKey() ) {
			delete_site_option( self::getOptionsPrefix() . self::THEME_API_KEY_OPTION );
		}
		delete_option( self::getOptionsPrefix() . self::THEME_API_KEY_OPTION );
		return true;
	}

	/**
	 * Check to see whether or not this is a managed API Key
	 * @return bool
	 */
	public static function isManagedApiKey()
	{
		$cName = strtoupper( ZNHGTFW()->getThemeId() ) . '_API_KEY';
		return defined( "$cName" );
	}

	/**
	 * If this is a managed API Key then retrieve its value
	 * @return string
	 */
	public static function getManagedApiKey()
	{
		$cName = strtoupper( ZNHGTFW()->getThemeId() ) . '_API_KEY';
		return (defined("$cName") ? constant($cName) : '');
	}

	/**
	 * Check to see whether or not the api key changed. Most likely this will be reached when using managed api keys.
	 * @return bool
	 */
	public static function apiKeyChanged() {
		return ( self::getApiKey() != self::getThemeApiKeyOption() );
	}

	/**
	 * Make sure this is a valid api key format
	 * @param string $apiKey
	 * @return int
	 */
	public static function isValidApiKeyFormat( $apiKey )
	{
		return preg_match( '/.{5}\-.{5}\-.{5}\-.{5}\-.{5}/iU', $apiKey );
	}
	//</editor-fold desc="::: UTILITY METHODS">
}

ZN_HogashDashboard::init();
