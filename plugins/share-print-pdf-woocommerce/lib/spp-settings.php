<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class WC_Spp_Settings {

		public static function init() {
			add_filter( 'svx_plugins_settings', __CLASS__ . '::get_settings', 50 );
		}

		public static function get_settings( $plugins ) {

			$plugins['share_print_pdf'] = array(
				'slug' => 'share_print_pdf',
				'name' => esc_html__( 'Share, Print, PDF for WooCommerce', 'wcsppdf' ),
				'desc' => esc_html__( 'Settings page for Share, Print, PDF for WooCommerce!', 'wcsppdf' ),
				'link' => 'https://mihajlovicnenad.com/product/share-print-pdf-products-woocommerce/',
				'ref' => array(
					'name' => esc_html__( 'More plugins and themes?', 'wcsppdf' ),
					'url' => 'https://mihajlovicnenad.com/shop/'
				),
				'doc' => array(
					'name' => esc_html__( 'Documentation and Plugin Guide', 'wcsppdf' ),
					'url' => 'https://mihajlovicnenad.com/share-print-pdf/documentation/'
				),
				'sections' => array(
					'dashboard' => array(
						'name' => esc_html__( 'Dashboard', 'wcsppdf' ),
						'desc' => esc_html__( 'Dashboard Overview', 'wcsppdf' ),
					),
					'general' => array(
						'name' => esc_html__( 'General', 'wcsppdf' ),
						'desc' => esc_html__( 'General Options', 'wcsppdf' ),
					),
					'print_pdf_setup' => array(
						'name' => esc_html__( 'Print/PDF Setup', 'wcsppdf' ),
						'desc' => esc_html__( 'Print/PDF Setup Options', 'wcsppdf' ),
					),
					'installation' => array(
						'name' => esc_html__( 'Installation', 'wcsppdf' ),
						'desc' => esc_html__( 'Installation Options', 'wcsppdf' ),
					),
				),
				'settings' => array(

					'wcmn_dashboard' => array(
						'type' => 'html',
						'id' => 'wcmn_dashboard',
						'desc' => '
						<img src="' . Wcmnspp()->plugin_url() . '/lib/images/share-print-pdf-for-woocommerce-shop.png" class="svx-dashboard-image" />
						<h3 style="margin-top: 0;"><span class="dashicons dashicons-store"></span> Get plugins and themes</h3><p>Like what you see? Improve your shop even more! Use our standardized items in your Shop today and earn more. Get <a href="https://www.mihajlovicnenad.com" target="_blank">Mihajlovicnenad.com</a> plugins and themes <a href="https://www.mihajlovicnenad.com/shop/" target="_blank">here</a>.</p>
						<h3><span class="dashicons dashicons-welcome-learn-more"></span> Knowledge Base</h3><p>Find everything about <a href="https://www.mihajlovicnenad.com" target="_blank">Mihajlovicnenad.com</a> plugins and themes in our <a href="https://www.mihajlovicnenad.com/knowledge-base/" target="_blank">Knowledge Base</a>. In-depth documentation for the items, including dozens of guide videos and plugin information.</p>
						<h3><span class="dashicons dashicons-admin-tools"></span> Support</h3><p>Need support? Please use one of the support channels provided <a href="https://www.mihajlovicnenad.com/support/" target="_blank">here</a>. If you have valid support, use the Premium Support and click the Connect with Envato. Open a ticket and an agent will reply asap. Further, use the Community Forums to get help from the community.</p>
						<h3><span class="dashicons dashicons-update"></span> Automatic Updates</h3><p>To get automatic updates use the Envato Market plugin! Install this simple plugin, and you will be noted about the new updates right in your WordPress Dashboard! Get Envato Market plugin <a href="https://envato.com/market-plugin/" target="_blank">here</a>.</p>',
						'section' => 'dashboard',
					),

					'wcmn_utility' => array(
						'name' => esc_html__( 'Plugin Options', 'wcsppdf' ),
						'type' => 'utility',
						'id' => 'wcmn_utility',
						'desc' => esc_html__( 'Quick export/import, backup and restore, or just reset your optons here', 'wcsppdf' ),
						'section' => 'dashboard',
					),

					'wc_settings_spp_enable' => array(
						'name' => esc_html__( 'Installation Method', 'wcsppdf' ),
						'type' => 'select',
						'desc' => esc_html__( 'Select method for installing the Share, Print and PDF template in your Shop.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_enable',
						'autoload' => true,
						'options' => array(
							'override' => esc_html__( 'Override WooCommerce Template', 'wcsppdf' ),
							'action' => esc_html__( 'Init Action', 'wcsppdf' )
						),
						'default' => 'yes',
						'section' => 'installation'
					),
					'wc_settings_spp_action' => array(
						'name' => esc_html__( 'Init Action', 'wcsppdf' ),
						'type' => 'text',
						'desc' => esc_html__( 'Change default plugin initialization action on single product pages. Use actions done in your content-single-product.php file. Please enter action in the following format action_name:priority.', 'wcsppdf' ) . ' ( default: woocommerce_single_product_summary:60 )' . ' (default: :60)',
						'id'   => 'wc_settings_spp_action',
						'autoload' => true,
						'default' => 'woocommerce_single_product_summary:60',
						'section' => 'installation'
					),
					'wc_settings_spp_force_scripts' => array(
						'name' => esc_html__( 'Plugin Scripts', 'isbwoo' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'Check this option to enable plugin scripts in all pages. This option fixes issues in Quick Views.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_force_scripts',
						'autoload' => true,
						'default' => 'no',
						'section' => 'installation'
					),

					'wc_settings_spp_logo' => array(
						'name' => esc_html__( 'Site Logo', 'wfsm' ),
						'type' => 'text',
						'desc' => esc_html__( 'Use site logo on print and PDF templates. Paste in the logo URL.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_logo',
						'autoload' => false,
						'default' => '',
						'section' => 'general'
					),
					'wc_settings_spp_style' => array(
						'name' => esc_html__( 'Icons Style', 'wcsppdf' ),
						'type' => 'select',
						'desc' => esc_html__( 'Choose share icons style.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_style',
						'autoload' => false,
						'options' => array(
							'line-icons' => esc_html__( 'Line Icons', 'wcsppdf' ),
							'background-colors' => esc_html__( 'Background Colors', 'wcsppdf' ),
							'border-colors' => esc_html__( 'Border Colors', 'wcsppdf' ),
							'flat' => esc_html__( 'Flat', 'wcsppdf' )
							
						),
						'default' => 'line-icons',
						'section' => 'general'
					),
					'wc_settings_spp_counts' => array(
						'name' => esc_html__( 'Show Counts', 'wcsppdf' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'Use this option to show share counts where possible.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_counts',
						'autoload' => false,
						'default' => 'no',
						'section' => 'general'
					),
					'wc_settings_spp_shares' => array(
						'name' => esc_html__( 'Hide Icons', 'wcsppdf' ),
						'type' => 'multiselect',
						'desc' => esc_html__( 'Select icons to hide on your webiste.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_shares',
						'autoload' => false,
						'options' => array(
							'facebook' => esc_html__( 'Facebook', 'wcsppdf' ),
							'twitter' => esc_html__( 'Twitter', 'wcsppdf' ),
							'google' => esc_html__( 'Google', 'wcsppdf' ),
							'pin' => esc_html__( 'Pinterest', 'wcsppdf' ),
							'linked' => esc_html__( 'LinkedIn', 'wcsppdf' ),
							'print' => esc_html__( 'Print', 'wcsppdf' ),
							'pdf' => esc_html__( 'PDF', 'wcsppdf' )

						),
						'default' => array(),
						'section' => 'general',
						'class' => 'svx-selectize'
					),

					'wc_settings_spp_pagesize' => array(
						'name' => esc_html__( 'Page Size', 'wcsppdf' ),
						'type' => 'select',
						'desc' => esc_html__( 'Select PDF page format.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_pagesize',
						'autoload' => false,
						'options' => array(
							'letter' => esc_html__( 'Letter', 'wcsppdf' ),
							'legal' => esc_html__( 'Legal', 'wcsppdf' ),
							'a4' => 'A4',
							'a3' => 'A3'
						),
						'default' => 'letter',
						'section' => 'print_pdf_setup'
					),
					'wc_settings_spp_header_after' => array(
						'name' => esc_html__( 'Header After', 'wfsm' ),
						'type' => 'textarea',
						'desc' => esc_html__( 'Set custom content after header in print and PDF mode.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_header_after',
						'autoload' => false,
						'default' => '',
						'section' => 'print_pdf_setup'
					),
					'wc_settings_spp_product_before' => array(
						'name' => esc_html__( 'Product Before', 'wfsm' ),
						'type' => 'textarea',
						'desc' => esc_html__( 'Set custom content before product content in print and PDF mode.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_product_before',
						'autoload' => false,
						'default' => '',
						'section' => 'print_pdf_setup'
					),
					'wc_settings_spp_product_after' => array(
						'name' => esc_html__( 'Product After', 'wfsm' ),
						'type' => 'textarea',
						'desc' => esc_html__( 'Set custom content after product content in print and PDF mode.', 'wcsppdf' ),
						'id'   => 'wc_settings_spp_product_after',
						'autoload' => false,
						'default' => '',
						'section' => 'print_pdf_setup'
					),

				)
			);

			foreach ( $plugins['share_print_pdf']['settings'] as $k => $v ) {
				$get = isset( $v['translate'] ) ? $v['id'] . SevenVX()->language() : $v['id'];
				$std = isset( $v['default'] ) ?  $v['default'] : '';
				$set = ( $set = get_option( $get, false ) ) === false ? $std : $set;
				$plugins['share_print_pdf']['settings'][$k]['val'] = SevenVX()->stripslashes_deep( $set );
			}

			return apply_filters( 'wc_shareprintpdf_settings', $plugins );
		}

	}

	if ( isset($_GET['page'], $_GET['tab']) && ($_GET['page'] == 'wc-settings' ) && $_GET['tab'] == 'share_print_pdf' ) {
		add_action( 'init', array( 'WC_Spp_Settings', 'init' ), 100 );
	}


?>