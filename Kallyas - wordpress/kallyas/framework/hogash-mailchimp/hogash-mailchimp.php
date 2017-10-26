<?php if ( !defined( 'ABSPATH' ) ) {
	return;
}
if( ! class_exists('Hg_Mailchimp'))
{
/*
	Plugin Name:       Hogash Mailchimp
	Description:       A plugin that will add Mailchimp functionality to all Hogash themes.
	Version:           1.0.0
	Author:            Hogash
	Author URI:        https://hogash.com/
	License:           GPLv2 or later
*/

	/**
	 * Class Hg_Mailchimp
	 *
	 * Standard singleton
	 */
	class Hg_Mailchimp
	{

		/**
		 * Holds the plugin current version
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Holds the HTTP path to the plugin's directory
		 * @var string
		 */
		public $url = '';

		/**
		 * Holds the SYSTEM path to the plugin's directory
		 * @var string
		 */
		public $path = '';

		public static $_dirPath = '';
		public static $_dirUri = '';

		/**
		 * Holds the reference to the instance of this class
		 * @var Hg_Mailchimp
		 */
		private static $instance = null;

		/**
		 * Returns the instance of the classs
		 * @return Hg_Mailchimp
		 */
		static public function get_instance()
		{
			if ( !( self::$instance instanceof self ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Hg_Mailchimp constructor.
		 */
		private function __construct()
		{
			// register text domain
			load_plugin_textdomain( 'hogash-mailchimp', false, basename( dirname( __FILE__ ) ) . '/languages' );

			$this->set_defaults();

			self::loadHgMcApiClass();

			// Add theme options
			add_filter( 'zn_theme_pages', array( $this, 'add_theme_options_page' ), 11 );
			add_filter( 'zn_theme_options', array( $this, 'add_theme_options' ), 11 );

			// refresh mailchimp lists on options save
			add_action( 'zn_save_theme_options', array( $this, 'refresh_mailchimp_lists' ) );

			// Load the mailchimp Widget
			require( $this->path . 'widget/widget-mailchimp.php' );

			// Add ajax functionality
			add_action( 'wp_ajax_nopriv_hg_mailchimp_register', array( $this, 'ajax_functionality' ) );
			add_action( 'wp_ajax_hg_mailchimp_register', array( $this, 'ajax_functionality' ) );

			// Register PB element
			add_action( 'znb:elements:register_elements', array( $this, 'register_pb_element' ) );

			// Load scripts and styles
			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
		}

		public static function loadHgMcApiClass() {
			if( ! class_exists('HG_MCAPI')) {
				//#! Load the HG_MCAPI class
				require( self::$_dirPath . 'lib/HG_MCAPI.php' );
			}
		}

		/**
		 * Setup class vars
		 */
		function set_defaults()
		{
			// Set plugin paths
			$parentDir = basename( realpath( dirname(__FILE__).'/../' ));
			if( $parentDir == 'framework' ){
				$this->url  = ZNHGTFW()->getThemeUrl('framework/hogash-mailchimp/');
				$this->path = ZNHGTFW()->getThemePath('framework/hogash-mailchimp/');
			}
			else {
				$this->url  = plugin_dir_url( __FILE__ );
				$this->path = plugin_dir_path( __FILE__ );
			}
			self::$_dirPath = $this->path;
			self::$_dirUri = $this->url;
		}

		function ajax_functionality()
		{
			$msg = '';
			if ( ( 'POST' == strtoupper( $_SERVER['REQUEST_METHOD'] ) ) && isset ( $_POST[ 'mc_email' ] ) )
			{
				//#! Validate nonce
				if( ! isset($_POST['zn_hg_mailchimp']) || ! wp_verify_nonce( $_POST['zn_hg_mailchimp'], 'zn_hg_mailchimp')) {
					$msg = '<span style="color:#ff0000;"><b>' . __( 'Error:', 'zn_framework' ) . '</b>&nbsp; ' . __( 'Error: Invalid nonce.', 'zn_framework' ) . '</span>';
				}
				else {
					$email = sanitize_email( $_POST[ 'mc_email' ] );
					$mcApiKey = zget_option( 'mailchimp_api', 'general_options' );

					if ( ! empty( $mcApiKey ) ) {

						self::loadHgMcApiClass();
						$mcapi = new HG_MCAPI( $mcApiKey );

						$merge_vars = array(
							'EMAIL' => $email
						);

						$list_id = $_POST[ 'zn_mailchimp_list' ];

						$subscribeResult = $mcapi->subscribe( $list_id, $email, $merge_vars );
						if ( $subscribeResult ) {
							$msg = '<div class="dn-alert alert alert-success">' . __( 'Success!&nbsp; Check your inbox or spam folder for a message containing a confirmation link.', 'hogash-mailchimp' ) . '</div>';
						}
						else {
							$msg = '<div class="dn-alert alert alert-danger"><strong>' . __( 'Error:', 'hogash-mailchimp' ) . '</strong>&nbsp; ' . $mcapi->errorMessage . '</div>';
						}
					}
				}
			}

			echo $msg; // Don't esc_html this, b/c we've already escaped it
			exit;
		}

		/**
		 * Register the zion page builder Newsletter element
		 * @param ZionElementsManager $elements_manager
		 */
		function register_pb_element( $elements_manager )
		{
			//#! Zion Page Builder not installed
			if ( !class_exists( 'ZionElement' ) ) {
				return;
			}
			require( $this->path . 'pb_element/newsletter/newsletter.php' );

			$elements_manager->registerElement( new ZNB_Newsletter( array(
				'id' => 'HgMcNewsletter',
				'name' => __( 'MailChimp Newsletter', 'hogash-mailchimp' ),
				'description' => __( 'This element will create a MailChimp based newsletter form.', 'hogash-mailchimp' ),
				'level' => 3,
				'category' => 'Content',
				'legacy' => false,
				'keywords' => array( 'mailing list', 'mailchimp' ),
			) ) );
		}

		function refresh_mailchimp_lists()
		{
			delete_option( 'zn_mailchimp_lists' );
		}

		function add_theme_options_page( $admin_pages )
		{
			$admin_pages[ 'general_options' ][ 'submenus' ][] = array(
				'slug' => 'mailchimp_options',
				'title' => __( 'Mailchimp options', 'hogash-mailchimp' )
			);
			return $admin_pages;
		}

		function add_theme_options( $admin_options )
		{
			include( $this->path . '/includes/options.php' );
			return $admin_options;
		}

		function add_scripts()
		{
			wp_enqueue_style( 'hg-mailchimp-styles', $this->url . 'assets/css/hg-mailchimp.css', array(), $this->version );
			wp_enqueue_script( 'hg-mailchimp-js', $this->url . 'assets/js/hg-mailchimp.js', array( 'jquery' ), $this->version, true );
			wp_localize_script( 'hg-mailchimp-js', 'hgMailchimpConfig', array(
				'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
			) );
		}
	}

	Hg_Mailchimp::get_instance();
}
