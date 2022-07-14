<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Share_Print_PDF {

	public static $version;
	public static $id;
	public static $dir;
	public static $path;
	public static $url_path;
	public static $settings;

	public static function init() {
		$class = __CLASS__;
		new $class;
	}

	function __construct() {

		if ( !class_exists( 'WooCommerce' ) ) {
			return false;
		}

		self::$version = Wcmnspp()->version();

		self::$dir = trailingslashit( Wcmnspp()->plugin_path() );
		self::$path = trailingslashit( Wcmnspp()->plugin_path() );
		self::$url_path = trailingslashit( Wcmnspp()->plugin_url() );

		$enable = get_option( 'wc_settings_spp_enable', 'override' );

		self::$settings['wc_settings_spp_counts'] = get_option( 'wc_settings_spp_counts', 'no' );

		if ( $enable == 'override' ) {
			add_filter( 'wc_get_template_part', __CLASS__ . '::add_filter', 10, 3 );
			add_filter( 'woocommerce_locate_template', __CLASS__ . '::add_loop_filter', 10, 3 );
		}
		else {
			$action = get_option( 'wc_settings_spp_action', 'woocommerce_single_product_summary:60' );
			if ( $action !== '' ) {
				$action = explode( ':', $action );
				$priority = isset( $action[1] ) ? floatval( $action[1] ) : 10;
				add_filter( $action[0], __CLASS__ . '::get_shares' , $priority );
			}
		}

		add_action( 'wp_enqueue_scripts', __CLASS__ . '::scripts' );
		add_action( 'init', __CLASS__ . '::setup_shares', 999 );

		add_action( 'wp_ajax_nopriv_wcspp_quickview', __CLASS__ . '::wcspp_quickview' );
		add_action( 'wp_ajax_wcspp_quickview', __CLASS__ . '::wcspp_quickview' );

		add_action( 'wp', __CLASS__ . '::create_settings' );

		add_shortcode( 'shareprintpdf', __CLASS__ . '::shortcode' );

		add_action( 'wp_footer', __CLASS__ . '::check_scripts' );

		add_filter( 'mnthemes_add_meta_information_used', __CLASS__ . '::sppdf_info' );

	}

	public static function sppdf_info( $val ) {
		$val = array_merge( $val, array( 'Share, Print and PDF for WooCommerce' ) );
		return $val;
	}

	public static function create_settings() {
		self::$id = get_the_ID();
	}

	public static function scripts() {

		//wp_enqueue_style( 'wcspp', self::$url_path .'lib/css/style' . ( is_rtl() ? '-rtl' : '' ) . '.css', false, self::$version );
		wp_enqueue_style( 'wcspp', self::$url_path .'lib/css/style' . ( is_rtl() ? '-rtl' : '' ) . '.min.css', false, self::$version );

		wp_register_script( 'wcspp', self::$url_path .'lib/js/scripts.js', array( 'jquery' ), self::$version, true );
		wp_enqueue_script( 'wcspp' );

	}

	public static function check_scripts() {

		if ( !isset( self::$settings['init'] ) && get_option( 'wc_settings_spp_force_scripts', 'no' ) == 'no' ) {
			wp_dequeue_script( 'wcspp' );
		}
		else if ( wp_script_is( 'wcspp', 'enqueued' ) ) {
			$args = array(
				'ajax' => admin_url( 'admin-ajax.php' ),
				'url' => self::$url_path,
				'style' => self::get_style(),
				'product_url' => get_the_permalink( self::$id ),
				'pdfmake' => self::$url_path .'lib/js/pdfmake.min.js',
				'pdffont' => self::$url_path .'lib/js/vfs_fonts.js',
				'showcounts' => self::$settings['wc_settings_spp_counts'],
				'pagesize' => apply_filters( 'wcmn_spp_pagesize', get_option( 'wc_settings_spp_pagesize', 'letter' ) ),
				'localization' => array(
					'desc' => esc_html__( 'Product Description', 'wcsppdf' ),
					'info' => esc_html__( 'Product Information', 'wcsppdf' )
				)
			);

			wp_localize_script( 'wcspp', 'wcspp', $args );
		}

	}

	public static function add_filter( $template, $slug, $name ) {

		if ( in_array( $slug, array( 'single-product/share.php' ) ) ) {

			if ( $name ) {
				$path = self::$path . WC()->template_path() . "{$slug}-{$name}.php";
			} else {
				$path = self::$path . WC()->template_path() . "{$slug}.php";
			}

			return file_exists( $path ) ? $path : $template;

		}
		else {
			return $template;
		}

	}

	public static function add_loop_filter( $template, $template_name, $template_path ) {

		if ( in_array( $template_name, array( 'single-product/share.php' ) ) ) {

			$path = self::$path . $template_path . $template_name;

			return file_exists( $path ) ? $path : $template;

		}
		else {
			return $template;
		}

	}

	public static function get_shares() {

		include( self::$dir . 'woocommerce/single-product/share.php' );

	}

	public static function setup_shares() {

		$shares = array(
			'facebook',
			'twitter',
			'google',
			'pin',
			'linked',
			'print',
			'pdf'
		);

		$disallowed = get_option( 'wc_settings_spp_shares', array() );

		$priority = 5;

		foreach( $shares as $share ) {

			if ( in_array( $share, $disallowed ) ) {
				continue;
			}

			switch( $share ) {
				case 'facebook' :
					add_action( 'wc_shareprintpdf_icons', __CLASS__ . '::get_icon_facebook', $priority );
				break;
				case 'twitter' :
					add_action( 'wc_shareprintpdf_icons', __CLASS__ . '::get_icon_twitter', $priority );
				break;
				case 'google' :
					add_action( 'wc_shareprintpdf_icons', __CLASS__ . '::get_icon_google', $priority );
				break;
				case 'pin' :
					add_action( 'wc_shareprintpdf_icons', __CLASS__ . '::get_icon_pin', $priority );
				break;
				case 'linked' :
					add_action( 'wc_shareprintpdf_icons', __CLASS__ . '::get_icon_linked', $priority );
				break;
				case 'print' :
					add_action( 'wc_shareprintpdf_icons', __CLASS__ . '::get_icon_print', $priority );
				break;
				case 'pdf' :
					add_action( 'wc_shareprintpdf_icons', __CLASS__ . '::get_icon_pdf', $priority );
				break;
				default :
				break;
			}
			

			$priority = $priority + 5;

		}

	}

	public static function get_icon_facebook() {

		$id = self::$id;
		$link = get_the_permalink( $id );
		$title = get_the_title( $id );

		$url = 'http://www.facebook.com/sharer.php?u=' . $link;

		$extras = ' data-href="' . $link . '" onclick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"';

		$icon = 'FB';
		$icon_path = self::$path . 'lib/images/facebook.svg';
		if ( file_exists( $icon_path ) ) {
			$icon = file_get_contents( $icon_path );
		}

		$share = array(
			'type' => 'facebook',
			'count' => '...',
			'url' => $url,
			'content' => $icon,
			'extras' => $extras,
			'class' => ''
		);

		self::wrap_icon( $share );
	}

	public static function get_icon_twitter() {

		$id = self::$id;
		$link = get_the_permalink( $id );
		$title = get_the_title( $id );

		$url = 'http://twitter.com/home/?status=' . $title . ' - ' . wp_get_shortlink( $id );

		$extras = ' data-count-layout="horizontal" onclick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"';

		$icon = 'TW';
		$icon_path = self::$path . 'lib/images/twitter.svg';
		if ( file_exists( $icon_path ) ) {
			$icon = file_get_contents( $icon_path );
		}

		$share = array(
			'type' => 'twitter',
			'count' => false,
			'url' => $url,
			'content' => $icon,
			'extras' => $extras,
			'class' => 'wcspp-nocounts'
		);

		self::wrap_icon( $share );
	}

	public static function get_icon_google() {

		$id = self::$id;
		$link = get_the_permalink( $id );
		$title = get_the_title( $id );


		$url = 'https://plus.google.com/share?url=' . $link;

		$extras = ' data-href="' . $link .'" data-send="false" data-layout="button_count" data-width="60" data-show-faces="false" onclick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"';

		$icon = 'G+';
		$icon_path = self::$path . 'lib/images/google.svg';
		if ( file_exists( $icon_path ) ) {
			$icon = file_get_contents( $icon_path );
		}

		$share = array(
			'type' => 'google',
			'count' => self::get_plusones( $link ),
			'url' => $url,
			'content' => $icon,
			'extras' => $extras,
			'class' => ''
		);

		self::wrap_icon( $share );
	}

	public static function get_plusones( $link ) {

		if ( self::$settings['wc_settings_spp_counts'] !== 'yes' ) {
			return false;
		}

		$expire = 600;

		$url_code = md5( $link . $expire );
		$transient = '_wcspp_cnt_gplus_' . $url_code;
		$cached =  get_transient( $transient );

		if ( $cached !== false ) {
			return $cached;
		}
		else {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $link . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			$curl_results = curl_exec ($curl);
			curl_close ($curl);
			$json = json_decode($curl_results, true);

			$count = isset( $json[0]['result']['metadata']['globalCounts']['count'] ) ? intval( $json[0]['result']['metadata']['globalCounts']['count'] ) : '...';

			if ( $count !== '...' ) {
				set_transient( $transient, $count, $expire );
			}

			return $count;
		}
	}

	public static function get_icon_pin() {

		$id = self::$id;
		$link = get_the_permalink( $id );
		$title = get_the_title( $id );
		$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'large');
		$image = $large_image_url[0];

		$url = 'http://pinterest.com/pin/create/button/?url=' . $link . '&media=' . $image .'&description=' . $title;

		$extras = ' onclick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"';

		$icon = 'PIN';
		$icon_path = self::$path . 'lib/images/pin.svg';
		if ( file_exists( $icon_path ) ) {
			$icon = file_get_contents( $icon_path );
		}

		$share = array(
			'type' => 'pin',
			'count' => self::get_pins( $link ),
			'url' => $url,
			'content' => $icon,
			'extras' => $extras,
			'class' => ''
		);

		self::wrap_icon( $share );
	}

	public static function get_pins( $link ) {

		if ( self::$settings['wc_settings_spp_counts'] !== 'yes' ) {
			return false;
		}

		$expire = 600;

		$url_code = md5( $link . $expire );
		$transient = '_wcspp_cnt_pins_' . $url_code;
		$cached =  get_transient( $transient );

		if ( $cached !== false ) {
			return $cached;
		}
		else {

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, 'https://widgets.pinterest.com/v1/urls/count.json?url=' . $link );
			curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
			curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			$cont = curl_exec($ch);
			if( curl_error( $ch ) ) {
				return '...';
			}

			$json_string = substr( $cont, 13, -1 );
			$json = json_decode( $json_string, true );

			$count = isset( $json['count'] ) ? intval( $json['count'] ) : '...';

			if ( $count !== '...' ) {
				set_transient( $transient, $count, $expire );
			}

			return $count;

		}

	}

	public static function get_icon_linked() {

		$id = self::$id;
		$link = get_the_permalink( $id );
		$title = get_the_title( $id );

		$url = 'http://www.linkedin.com/shareArticle?mini=true&amp;url=' . $link . '&amp;title=' . $title .'&amp;source=' . home_url( '/' );

		$extras = ' onclick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"';

		$icon = 'LNKD';
		$icon_path = self::$path . 'lib/images/linked.svg';
		if ( file_exists( $icon_path ) ) {
			$icon = file_get_contents( $icon_path );
		}

		$share = array(
			'type' => 'linked',
			'count' => '...',
			'url' => $url,
			'content' => $icon,
			'extras' => $extras,
			'class' => ''
		);

		self::wrap_icon( $share );
	}

	public static function get_icon_print() {

		$icon = 'PRNT';
		$icon_path = self::$path . 'lib/images/print.svg';
		if ( file_exists( $icon_path ) ) {
			$icon = file_get_contents( $icon_path );
		}

		$share = array(
			'type' => 'print',
			'count' => false,
			'url' => '#',
			'content' => $icon,
			'extras' => '',
			'class' => ''
		);

		self::wrap_icon( $share );
	}

	public static function get_icon_pdf() {

		$icon = 'PRNT';
		$icon_path = self::$path . 'lib/images/pdf.svg';
		if ( file_exists( $icon_path ) ) {
			$icon = file_get_contents( $icon_path );
		}


		$share = array(
			'type' => 'pdf',
			'count' => false,
			'url' => '#',
			'content' => $icon,
			'extras' => '',
			'class' => ''
		);

		self::wrap_icon( $share );
	}

	public static function wrap_icon( $share ) {
?>
		<li class="<?php echo 'wcspp-' . $share['type']; ?>">
			<a href="<?php echo $share['url']; ?>" class="<?php echo $share['class']; ?>"<?php echo $share['extras']; ?> target="_blank">
				<?php
					echo $share['content'];

					if ( self::$settings['wc_settings_spp_counts'] == 'no' ) {
						$share['count'] = false;
					}

					if ( $share['count'] !== false ) {
						echo '<span>' . $share['count'] . '</span>';
					}
				?>
			</a>
		</li>
<?php
	}

	public static function wcspp_quickview() {

		if ( isset( $_POST['product_id'] ) ) {

			$id = $_POST['product_id'];
			$type = $_POST['type'];

			global $product;

			$product = wc_get_product( $id );

			ob_start();
?>
			<div class="wcspp-quickview">
			<?php

				$cats = function_exists( 'wc_get_product_category_list' ) ? strip_tags( wc_get_product_category_list( $id, ', ', '', '' ) ) : strip_tags( $product->get_categories( ', ', '', '' ) );
				$tags = function_exists( 'wc_get_product_tag_list' ) ? strip_tags( wc_get_product_tag_list( $id, ', ', '', '' ) ) : strip_tags( $product->get_tags( ', ', '', '' ) );

				$site_title = get_bloginfo( 'name' );
				$site_desc = get_bloginfo( 'description' );

				$product_title = get_the_title( $id );
				$product_price = wc_price( $product->get_price() );

				$product_sku = esc_html__( 'SKU', 'wcsppdf' ) . ': ' . $product->get_formatted_name();
				$product_link = esc_html__( 'Link', 'wcsppdf' ) . ': ' . get_the_permalink( $id );

				$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'shop_catalog' );

				$product_content = strip_shortcodes( get_post_field( 'post_content', $id ) );
				$product_content = strip_tags( $product_content, '<a><ul><ol><li><p><div><img><u><i><em><b><strong><table><tbody><tr><th><td><pre><blockquote><hr><span><h1><h2><h3><h4><h5><h6>' );

				$product_description = strip_shortcodes( get_post_field( 'post_excerpt', $id ) );
				$product_description = strip_tags( $product_description, '<a><ul><ol><li><p><div><img><u><i><em><b><strong><table><tbody><tr><th><td><pre><blockquote><hr><span><h1><h2><h3><h4><h5><h6>' );

				if ( function_exists( 'wc_format_dimensions' ) ) {
					$filter = array();
					if ( !empty( $product->get_length() ) && !empty( $product->get_length() ) && !empty( $product->get_length() ) ) {
						$dimensions = array(
							'length' => $product->get_length(),
							'width'  => $product->get_width(),
							'height' => $product->get_height()
						);
						$filter = array_filter( $dimensions );
					}
					$product_dimensions = !empty( $filter ) ? wc_format_dimensions( $dimensions ) : '';
				}
				else {
					$product_dimensions = !empty( $product->get_dimensions() ) ? $product->get_dimensions() : '';
				}

				$product_weight = $product->get_weight() !== '' ? $product->get_weight() . ' ' . esc_attr( get_option( 'woocommerce_weight_unit' ) ) : '';

				$attachment_ids = method_exists( $product, 'get_gallery_image_ids' ) ? $product->get_gallery_image_ids() : $product->get_gallery_attachment_ids();
				$img = array( '', '', '', '' );
				$i = 0;
				foreach ( $attachment_ids as $attachment_id ) {
					$image = wp_get_attachment_image_src( $attachment_id, 'shop_thumbnail' );

					if ( !$image ) {
						continue;
					}
					$img[$i] = $image[0];

					if ( $i == 3 ) {
						break;
					}
					$i++;
				}

				$attributes = $product->get_attributes();
				$attribute_echo = '';
				$i=0;
				if ( !empty( $attributes ) ) {
					foreach( $attributes as $attribute ) {
						if ( empty( $attribute['is_visible'] ) || ( $attribute['is_taxonomy'] && ! taxonomy_exists( $attribute['name'] ) ) ) {
							continue;
						}

						if ( $i !== 0 ) {
							$attribute_echo .= '
';
						}
						$attribute_echo .= wc_attribute_label( $attribute['name'] ) . ': ';
						if ( $attribute['is_taxonomy'] ) {
							$values = wc_get_product_terms( $id, $attribute['name'], array( 'fields' => 'names' ) );
							$attribute_echo .= apply_filters( 'woocommerce_attribute', implode( ', ', $values ), $attribute, $values );
						} else {
							$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
							$attribute_echo .= apply_filters( 'woocommerce_attribute', implode( ', ', $values ), $attribute, $values );
						}
						$i++;
					}
				}

				$logo = get_option( 'wc_settings_spp_logo', '' );
				if ( $logo !== '' ) {
					$logo = esc_url( $logo );
				}

				$header_after = wp_strip_all_tags( strip_shortcodes( get_option( 'wc_settings_spp_header_after', '' ) ) );
				$product_before = wp_strip_all_tags( strip_shortcodes( get_option( 'wc_settings_spp_product_before', '' ) ) );
				$product_after = wp_strip_all_tags( strip_shortcodes( get_option( 'wc_settings_spp_product_after', '' ) ) );

				if ( $type == 'pdf' ) {

					$pdf_product_image = '';
					if ( isset( $product_image[0] ) && $product_image[0] !== '' ) {
						$pdf_product_image = $product_image[0];
					}

					$pdf_vars = array(
						'site_logo' => $logo,
						'site_title' => $site_title,
						'site_description' => $site_desc,
						'product_title' => $product_title,
						'product_price' => strip_tags( $product_price ),
						'product_meta' => $product_sku,
						'product_link' => $product_link,
						'product_categories' => ( !empty( $cats ) ? esc_html__( 'Categories', 'wcsppdf' ) . ': '. $cats . '' : '' ),
						'product_tags' => ( !empty( $tags ) ? esc_html__( 'Tags', 'wcsppdf' ) . ': '. $tags . '' : '' ),
						'product_image' => $pdf_product_image,
						'product_description' => wpautop( $product_description ),
						'product_attributes' => $attribute_echo,
						'product_dimensions' => $product_dimensions !== '' ? esc_html__( 'Dimensions', 'wcsppdf' ) . ': ' . $product_dimensions : '',
						'product_weight' => $product_weight !== '' ? esc_html__( 'Weight', 'wcsppdf' ) . ': ' . $product_weight : '',
						'product_img0' => $img[0],
						'product_img1' => $img[1],
						'product_img2' => $img[2],
						'product_img3' => $img[3],
						'product_content' => wpautop( $product_content ),
						'header_after' => $header_after,
						'product_before' => $product_before,
						'product_after' => $product_after
					);

					$pdf = ' data-wcspp-pdf="' . esc_attr( json_encode( $pdf_vars ) ) . '"';
				}
			?>
				<div class="wcspp-wrapper">
					<a href="#" class="wcspp-go-<?php echo $type; ?>">
					<?php
						$icon = 'IC';
						$icon_path = self::$path . 'lib/images/' . $type . '.svg';
						if ( file_exists( $icon_path ) ) {
							$icon = file_get_contents( $icon_path );
						}
						if ( $type == 'print' ) {
							echo $icon . '<span>' . esc_html__( 'Print now!', 'wcsppdf' ) . '</span>';
						}
						else {
							echo $icon . '<span>' . esc_html__( 'Download now!', 'wcsppdf' ) . '</span>';
						}
						
					?>
					</a>
					<div class="wcspp-page-wrap" <?php echo isset( $pdf ) ? $pdf : ''; ?>>
						<?php
							if ( $logo !== '' ) {
								echo '<img src="' . $logo . '" class="wcspp-logo" />';
							}
						?>
						<span class="wcspp-product-title"><?php echo $site_title; ?></span>
						<span class="wcspp-product-desc"><?php echo $site_desc; ?></span>
						<?php
							if ( $header_after !== '' ) {
								echo '<div class="wcspp-add">' . $header_after . '</div>';
							}
						?>
						<hr/>
						<?php
							if ( $product_before !== '' ) {
								echo '<div class="wcspp-add">' . $product_before . '</div>';
							}
						?>
						<h1>
							<span class="wcspp-title"><?php echo $product_title; ?></span>
							<span class="wcspp-price"><?php echo $product_price; ?></span>
						</h1>
						<div class="wcspp-meta">
							<p>
							<?php
								echo $product_sku . '<br/>';
								echo $product_link . '<br/>';
							?>
							</p>
						</div>
						<div class="wcspp-main-image">
							<?php echo $product->get_image( 'shop_catalog' ); ?>
						</div>
						<div class="wcspp-images">
						<?php
							foreach ( $attachment_ids as $attachment_id ) {
								$image = wp_get_attachment_image( $attachment_id, 'shop_thumbnail' );

								if ( !$image ) {
									continue;
								}
								echo $image;
							}
						?>
						</div>
						<div class="wcspp-description">
							<h2><?php esc_html_e( 'Product Information', 'wcsppdf' ); ?></h2>
							<hr/>
							<div class="wcspp-block-wrap">
						<?php
							if ( !empty( $cats ) ) {
								echo '<strong class="wcspp-block">' . esc_html__( 'Category', 'wcsppdf' ) . ': '. $cats . '</strong>';
							}
							if ( !empty( $tags ) ) {
								echo '<strong class="wcspp-block">' . esc_html__( 'Tags', 'wcsppdf' ) . ': ' . $tags . '</strong>';
							}
							if ( !empty( $attributes ) ) {
								foreach( $attributes as $attribute ) {
									if ( empty( $attribute['is_visible'] ) || ( $attribute['is_taxonomy'] && ! taxonomy_exists( $attribute['name'] ) ) ) {
										continue;
									}

									echo '<strong class="wcspp-block">';
									echo wc_attribute_label( $attribute['name'] ) . ': ';
									if ( $attribute['is_taxonomy'] ) {
										$values = wc_get_product_terms( $id, $attribute['name'], array( 'fields' => 'names' ) );
										echo apply_filters( 'woocommerce_attribute', implode( ', ', $values ), $attribute, $values );
									} else {
										$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
										echo apply_filters( 'woocommerce_attribute', implode( ', ', $values ), $attribute, $values );
									}
									echo '</strong>';
								}
							}
							if ( $product_dimensions !== '' ) {
								echo '<strong class="wcspp-block">' . esc_html__( 'Dimensions', 'wcsppdf' ) . ': ' . $product_dimensions . '</strong>';
							}
							if ( $product_weight !== '' ) {
								echo '<strong class="wcspp-block">' . esc_html__( 'Weight', 'wcsppdf' ) . ': ' . $product_weight . '</strong>';
							}
						?>
							</div>
							<div class="wcspp-content-short">
								<?php echo wpautop( strip_shortcodes( $product_description ) ); ?>
							</div>
						</div>
						<div class="wcspp-content">
							<h2><?php esc_html_e( 'Product Description', 'wcsppdf' ); ?></h2>
							<hr/>
							<?php echo wpautop( strip_shortcodes( $product_content ) ); ?>
						</div>
						<?php
							if ( $product_after !== '' ) {
								echo '<div class="wcspp-add">' . $product_after . '</div>';
							}
						?>
					</div>
				</div>
				<a href="javascript:void(0)" class="wcspp-quickview-close"><span class="wcspp-quickview-close-button"><?php esc_html_e( 'Click to close the preview!', 'wcsppdf' ); ?></span></a>
			</div>
<?php
			$out = ob_get_clean();

			die( $out );
			exit;
		}
		die(0);
		exit;
	}

	public static function get_style() {

		//$css = file_get_contents( self::$dir . 'lib/css/print' . ( is_rtl() ? '-rtl' : '' ) . '.css' );
		$css = file_get_contents( self::$dir . 'lib/css/print' . ( is_rtl() ? '-rtl' : '' ) . '.min.css' );

		return $css;

	}

	public static function shortcode( $atts, $content = null ) {

		global $post;

		if ( $post->post_type == 'product') {
			ob_start();
			self::get_shares();
			return ob_get_clean();
		}

		return;

	}

}

add_action( 'init', array( 'WC_Share_Print_PDF', 'init' ) );

if ( !function_exists( 'mnthemes_add_meta_information' ) ) {
	function mnthemes_add_meta_information_action() {
		$val = apply_filters( 'mnthemes_add_meta_information_used', array() );
		if ( !empty( $val ) ) {
			echo '<meta name="generator" content="' . implode( ', ', $val ) . '"/>';
		}
	}
	function mnthemes_add_meta_information() {
		add_action( 'wp_head', 'mnthemes_add_meta_information_action', 99 );
	}
	mnthemes_add_meta_information();
}

?>