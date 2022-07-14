<?php
/**
 * @package The7
 */

namespace The7\Mods\Compatibility\Elementor\Widgets\Woocommerce;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Core\Responsive\Responsive;
use Elementor\Utils;
use The7\Inc\Mods\Compatibility\WooCommerce\Front\Recently_Viewed_Products;
use The7\Mods\Compatibility\Elementor\Pro\Modules\Query_Control\The7_Group_Control_Query;
use The7\Mods\Compatibility\Elementor\Shortcode_Adapters\Query_Adapters\Products_Query;
use The7\Mods\Compatibility\Elementor\The7_Elementor_Widget_Base;
use The7\Mods\Compatibility\Elementor\Widget_Templates\Arrows;
use The7\Mods\Compatibility\Elementor\Widget_Templates\Bullets;
use The7\Mods\Compatibility\Elementor\Widget_Templates\Button;
use The7\Mods\Compatibility\Elementor\Widget_Templates\General;
use The7\Mods\Compatibility\Elementor\Widget_Templates\Woocommerce\Sale_Flash;
use The7\Mods\Compatibility\Elementor\Widget_Templates\Woocommerce\Price;
use The7\Mods\Compatibility\Elementor\The7_Elementor_Less_Vars_Decorator_Interface;
use WC_Product;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Products_Carousel class.
 */
class Products_Carousel extends Products {

	/**
	 * Get element name.
	 * Retrieve the element name.
	 *
	 * @return string The name.
	 */
	public function get_name() {
		return 'the7-wc-products-carousel';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function the7_title() {
		return __( 'Products Carousel', 'the7mk2' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function the7_icon() {
		return 'eicon-posts-carousel';
	}

	/**
	 * @return string
	 */
	protected function get_less_file_name() {
		return PRESSCORE_THEME_DIR . '/css/dynamic-less/elementor/the7-woocommerce-products-carousel.less';
	}

	/**
	 * Get script dependencies.
	 *
	 * Retrieve the list of script dependencies the element requires.
	 *
	 * @return array Element scripts dependencies.
	 */
	public function get_script_depends() {
		$scripts = [ 'the7-elementor-masonry' ];

		if ( $this->is_preview_mode() ) {
			$scripts[] = 'the7-elements-carousel-widget-preview';
		}

		return $scripts;
	}

	/**
	 * @param strig $element Element name.
	 *
	 * @return void
	 */
	protected function add_container_data_render_attributes( $element ) {
		$settings = $this->get_settings_for_display();

		$data_atts = [
			'data-scroll-mode'          => $settings['slide_to_scroll'] === 'all' ? 'page' : '1',
			'data-col-num'              => $settings['widget_columns'],
			'data-wide-col-num'         => $settings['widget_columns_wide_desktop'] ?: $settings['widget_columns'],
			'data-laptop-col'           => $settings['widget_columns_tablet'],
			'data-h-tablet-columns-num' => $settings['widget_columns_tablet'],
			'data-v-tablet-columns-num' => $settings['widget_columns_tablet'],
			'data-phone-columns-num'    => $settings['widget_columns_mobile'],
			'data-auto-height'          => $settings['adaptive_height'] ? 'true' : 'false',
			'data-col-gap'              => $settings['gap_between_posts']['size'],
			'data-col-gap-tablet'       => $settings['gap_between_posts_tablet']['size'],
			'data-col-gap-mobile'       => $settings['gap_between_posts_mobile']['size'],
			'data-speed'                => $settings['speed'],
			'data-autoplay'             => $settings['autoplay'] ? 'true' : 'false',
			'data-autoplay_speed'       => $settings['autoplay_speed'],
			'data-bullet'               => $settings['show_bullets'],
			'data-bullet_tablet'        => $settings['show_bullets_tablet'],
			'data-bullet_mobile'        => $settings['show_bullets_mobile'],
		];

		$this->add_render_attribute( $element, $data_atts );
	}

	/**
	 * Render element.
	 *
	 * Generates the final HTML on the frontend.
	 */
	protected function render() {
		$this->print_inline_css();

		$settings = $this->get_settings_for_display();

		if ( $settings['query_post_type'] === 'recently_viewed' && ! $this->is_preview_mode() ) {
			Recently_Viewed_Products::track_via_js();
		}

		// Loop query.
		$query_builder = new Products_Query( $settings, 'query_' );
		$query         = $query_builder->create();

		if ( ! $query->have_posts() ) {
			if ( $settings['query_post_type'] === 'current_query' ) {
				$this->render_nothing_found_message();
			}
			$this->remove_hooks();
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>';

		if ( $settings['show_widget_title'] === 'y' && $settings['widget_title_text'] ) {
			echo $this->display_widget_title( $settings['widget_title_text'], $settings['title_tag'] );
		}

		$this->template( Arrows::class )->add_container_render_attributes( 'inner-wrapper' );
		$this->add_container_class_render_attribute( 'inner-wrapper' );
		$this->add_container_data_render_attributes( 'inner-wrapper' );

		echo '<div ' . $this->get_render_attribute_string( 'inner-wrapper' ) . '>';

		// Related to print_render_attribute_string( 'woo_buttons_on_img' ); .
		$this->setup_woo_buttons_on_image_attributes();

		// Start loop.
		global $product;

		while ( $query->have_posts() ) {
			$query->the_post();

			$product = wc_get_product();

			if ( ! $product ) {
				continue;
			}

			$article_class = '';
			if ( $settings['image_hover_trigger'] === 'box' ) {
				$article_class = 'trigger-img-hover';
			}

			echo '<article ';
			wc_product_class(
				[
					'post',
					'visible',
					$article_class,
				]
			);
			echo ' >';
			?>

				<figure class="woocom-project">
					<div <?php $this->print_render_attribute_string( 'woo_buttons_on_img' ); ?>>

						<?php
						if ( $settings['layout'] !== 'content_below_img' || $settings['show_product_image'] ) {
							$this->render_product_image( $product );
						}
						?>

					</div>
					<figcaption class="woocom-list-content">

						<?php
						if ( $settings['show_product_title'] ) {
							$this->render_product_title( $product );
						}

						$this->template( Price::class )->render_product_price( $product );

						if ( $settings['show_rating'] && wc_review_ratings_enabled() ) {
							$price_html = wc_get_rating_html( $product->get_average_rating() );
							if ( $price_html ) {
								echo '<div class="star-rating-wrap">' . $price_html . '</div>'; // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
						}

						if ( $settings['show_short_description'] ) {
							$this->render_short_description( $product );
						}

						if ( $settings['show_add_to_cart'] && $settings['layout'] === 'content_below_img' ) {
							$this->render_add_to_cart_content_button( $product );
						}
						?>

					</figcaption>
				</figure>

			<?php
			echo '</article>';
		}

		wc_reset_loop();
		wp_reset_postdata();

		echo '</div>';

		$this->template( Arrows::class )->render();

		echo '</div>';

		$this->render_added_to_cart_icon_template();

		$this->remove_hooks();
	}

	/**
	 * Setup article wrapper attribute.
	 */
	protected function setup_article_wrapper_attributes() {
		global $post;

		$settings = $this->get_settings_for_display();

		if ( $settings['image_hover_trigger'] === 'box' ) {
			$class[] = 'trigger-img-hover';
		}
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {
		// Content.
		$this->add_query_controls();
		$this->add_layout_content_controls();
		$this->add_content_controls();
		$this->add_scrolling_controls();
		$this->template( Arrows::class )->add_content_controls();
		$this->template( Bullets::class )->add_content_controls();

		// Style.
		$this->add_widget_title_style_controls();
		$this->template( General::class )->add_box_style_controls();
		$this->add_image_style_controls();
		$this->add_content_style_controls();
		$this->template( Sale_Flash::class )->add_style_controls();
		$this->add_icon_on_image_style_controls();
		$this->add_title_style_controls();
		$this->template( Price::class )->add_style_controls();
		$this->add_rating_style_controls();
		$this->add_short_description_style_controls();
		$this->template( Button::class )->add_style_controls(
			Button::ICON_SWITCHER,
			[
				'layout'           => 'content_below_img',
				'show_add_to_cart' => 'y',
			]
		);
		$this->template( Arrows::class )->add_style_controls();
		$this->template( Bullets::class )->add_style_controls();
	}

	/**
	 * Add query controls.
	 */
	protected function add_query_controls() {
		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_group_control(
			The7_Group_Control_Query::get_type(),
			[
				'name'            => 'query',
				'query_post_type' => 'product',
				'presets'         => [ 'include', 'exclude', 'order' ],
				'fields_options'  => [
					'post_type' => [
						'default' => 'product',
						'options' => [
							'current_query'   => __( 'Current Query', 'the7mk2' ),
							'product'         => __( 'Latest Products', 'the7mk2' ),
							'sale'            => __( 'Sale', 'the7mk2' ),
							'top'             => __( 'Top rated products', 'the7mk2' ),
							'best_selling'    => __( 'Best selling', 'the7mk2' ),
							'featured'        => __( 'Featured', 'the7mk2' ),
							'by_id'           => _x( 'Manual Selection', 'Posts Query Control', 'the7mk2' ),
							'related'         => __( 'Related Products', 'the7mk2' ),
							'recently_viewed' => __( 'Recently Viewed', 'the7mk2' ),
						],
					],
					'orderby'   => [
						'default' => 'date',
						'options' => [
							'date'       => __( 'Date', 'the7mk2' ),
							'title'      => __( 'Title', 'the7mk2' ),
							'price'      => __( 'Price', 'the7mk2' ),
							'popularity' => __( 'Popularity', 'the7mk2' ),
							'rating'     => __( 'Rating', 'the7mk2' ),
							'rand'       => __( 'Random', 'the7mk2' ),
							'menu_order' => __( 'Menu Order', 'the7mk2' ),
						],
					],
					'exclude'   => [
						'options' => [
							'current_post'     => __( 'Current Post', 'the7mk2' ),
							'manual_selection' => __( 'Manual Selection', 'the7mk2' ),
							'terms'            => __( 'Term', 'the7mk2' ),
						],
					],
					'include'   => [
						'options' => [
							'terms' => __( 'Term', 'the7mk2' ),
						],
					],
				],
				'exclude'         => [
					'posts_per_page',
					'exclude_authors',
					'authors',
					'offset',
					'related_fallback',
					'related_ids',
					'query_id',
					'avoid_duplicates',
					'ignore_sticky_posts',
				],
			]
		);

		$this->add_control(
			'dis_posts_total',
			[
				'label'       => __( 'Total Number Of Posts', 'the7mk2' ),
				'description' => __( 'Leave empty to display all posts.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '12',
				'condition'   => [
					'query_post_type!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'posts_offset',
			[
				'label'       => __( 'Posts Offset', 'the7mk2' ),
				'description' => __(
					'Offset for posts query (i.e. 2 means, posts will be displayed starting from the third post).',
					'the7mk2'
				),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'condition'   => [
					'query_post_type!' => 'current_query',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add scrolling controls.
	 */
	protected function add_scrolling_controls() {
		$this->start_controls_section(
			'scrolling_section',
			[
				'label' => __( 'Scrolling', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'slide_to_scroll',
			[
				'label'   => __( 'Scroll Mode', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'single',
				'options' => [
					'single' => 'One slide at a time',
					'all'    => 'All slides',
				],
			]
		);

		$this->add_control(
			'speed',
			[
				'label'   => __( 'Transition Speed (ms)', 'the7mk2' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => '600',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Autoplay Slides', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'y',
				'default'      => '',
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label'     => __( 'Autoplay Speed (ms)', 'the7mk2' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6000,
				'min'       => 100,
				'max'       => 10000,
				'step'      => 10,
				'condition' => [
					'autoplay' => 'y',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register layout controls.
	 */
	protected function add_layout_content_controls() {
		$this->start_controls_section(
			'layout_content_section',
			[
				'label' => __( 'Layout', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_widget_title',
			[
				'label'        => __( 'Widget Title', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => '',
			]
		);

		$this->add_control(
			'widget_title_text',
			[
				'label'     => __( 'Title', 'the7mk2' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Widget title',
				'condition' => [
					'show_widget_title' => 'y',
				],
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'     => __( 'Title HTML Tag', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				],
				'default'   => 'h3',
				'condition' => [
					'show_widget_title' => 'y',
				],
			]
		);

		$this->add_control(
			'widget_columns_wide_desktop',
			[
				'label'       => __( 'Columns On A Wide Desktop', 'the7mk2' ),
				'description' => the7_elementor_get_wide_columns_control_description(),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'min'         => 1,
				'max'         => 12,
				'separator'   => 'before',
			]
		);

		$this->add_basic_responsive_control(
			'widget_columns',
			[
				'label'          => __( 'Columns', 'the7mk2' ),
				'type'           => Controls_Manager::NUMBER,
				'default'        => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'min'            => 1,
				'max'            => 12,
			]
		);

		$this->add_basic_responsive_control(
			'gap_between_posts',
			[
				'label'      => __( 'Columns Gap (px)', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => '40',
				],
				'range'      => [
					'px' => [
						'max' => 100,
					],
				],
			]
		);
		$this->add_basic_responsive_control(
			'carousel_margin',
			[
				'label'       => __( 'outer gaps', 'the7mk2' ),
				'type'        => Controls_Manager::DIMENSIONS,
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .owl-stage, {{WRAPPER}} .owl-carousel' => '--stage-top-gap:{{TOP}}{{UNIT}}; --stage-right-gap:{{RIGHT}}{{UNIT}};  --stage-left-gap:{{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .owl-stage-outer' => ' padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'adaptive_height',
			[
				'label'        => __( 'Adaptive Height', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'y',
				'default'      => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Setup wrapper class attribute.
	 */
	protected function setup_wrapper_class() {
		$class = [
			'products-shortcode',
			'loading-effect-none',
		];

		$settings = $this->get_settings_for_display();

		// Unique class.
		$class[] = $this->get_unique_class();

		$class[] = the7_array_match(
			$settings['layout'],
			[
				'content_below_img' => 'cart-btn-below-img',
				'btn_on_img'        => 'cart-btn-on-img',
			]
		);

		$class[] = the7_array_match(
			$settings['image_hover_style'],
			[
				'quick_scale' => 'quick-scale-img',
				'slow_scale'  => 'scale-img',
				'hover_image' => 'wc-img-hover',
			]
		);

		$this->add_render_attribute( 'wrapper', 'class', $class );
	}

	/**
	 * @param string $element Render element.
	 */
	protected function add_container_class_render_attribute( $element ) {

		$class = [
			'owl-carousel',
			'the7-elementor-widget',
			'the7-products-carousel',
			'elementor-owl-carousel-call',
			'loading-effect-none',
			'classic-layout-list',
		];

		// Unique class.
		$class[] = $this->get_unique_class();

		$settings = $this->get_settings_for_display();

		$class[] = the7_array_match(
			$settings['layout'],
			[
				'content_below_img' => 'cart-btn-below-img',
				'btn_on_img'        => 'cart-btn-on-img',
			]
		);

		$class[] = the7_array_match(
			$settings['image_hover_style'],
			[
				'quick_scale' => 'quick-scale-img',
				'slow_scale'  => 'scale-img',
				'hover_image' => 'wc-img-hover',
			]
		);

		$class[] = the7_array_match(
			$settings['bullets_style'],
			[
				'scale-up'         => 'bullets-scale-up',
				'stroke'           => 'bullets-stroke',
				'fill-in'          => 'bullets-fill-in',
				'small-dot-stroke' => 'bullets-small-dot-stroke',
				'ubax'             => 'bullets-ubax',
				'etefu'            => 'bullets-etefu',
			]
		);

		if ( $settings['product_title_width'] === 'crp-to-line' ) {
			$class[] = 'title-to-line';
		}

		if ( $settings['description_width'] === 'crp-to-line' ) {
			$class[] = 'desc-to-line';
		}

		if ( ! $settings['show_product_image'] ) {
			$class[] = 'hide-product-image';
		}

		$this->add_render_attribute( $element, 'class', $class );
	}

	/**
	 * @param  The7_Elementor_Less_Vars_Decorator_Interface $less_vars Less vars manager.
	 */
	protected function less_vars( The7_Elementor_Less_Vars_Decorator_Interface $less_vars ) {
		$settings = $this->get_settings_for_display();

		$less_vars->add_keyword(
			'unique-shortcode-class-name',
			$this->get_unique_class() . '.the7-products-carousel',
			'~"%s"'
		);

		foreach ( Responsive::get_breakpoints() as $size => $value ) {
			$less_vars->add_pixel_number( "elementor-{$size}-breakpoint", $value );
		}

		$this->template( Arrows::class )->add_less_vars( $less_vars );

		$less_vars->add_rgba_color( 'bullet-color', $settings['bullet_color'] );
		$less_vars->add_rgba_color( 'bullet-color-hover', $settings['bullet_color_hover'] );
		$less_vars->add_keyword( 'bullets-v-position', $settings['bullets_v_position'] );
		$less_vars->add_keyword( 'bullets-h-position', $settings['bullets_h_position'] );
		$less_vars->add_pixel_number( 'bullet-v-position', $settings['bullets_v_offset'] );
		$less_vars->add_pixel_number( 'bullet-h-position', $settings['bullets_h_offset'] );
	}
}
