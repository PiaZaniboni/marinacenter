<?php
/*
Plugin Name: Share, Print and PDF Products for WooCommerce
Plugin URI: https://www.mihajlovicnenad.com/product-filter
Description: Share, Print and PDF Products for Woocommerce! It is going viral! - mihajlovicnenad.com
Author: Mihajlovic Nenad
Version: 2.1.1
Requires at least: 4.5
Tested up to: 4.9.8
WC requires at least: 3.0.0
WC tested up to: 3.4.5
Author URI: https://www.mihajlovicnenad.com
Text Domain: wcsppdf
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$GLOBALS['svx'] = isset( $GLOBALS['svx'] ) && version_compare( $GLOBALS['svx'], '1.0.9') == 1 ? $GLOBALS['svx'] : '1.0.9';

if ( !class_exists( 'WC_Share_Print_PDF_Init' ) ) :

	final class WC_Share_Print_PDF_Init {

		public static $version = '2.1.1';

		protected static $_instance = null;

		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {
			do_action( 'wcmnspp_loading' );

			$this->init_hooks();

			$this->includes();

			do_action( 'wcmnspp_loaded' );
		}

		private function init_hooks() {
			add_action( 'init', array( $this, 'init' ), 0 );
			add_action( 'init', array( $this, 'load_svx' ), 100 );
			add_action( 'plugins_loaded', array( $this, 'fix_svx' ), 100 );
		}

		public function fix_svx() {
			include_once ( 'lib/svx-settings/svx-fixoptions.php' );
		}

		public function load_svx() {
			if ( $this->is_request( 'admin' ) ) {
				include_once ( 'lib/svx-settings/svx-settings.php' );
			}
		}

		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		public function includes() {

			if ( $this->is_request( 'admin' ) ) {

				include_once ( 'lib/spp-settings.php' );

			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}

		}

		public function frontend_includes() {
			include_once( 'lib/spp-frontend.php' );
		}

		public function init() {

			do_action( 'before_wcmnspp_init' );

			$this->load_plugin_textdomain();

			do_action( 'after_wcmnspp_init' );

		}

		public function load_plugin_textdomain() {

			$domain = 'wcsppdf';
			$dir = untrailingslashit( WP_LANG_DIR );
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			if ( $loaded = load_textdomain( $domain, $dir . '/plugins/' . $domain . '-' . $locale . '.mo' ) ) {
				return $loaded;
			}
			else {
				load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
			}

		}

		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		public function plugin_basename() {
			return untrailingslashit( plugin_basename( __FILE__ ) );
		}

		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}

		public static function version_check( $version = '3.0.0' ) {
			if ( class_exists( 'WooCommerce' ) ) {
				global $woocommerce;
				if( version_compare( $woocommerce->version, $version, ">=" ) ) {
					return true;
				}
			}
			return false;
		}

		public function version() {
			return self::$version;
		}

	}

	add_filter( 'svx_plugins', 'svx_share_print_pdf_add_plugin', 60 );
	add_filter( 'svx_plugins_settings_short', 'svx_share_print_pdf_add_short' );

	function svx_share_print_pdf_add_plugin( $plugins ) {

		$plugins['share_print_pdf'] = array(
			'slug' => 'share_print_pdf',
			'name' => esc_html__( 'Share, Print, PDF', 'wcsppdf' )
		);

		return $plugins;

	}
	function svx_share_print_pdf_add_short( $plugins ) {
		$plugins['share_print_pdf'] = array(
			'slug' => 'share_print_pdf',
			'settings' => array(
				'wc_settings_spp_enable' => array(
					'autoload' => true,
				),
				'wc_settings_spp_action' => array(
						'autoload' => true,
				),
				'wc_settings_spp_force_scripts' => array(
					'autoload' => true,
				),
				'wc_settings_spp_logo' => array(
					'autoload' => false,
				),
				'wc_settings_spp_style' => array(
					'autoload' => false,
				),
				'wc_settings_spp_counts' => array(
					'autoload' => false,
				),
				'wc_settings_spp_shares' => array(
					'autoload' => false,
				),
				'wc_settings_spp_pagesize' => array(
					'autoload' => false,
				),
				'wc_settings_spp_header_after' => array(
					'autoload' => false,
				),
				'wc_settings_spp_product_before' => array(
					'autoload' => false,
				),
				'wc_settings_spp_product_after' => array(
					'autoload' => false,
				),
			),
		);
		return $plugins;
	}

	function Wcmnspp() {
		return WC_Share_Print_PDF_Init::instance();
	}

	WC_Share_Print_PDF_Init::instance();


endif;



?>