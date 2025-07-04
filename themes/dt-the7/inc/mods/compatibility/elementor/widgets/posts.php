<?php
/**
 * The7 elements widget for Elementor.
 *
 * @package The7
 */

namespace The7\Mods\Compatibility\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Responsive\Responsive;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;
use Elementor\Modules\DynamicTags\Module as TagsModule;
use The7\Mods\Compatibility\Elementor\Style\Posts_Masonry_Style;
use The7\Mods\Compatibility\Elementor\The7_Elementor_Less_Vars_Decorator_Interface;
use The7\Mods\Compatibility\Elementor\The7_Elementor_Widget_Base;
use The7\Mods\Compatibility\Elementor\Widget_Templates\Button;
use The7\Mods\Compatibility\Elementor\Widget_Templates\Pagination;
use The7\Mods\Compatibility\Elementor\With_Post_Excerpt;
use The7_Categorization_Request;
use The7_Query_Builder;
use The7_Related_Query_Builder;

defined( 'ABSPATH' ) || exit;

class Posts extends The7_Elementor_Widget_Base {

	use With_Post_Excerpt;
	use Posts_Masonry_Style;

	/**
	 * Get element name.
	 *
	 * Retrieve the element name.
	 *
	 * @return string The name.
	 */
	public function get_name() {
		return 'the7_elements';
	}

	protected function the7_title() {
		return __( 'Posts Masonry & Grid', 'the7mk2' );
	}

	protected function the7_icon() {
		return 'eicon-posts-grid';
	}

	public function get_script_depends() {
		$scripts = [];

		if ( $this->is_preview_mode() ) {
			$scripts[] = 'the7-elements-widget-preview';
		}

		$scripts[] = 'the7-elementor-masonry';

		return $scripts;
	}

	/**
	 * Register widget assets.
	 *
	 * @see The7_Elementor_Widget_Base::__construct()
	 */
	protected function register_assets() {
		the7_register_style(
			$this->get_name(),
			PRESSCORE_THEME_URI . '/css/compatibility/elementor/the7-elements-widget.css',
			[ 'the7-filter-decorations-base', 'the7-simple-common' ]
		);
	}

	public function get_style_depends() {
		return [ $this->get_name() ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {
		// Content Tab.
		$this->add_query_content_controls();
		$this->add_layout_content_controls();
		$this->add_content_controls();
		$this->add_filter_bar_content_controls();
		$this->template( Pagination::class )->add_content_controls( 'post_type' );

		$this->start_injection(
			[
				'type' => 'control',
				'at'   => 'before',
				'of'   => 'loading_mode',
			]
		);

		$this->add_control(
			'standard_pagination_mode_description',
			[
				'raw'             => esc_html__( 'Filter and pagination with page reloading.', 'the7mk2' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => [
					'loading_mode' => 'standard',
				],
			]
		);

		$this->end_injection();

		// Style Tab.
		$this->add_skin_style_controls();
		$this->add_box_style_controls();
		$this->add_image_style_controls();
		$this->add_hover_icon_style_controls();
		$this->add_content_style_controls();
		$this->add_post_title_style_controls();
		$this->add_post_meta_style_controls();
		$this->add_text_style_controls();
		$this->template( Button::class )->add_style_controls(
			Button::ICON_MANAGER,
			[
				'show_read_more_button' => 'y',
			],
			[
				'button_icon'      => [
					'default' => [
						'value'   => 'dt-icon-the7-arrow-552',
						'library' => 'the7-icons',
					],
				],
				'gap_above_button' => [
					'label'     => __( 'Spacing Above Button', 'the7mk2' ),
					'selectors' => [
						'{{WRAPPER}} .box-button' => 'margin-top: {{SIZE}}{{UNIT}};',
					],
				],
			]
		);
		$this->add_filter_bar_style_controls();
		$this->template( Pagination::class )->add_style_controls( 'post_type' );

		/**
		 * Inject archive posts per page control.
		 */
		$this->start_injection(
			[
				'type' => 'control',
				'at'   => 'before',
				'of'   => 'loading_mode',
			]
		);

		/**
		 * Add archive posts_per_page setting.
		 *
		 * @see Custom_Pagination_Query_Handler::handle_archive_and_search_posts_per_page()
		 */
		$this->add_control(
			'archive_posts_per_page',
			[
				'label'       => __( 'Number Of Posts On One Page', 'the7mk2' ),
				'description' => __( 'Leave empty to display default archive posts amount.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'post_type' => 'current_query',
				],
			]
		);

		$this->end_injection();
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( ! in_array( $settings['post_type'], [ 'current_query', 'related' ] ) && ! post_type_exists( $settings['post_type'] ) ) {
			echo the7_elementor_get_message_about_disabled_post_type();

			return;
		}
		// Force disable pagination for related posts.
		if ( $settings['post_type'] === 'related' ) {
			$this->template( Pagination::class )->set_loading_mode( 'disabled' );
		}

		$terms = [];
		if ( $this->filter_is_visible() ) {
			$terms = $this->get_posts_filter_terms( $settings['taxonomy'], $settings['terms'] );
		}

		$request = new The7_Categorization_Request();

		// Do local filtering if there are no global (no taxonomy in request).
		if ( ! $request->taxonomy ) {
			// Turn off query filtering if current_query.
			// Turn off query filtering if not use_filter_request().
			if ( $settings['post_type'] === 'current_query' || ! $this->use_filter_request() ) {
				$request = null;
			} elseif ( isset( $terms[0] ) && ! $this->filter_show_all() && ! $request->get_first_term() ) {
				// Force filter by terms[0] if there are no requested term or show all button.
				$request->filter_by_term( $terms[0]->term_id );
			}
		}

		$query = $this->get_query( $request );

		if ( ! $query->have_posts() ) {
			if ( $settings['post_type'] === 'current_query' ) {
				$this->render_nothing_found_message();
			}
			return;
		}

		$this->remove_image_hooks();
		$this->print_inline_css();

		$this->add_container_attributes( 'wrapper' );
		$this->template( Pagination::class )->add_containter_attributes( 'wrapper' );

		echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Posts filter.
		$this->display_filter( $terms, $request );

		echo '<div class="' . esc_attr( $this->is_masonry_layout( $settings ) ? 'iso-container dt-isotope custom-iso-columns' : 'dt-css-grid custom-pagination-handler' ) . '">';

		$data_post_limit        = $this->template( Pagination::class )->get_post_limit();
		$is_overlay_post_layout = $this->is_overlay_post_layout( $settings );

		while ( $query->have_posts() ) {
			$query->the_post();

			// Post is visible on the first page.
			$visibility = 'visible';
			if ( $data_post_limit >= 0 && $query->current_post >= $data_post_limit ) {
				$visibility = 'hidden';
			}

			$post_class_array = [
				'post',
				'visible',
			];

			if ( ! has_post_thumbnail() ) {
				$post_class_array[] = 'no-img';
			}

			$icons_html = $this->get_hover_icons_html_template( $settings );
			if ( ! $icons_html && $is_overlay_post_layout ) {
				$post_class_array[] = 'forward-post';
			}

			echo '<div ' . $this->masonry_item_wrap_class( $visibility ) . presscore_tpl_masonry_item_wrap_data_attr() . '>';
			echo '<article class="' . esc_attr( implode( ' ', get_post_class( $post_class_array ) ) ) . '" data-name="' . esc_attr( get_the_title() ) . '" data-date="' . esc_attr( get_the_date( 'c' ) ) . '">';

			$details_btn = '';
			if ( $settings['show_read_more_button'] ) {
				$details_btn = $this->get_details_btn( $settings );
			}

			$post_title = '';
			if ( $settings['show_post_title'] ) {
				$post_title = $this->get_post_title( $settings, $settings['title_tag'] );
			}

			$post_excerpt = '';
			if ( $settings['post_content'] === 'show_excerpt' ) {
				$post_excerpt = $this->get_post_excerpt( $settings['excerpt_words_limit'] );
			}

			$link_attributes = $this->get_link_attributes( $settings );

			presscore_get_template_part(
				'elementor',
				'the7-elements/tpl-layout',
				$settings['post_layout'],
				[
					'settings'     => $settings,
					'post_title'   => $post_title,
					'post_media'   => $this->get_post_image( $settings ),
					'post_meta'    => $this->get_post_meta_html_based_on_settings( $settings ),
					'details_btn'  => $details_btn,
					'post_excerpt' => $post_excerpt,
					'icons_html'   => $icons_html,
					'follow_link'  => $link_attributes['href'],
				]
			);

			echo '</article>';
			echo '</div>';
		}

		wp_reset_postdata();

		echo '</div><!-- iso-container|iso-grid -->';

		$this->template( Pagination::class )->render( $query->max_num_pages );

		echo '</div>';

		$this->add_image_hooks();
	}

	protected function get_details_btn( $settings ) {
		// Cleanup button render attributes.
		$this->remove_render_attribute( 'box-button' );

		$link_attributes = $this->get_link_attributes( $settings );

		$tag = 'button';
		if ( $link_attributes['href'] ) {
			$tag                           = 'a';
			$link_attributes['aria-label'] = the7_get_read_more_aria_label();
			$this->add_render_attribute( 'box-button', $link_attributes );
		}

		ob_start();
		$this->template( Button::class )->render_button( 'box-button', esc_html( $settings['read_more_button_text'] ), $tag );

		return ob_get_clean();
	}

	protected function masonry_item_wrap_class( $class = array() ) {
		global $post;

		if ( ! is_array( $class ) ) {
			$class = explode( ' ', $class );
		}

		$settings = $this->get_settings_for_display();

		$class[] = 'wf-cell';

		if ( $this->current_post_is_wide( $settings ) ) {
			$class[] = 'double-width';
		}

		if ( $this->is_masonry_layout( $settings ) ) {
			$class[] = 'iso-item';
		}

		if ( $this->filter_is_visible() ) {
			$terms = get_the_terms( $post->ID, $settings['taxonomy'] );
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$class[] = sanitize_html_class( 'category-' . $term->term_id );
				}
			} else {
				$class[] = 'category-0';
			}
		}

		return 'class="' . esc_attr( implode( ' ', $class ) ) . '" ';
	}

	/**
	 * Add container class attribute.
	 *
	 * @param string $element Elementor element.
	 */
	protected function add_container_attributes( $element ) {
		$class   = [];
		$class[] = 'portfolio-shortcode';
		$class[] = 'the7-elementor-widget';

		// Unique class.
		$class[] = $this->get_unique_class();

		$settings = $this->get_settings_for_display();

		$class[] = $this->is_masonry_layout( $settings ) ? 'mode-masonry' : 'mode-grid dt-css-grid-wrap';

		$layout_classes = array(
			'classic'           => 'classic-layout-list',
			'bottom_overlap'    => 'bottom-overlap-layout-list',
			'gradient_overlap'  => 'gradient-overlap-layout-list',
			'gradient_overlay'  => 'gradient-overlay-layout-list',
			'gradient_rollover' => 'content-rollover-layout-list',
		);

		$layout = $settings['post_layout'];
		if ( array_key_exists( $layout, $layout_classes ) ) {
			$class[] = $layout_classes[ $layout ];
		}

		if ( in_array( $settings['post_layout'], [ 'gradient_overlay', 'gradient_rollover' ], true ) ) {
			$class[] = 'description-on-hover';
		} else {
			$class[] = 'description-under-image';
		}

		if ( $settings['image_scale_animation_on_hover'] === 'quick_scale' ) {
			$class[] = 'quick-scale-img';
		} elseif ( $settings['image_scale_animation_on_hover'] === 'slow_scale' ) {
			$class[] = 'scale-img';
		}

		if ( ! $settings['post_date'] && ! $settings['post_terms'] && ! $settings['post_comments'] && ! $settings['post_author'] ) {
			$class[] = 'meta-info-off';
		}

		$gradient_layout = in_array( $layout, [ 'gradient_overlay', 'gradient_rollover' ], true );
		if ( $gradient_layout && $settings['post_content'] !== 'show_excerpt' && ! $settings['show_read_more_button'] ) {
			$class[] = 'disable-layout-hover';
		}

		$class[] = 'content-bg-on';

		if ( ! $settings['overlay_background_background'] && ! $settings['overlay_hover_background_background'] ) {
			$class[] = 'enable-bg-rollover';
		}

		if ( 'browser_width_based' === $settings['responsiveness'] ) {
			$class[] = 'resize-by-browser-width';
		}

		if ( $settings['show_orderby_filter'] || $settings['show_order_filter'] || $this->filter_is_visible() ) {
			$class[] = 'widget-with-filter';
		}

		$class[] = presscore_tpl_get_load_effect_class( $settings['loading_effect'] );

		if ( 'gradient_overlay' === $settings['post_layout'] ) {
			$class[] = presscore_tpl_get_hover_anim_class( $settings['go_animation'] );
		}

		$attributes = [
			'class'        => $class,
			'data-padding' => esc_attr( $this->combine_slider_value( $settings['gap_between_posts'] ) ),
		];

		$target_width = $settings['pwb_column_min_width'];
		if ( ! empty( $target_width['size'] ) ) {
			$attributes['data-width'] = absint( $target_width['size'] );
		}

		if ( ! empty( $settings['pwb_columns'] ) ) {
			$attributes['data-columns'] = absint( $settings['pwb_columns'] );
		}

		if ( 'browser_width_based' === $settings['responsiveness'] ) {
			$columns = [
				'mobile'       => $settings['widget_columns_mobile'],
				'tablet'       => $settings['widget_columns_tablet'],
				'desktop'      => $settings['widget_columns'],
				'wide-desktop' => $settings['widget_columns_wide_desktop'] ?: $settings['widget_columns'],
			];

			foreach ( $columns as $column => $val ) {
				$attributes[ 'data-' . $column . '-columns-num' ] = esc_attr( $val );
			}
		}

		$this->add_render_attribute( $element, $attributes );
	}

	protected function display_filter( $terms, $request ) {
		$settings     = $this->get_settings_for_display();
		$loading_mode = $this->get_loading_mode();
		$filter_class = [ 'iso-filter', 'filter-decorations' ];

		if ( $loading_mode === 'standard' ) {
			$filter_class[] = 'without-isotope';
		}

		if ( ! $this->is_masonry_layout( $settings ) ) {
			$filter_class[] = 'css-grid-filter';
		}

		$show_order   = ! ( empty( $settings['show_order_filter'] ) && empty( $settings['show_order_filter_tablet'] ) && empty( $settings['show_order_filter_mobile'] ) );
		$show_orderby = ! ( empty( $settings['show_orderby_filter'] ) && empty( $settings['show_orderby_filter_tablet'] ) && empty( $settings['show_orderby_filter_mobile'] ) );

		if ( ! $show_order && ! $show_orderby ) {
			$filter_class[] = 'extras-off';
		}
		if ( ! empty( $settings['show_order_filter_tablet'] ) ) {
			$filter_class[] = 'extras-tablet-on';
		}
		if ( ! empty( $settings['show_order_filter_mobile'] ) ) {
			$filter_class[] = 'extras-mobile-on';
		}

		$filter_class[] = 'filter';

		if ( $settings['filter_style'] ) {
			$filter_class[] = 'filter-pointer-' . $settings['filter_style'];

			foreach ( $settings as $key => $value ) {
				if ( 0 === strpos( $key, 'animation' ) && $value ) {
					$filter_class[] = 'filter-animation-' . $value;
					break;
				}
			}
		}
		$filter_class[] = the7_array_match(
			$settings['show_categories_filter'],
			[
				'show' => 'show-filter-categories',
				'hide' => 'hide-filter-categories',
			]
		);
		$filter_class[] = the7_array_match(
			$settings['show_categories_filter_tablet'],
			[
				'show' => 'show-filter-categories-tablet',
				'hide' => 'hide-filter-categories-tablet',
			]
		);
		$filter_class[] = the7_array_match(
			$settings['show_categories_filter_mobile'],
			[
				'show' => 'show-filter-categories-mobile',
				'hide' => 'hide-filter-categories-mobile',
			]
		);

		if ( $settings['filter_style'] === 'default' ) {
			$filter_style = of_get_option( 'general-filter_style' );
			if ( $filter_style === 'minimal' ) {
				$filter_class[] = 'filter-bg-decoration';
			} elseif ( $filter_style === 'material' ) {
				$filter_class[] = 'filter-underline-decoration';
			} else {
				$filter_class[] = 'filter-without-decoration';
			}
		}

		$current_term = 'all';

		$sorting_args = [
			'show_order'      => $show_order,
			'show_orderby'    => $show_orderby,
			'order'           => $settings['order'],
			'orderby'         => $settings['orderby'],
			'default_order'   => $settings['order'],
			'default_orderby' => $settings['orderby'],
			'select'          => 'all',
			'term_id'         => 'none',
		];

		if ( is_object( $request ) && $request->not_empty() && $this->use_filter_request() ) {
			if ( $request->order ) {
				$sorting_args['order'] = $request->order;
			}

			if ( $request->orderby ) {
				$sorting_args['orderby'] = $request->orderby;
			}

			$sorting_args['select']  = 'only';
			$sorting_args['term_id'] = $request->get_first_term();
			$current_term            = $request->get_first_term();
		}

		$args_filter_priority = has_filter(
			'presscore_get_category_list-args',
			'presscore_filter_categorizer_current_arg'
		);
		remove_filter(
			'presscore_get_category_list-args',
			'presscore_filter_categorizer_current_arg',
			$args_filter_priority
		);

		presscore_get_category_list(
			[
				'data'       => [
					'terms'       => $terms,
					'all_count'   => false,
					'other_count' => false,
				],
				'hash'       => [ 'term' => '%TERM_SLUG%' ],
				'class'      => implode( ' ', $filter_class ),
				'item_class' => 'filter-item',
				'all_class'  => 'show-all filter-item',
				'sorting'    => $sorting_args,
				'all_btn'    => $this->filter_show_all(),
				'all_text'   => $settings['filter_all_text'],
				'current'    => $current_term,
			]
		);

		$args_filter_priority !== false && add_filter(
			'presscore_get_category_list-args',
			'presscore_filter_categorizer_current_arg',
			$args_filter_priority
		);
	}

	/**
	 * @return string
	 */
	protected function get_loading_mode() {
		$settings = $this->get_settings_for_display();

		// Only standard pagination for current query.
		if ( $settings['post_type'] === 'current_query' ) {
			return 'standard';
		}

		return $settings['loading_mode'];
	}

	protected function filter_show_all() {
		return $this->get_settings_for_display( 'filter_show_all' );
	}

	/**
	 * Return shortcode less file absolute path to output inline.
	 *
	 * @return string
	 */
	protected function get_less_file_name() {
		return PRESSCORE_THEME_DIR . '/css/dynamic-less/elementor/the7-elements-widget.less';
	}

	/**
	 * Return less imports.
	 *
	 * @return array
	 */
	protected function get_less_imports() {
		$settings           = $this->get_settings_for_display();
		$dynamic_import_top = [];

		switch ( $settings['post_layout'] ) {
			case 'gradient_overlap':
				$dynamic_import_top[] = 'post-layouts/gradient-overlap-layout.less';
				break;
			case 'gradient_overlay':
				$dynamic_import_top[] = 'post-layouts/gradient-overlay-layout.less';
				break;
			case 'gradient_rollover':
				$dynamic_import_top[] = 'post-layouts/content-rollover-layout.less';
				break;
			case 'classic':
			default:
				$dynamic_import_top[] = 'post-layouts/classic-layout.less';
		}

		$dynamic_import_bottom = [];
		if ( ! $this->is_masonry_layout( $settings ) ) {
			$dynamic_import_bottom[] = 'grid.less';
		}

		return compact( 'dynamic_import_top', 'dynamic_import_bottom' );
	}

	/**
	 * Return array of prepared less vars to insert to less file.
	 *
	 * @return array
	 */
	protected function less_vars( The7_Elementor_Less_Vars_Decorator_Interface $less_vars ) {
		$settings = $this->get_settings_for_display();

		$less_vars->add_keyword(
			'unique-shortcode-class-name',
			$this->get_unique_class() . '.portfolio-shortcode',
			'~"%s"'
		);

		if ( 'browser_width_based' === $settings['responsiveness'] ) {
			$columns = array(
				'desktop'      => $settings['widget_columns'],
				'tablet'       => $settings['widget_columns_tablet'],
				'mobile'       => $settings['widget_columns_mobile'],
				'wide-desktop' => $settings['widget_columns_wide_desktop'] ?: $settings['widget_columns'],
			);

			foreach ( $columns as $column => $val ) {
				$less_vars->add_keyword( $column . '-columns', $val );
			}
		}

		// $less_vars->add_rgba_color( 'post-content-bg', $settings['custom_content_bg_color'] );

		$less_vars->add_pixel_number( 'grid-posts-gap', $settings['gap_between_posts'] );
		$less_vars->add_pixel_number( 'grid-post-min-width', $settings['pwb_column_min_width'] );

		$less_vars->add_paddings(
			array(
				'post-content-padding-top',
				'post-content-padding-right',
				'post-content-padding-bottom',
				'post-content-padding-left',
			),
			$settings['post_content_padding']
		);
		foreach ( $this->get_supported_devices() as $device => $dep ) {
			$less_vars->start_device_section( $device );
			$less_vars->add_keyword(
				'show-filter-categories',
				$this->get_responsive_setting( 'show_categories_filter' ) ? : 'hide'
			);
			$less_vars->close_device_section();
		}

		foreach ( Responsive::get_breakpoints() as $size => $value ) {
			$less_vars->add_pixel_number( "elementor-{$size}-breakpoint", $value );
		}

		$less_vars->add_pixel_number(
			'elementor-container-width',
			the7_elementor_get_content_width_string()
		);
	}

	protected function get_query( $request ) {
		$settings  = $this->get_settings_for_display();
		$post_type = $settings['post_type'];

		if ( $post_type === 'current_query' ) {
			return static::get_current_query( $settings );
		}

		$taxonomy = $settings['taxonomy'];
		$terms    = $settings['terms'];

		// Loop query.
		$query_args = [
			'posts_offset'   => $settings['posts_offset'],
			'post_type'      => $post_type,
			'order'          => $settings['order'],
			'orderby'        => $settings['orderby'],
			'paged'          => $this->template( Pagination::class )->get_paged(),
			'posts_per_page' => $this->template( Pagination::class )->get_posts_per_page(),
		];

		if ( $post_type === 'related' ) {
			$query_builder = new The7_Related_Query_Builder( $query_args );
		} else {
			$query_builder = new The7_Query_Builder( $query_args );
		}

		$query_builder->from_terms( $taxonomy, $terms );

		if ( ! empty( $request->taxonomy ) || $this->template( Pagination::class )->get_loading_mode() === 'standard' ) {
			$query_builder->with_categorizaition( $request );
		}

		return $query_builder->query();
	}

	protected function get_posts_filter_terms( $taxonomy, $terms = [] ) {
		$get_terms_args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'slug',
		];

		if ( $terms ) {
			$get_terms_args['include'] = $terms;
		}

		return get_terms( $get_terms_args );
	}

	/**
	 * @return bool
	 */
	protected function use_filter_request() {
		$settings = $this->get_settings_for_display();

		return $settings['loading_mode'] === 'standard' || $settings['allow_filter_navigation_by_url'];
	}

	protected function add_filter_bar_content_controls() {
		$this->start_controls_section(
			'categorization_section',
			[
				'label'     => __( 'Filter Bar', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'post_type!' => [ 'current_query', 'related' ],
				],
			]
		);

		$layouts            = [
			'show' => __( 'Show', 'the7mk2' ),
			'hide' => __( 'Hide', 'the7mk2' ),
		];
		$responsive_layouts = [ '' => __( 'No change', 'the7mk2' ) ] + $layouts;

		$this->add_basic_responsive_control(
			'show_categories_filter',
			[
				'label'       => __( 'Taxonomy Filter', 'the7mk2' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'hide',
				'options'     => $layouts,
				'device_args' => [
					'tablet' => [
						'options' => $responsive_layouts,
					],
					'mobile' => [
						'options' => $responsive_layouts,
					],
				],
			]
		);

		$this->add_control(
			'filter_show_all',
			[
				'label'        => __( '"All" Filter', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'conditions'   => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'show_categories_filter',
							'operator' => '=',
							'value'    => 'show',
						],
						[
							'name'     => 'show_categories_filter_tablet',
							'operator' => '!==',
							'value'    => 'hide',
						],
						[
							'name'     => 'show_categories_filter_mobile',
							'operator' => '!==',
							'value'    => 'hide',
						],
					],
				],
			]
		);

		$this->add_control(
			'filter_all_text',
			[
				'label'       => __( '"All" Filter Label', 'the7mk2' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'View all', 'the7mk2' ),
				'placeholder' => '',
				'conditions'  => [
					'relation' => 'or',
					'terms'    => [
						[
							'terms' => [
								[
									'name'     => 'show_categories_filter',
									'operator' => '=',
									'value'    => 'show',
								],
								[
									'name'     => 'filter_show_all',
									'operator' => '=',
									'value'    => 'y',
								],
							],
						],
						[
							'terms' => [
								[
									'name'     => 'show_categories_filter_tablet',
									'operator' => '!==',
									'value'    => 'hide',
								],
								[
									'name'     => 'filter_show_all',
									'operator' => '!==',
									'value'    => 'hide',
								],
							],
						],
						[
							'terms' => [
								[
									'name'     => 'show_categories_filter_mobile',
									'operator' => '!==',
									'value'    => 'hide',
								],
								[
									'name'     => 'filter_show_all',
									'operator' => '=',
									'value'    => 'y',
								],
							],
						],
					],
				],
			]
		);

		$this->add_basic_responsive_control(
			'show_orderby_filter',
			[
				'label'                => __( 'Name / Date Ordering', 'the7mk2' ),
				'type'                 => Controls_Manager::SWITCHER,
				'label_on'             => __( 'Show', 'the7mk2' ),
				'label_off'            => __( 'Hide', 'the7mk2' ),
				'return_value'         => 'y',
				'default'              => '',
				'selectors_dictionary' => [
					''  => $this->combine_to_css_vars_definition_string(
						[
							'display-by' => 'none',
						]
					),
					'y' => $this->combine_to_css_vars_definition_string(
						[
							'display-by' => 'inline-flex',
						]
					),
				],
				'selectors'            => [
					'{{WRAPPER}} .filter' => '{{VALUE}}',
				],
				'render_type'          => 'template',
			]
		);

		$this->add_basic_responsive_control(
			'show_order_filter',
			[
				'label'                => __( 'Asc. / Desc. Ordering', 'the7mk2' ),
				'type'                 => Controls_Manager::SWITCHER,
				'label_on'             => __( 'Show', 'the7mk2' ),
				'label_off'            => __( 'Hide', 'the7mk2' ),
				'return_value'         => 'y',
				'default'              => '',
				'selectors_dictionary' => [
					''  => $this->combine_to_css_vars_definition_string(
						[
							'display-sort' => 'none',
						]
					),
					'y' => $this->combine_to_css_vars_definition_string(
						[
							'display-sort' => 'inline-flex',
						]
					),
				],
				'selectors'            => [
					'{{WRAPPER}} .filter' => '{{VALUE}}',
				],
				'render_type'          => 'template',
			]
		);

		$this->add_control(
			'allow_filter_navigation_by_url',
			[
				'label'        => __( 'Allow Navigation By Url', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => '',
				'separator'    => 'before',
				'conditions'   => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'loading_mode',
							'operator' => '!=',
							'value'    => 'standard',
						],
						[
							'relation' => 'or',
							'terms'    => [
								[
									'name'     => 'show_categories_filter',
									'operator' => '==',
									'value'    => 'show',
								],
								[
									'name'     => 'show_categories_filter_tablet',
									'operator' => '!==',
									'value'    => 'hide',
								],
								[
									'name'     => 'show_categories_filter_mobile',
									'operator' => '!==',
									'value'    => 'hide',
								],
								[
									'name'     => 'show_orderby_filter',
									'operator' => '==',
									'value'    => 'y',
								],
								[
									'name'     => 'show_order_filter',
									'operator' => '==',
									'value'    => 'y',
								],
							],
						],
					],
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_query_content_controls() {
		/**
		 * Must have section_id = query_section to work properly.
		 *
		 * @see elements-widget-settings.js:onEditSettings()
		 */
		$this->start_controls_section(
			'query_section',
			[
				'label' => __( 'Query', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'post_type',
			[
				'label'   => __( 'Source', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT2,
				'default' => 'post',
				'options' => the7_elementor_elements_widget_post_types() + [ 'related' => __( 'Related', 'the7mk2' ) ],
				'classes' => 'select2-medium-width',
			]
		);

		$this->add_control(
			'taxonomy',
			[
				'label'     => __( 'Select Taxonomy', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'category',
				'options'   => [],
				'classes'   => 'select2-medium-width',
				'condition' => [
					'post_type!' => [ '', 'current_query' ],
				],
			]
		);

		$this->add_control(
			'terms',
			[
				'label'     => __( 'Select Terms', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT2,
				'default'   => '',
				'multiple'  => true,
				'options'   => [],
				'classes'   => 'select2-medium-width',
				'condition' => [
					'taxonomy!'  => '',
					'post_type!' => [ 'current_query', 'related' ],
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'     => __( 'Order', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'desc',
				'options'   => [
					'asc'  => __( 'Ascending', 'the7mk2' ),
					'desc' => __( 'Descending', 'the7mk2' ),
				],
				'condition' => [
					'post_type!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'     => __( 'Order By', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => [
					'date'          => __( 'Date', 'the7mk2' ),
					'title'         => __( 'Name', 'the7mk2' ),
					'ID'            => __( 'ID', 'the7mk2' ),
					'modified'      => __( 'Modified', 'the7mk2' ),
					'comment_count' => __( 'Comment count', 'the7mk2' ),
					'menu_order'    => __( 'Menu order', 'the7mk2' ),
					'rand'          => __( 'Rand', 'the7mk2' ),
				],
				'condition' => [
					'post_type!' => 'current_query',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_content_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'article_links',
			[
				'label'        => __( 'Links To A Single Post', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
			]
		);

		$this->add_control(
			'article_links_goes_to',
			[
				'label'     => __( 'Links Lead To', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'posts',
				'options'   => [
					'posts'                => __( 'Posts', 'the7mk2' ),
					'external_or_posts'    => __( 'External links or posts', 'the7mk2' ),
					'external_or_disabled' => __( 'External links or disabled', 'the7mk2' ),
				],
				'condition' => [
					'article_links' => 'y',
				],
			]
		);

		$this->add_control(
			'article_link_meta_field',
			[
				'label'       => esc_html__( 'Link Meta Field', 'the7mk2' ),
				'description' => esc_html__( 'Post meta field with external link.', 'the7mk2' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Meta Field Key', 'the7mk2' ),
				'condition'   => [
					'post_type!'             => 'dt_portfolio',
					'article_links_goes_to!' => 'posts',
					'article_links'          => 'y',
				],
			]
		);

		$this->add_control(
			'show_details_icon',
			[
				'label'        => __( 'Hover Icon', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'project_link_icon',
			[
				'label'     => __( 'Choose Icon', 'the7mk2' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'icomoon-the7-font-the7-plus-02',
					'library' => 'the7-cons',
				],
				'condition' => [
					'show_details_icon' => 'y',
				],
			]
		);

		$this->add_control(
			'show_post_title',
			[
				'label'        => __( 'Title', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
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
					'show_post_title' => 'y',
				],
			]
		);

		$this->add_control(
			'post_content',
			[
				'label'        => __( 'Excerpt', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'show_excerpt',
				'default'      => 'show_excerpt',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'excerpt_words_limit',
			[
				'label'       => __( 'Maximum Number Of Words', 'the7mk2' ),
				'description' => __( 'Leave empty to show the entire excerpt.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'post_content' => 'show_excerpt',
				],
			]
		);

		$this->add_control(
			'post_terms',
			[
				'label'        => __( 'Category', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'post_terms_link',
			[
				'label'        => __( 'Link', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'post_terms' => 'y',
				],
			]
		);

		$this->add_control(
			'post_author',
			[
				'label'        => __( 'Author', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'post_author_link',
			[
				'label'        => __( 'Link', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'post_author' => 'y',
				],
			]
		);

		$this->add_control(
			'post_date',
			[
				'label'        => __( 'Date', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'post_date_link',
			[
				'label'        => __( 'Link', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'post_date' => 'y',
					'post_type' => [ 'post', 'current_query', 'related' ],
				],
			]
		);

		$this->add_control(
			'post_comments',
			[
				'label'        => __( 'Comments count', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'post_comments_link',
			[
				'label'        => __( 'Link', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'post_comments' => 'y',
				],
			]
		);

		$this->add_control(
			'show_read_more_button',
			[
				'label'        => __( 'Read More', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'read_more_button_text',
			[
				'label'     => __( 'Button Text', 'the7mk2' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Read more', 'the7mk2' ),
				'condition' => [
					'show_read_more_button' => 'y',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_layout_content_controls() {
		$this->start_controls_section(
			'layout_section',
			[
				'label' => __( 'Layout', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'        => __( 'Masonry', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'masonry',
				'default'      => 'masonry',
			]
		);

		$this->add_control(
			'loading_effect',
			[
				'label'   => __( 'Loading Effect', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'             => __( 'None', 'the7mk2' ),
					'fade_in'          => __( 'Fade in', 'the7mk2' ),
					'move_up'          => __( 'Move up', 'the7mk2' ),
					'scale_up'         => __( 'Scale up', 'the7mk2' ),
					'fall_perspective' => __( 'Fall perspective', 'the7mk2' ),
					'fly'              => __( 'Fly', 'the7mk2' ),
					'flip'             => __( 'Flip', 'the7mk2' ),
					'helix'            => __( 'Helix', 'the7mk2' ),
					'scale'            => __( 'Scale', 'the7mk2' ),
				],
			]
		);

		$this->add_control(
			'responsiveness',
			[
				'label'     => __( 'Responsiveness Mode', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'browser_width_based',
				'options'   => [
					'browser_width_based' => __( 'Browser width based', 'the7mk2' ),
					'post_width_based'    => __( 'Post width based', 'the7mk2' ),
				],
				'separator' => 'before',
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
				'condition'   => [
					'responsiveness' => 'browser_width_based',
				],
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
				'min'            => 0,
				'max'            => 12,
				'condition'      => [
					'responsiveness' => 'browser_width_based',
				],
			]
		);

		$this->add_control(
			'pwb_column_min_width',
			[
				'label'      => __( 'Column Minimum Width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => 300,
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],
				'condition'  => [
					'responsiveness' => 'post_width_based',
				],
				'separator'  => 'before',
			]
		);

		$this->add_control(
			'pwb_columns',
			[
				'label'     => __( 'Desired Columns Number', 'the7mk2' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 3,
				'min'       => 1,
				'max'       => 12,
				'condition' => [
					'responsiveness' => 'post_width_based',
					'layout'         => 'masonry',
				],
			]
		);

		$this->add_basic_responsive_control(
			'gap_between_posts',
			[
				'label'       => __( 'Gap Between Columns', 'the7mk2' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'unit' => 'px',
					'size' => 15,
				],
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .dt-css-grid'         => 'grid-column-gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .dt-isotope .wf-cell' => 'padding-right: calc({{SIZE}}{{UNIT}}/2) !important; padding-left: calc({{SIZE}}{{UNIT}}/2) !important;',
					'{{WRAPPER}} .dt-isotope'          => 'margin-right: calc(-1*{{SIZE}}{{UNIT}}/2) !important; margin-left: calc(-1*{{SIZE}}{{UNIT}}/2) !important;',
				],
				'render_type' => 'template',
			]
		);

		$this->add_basic_responsive_control(
			'rows_gap',
			[
				'label'       => __( 'Rows Gap', 'the7mk2' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'default'     => [
					'size' => '20',
				],
				'range'       => [
					'px' => [
						'max' => 100,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .dt-css-grid'         => 'grid-row-gap: {{SIZE}}{{UNIT}}; --grid-row-gap: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .dt-isotope .wf-cell' => 'padding-top: calc({{SIZE}}{{UNIT}}/2) !important; padding-bottom: calc({{SIZE}}{{UNIT}}/2) !important;',
					'{{WRAPPER}} .dt-isotope'          => 'margin-top: calc(-1*{{SIZE}}{{UNIT}}/2) !important; margin-bottom: calc(-1*{{SIZE}}{{UNIT}}/2) !important;',
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'all_posts_the_same_width',
			[
				'label'        => __( 'Make All Posts The Same Width', 'the7mk2' ),
				'description'  => __( 'Post wide/normal width can be chosen in single post options.', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'y',
				'default'      => '',
			]
		);

		$this->end_controls_section();
	}

	protected function add_skin_style_controls() {
		$this->start_controls_section(
			'skins_style_section',
			[
				'label' => __( 'Skin', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'post_layout',
			[
				'label'   => __( 'Choose Skin', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => [
					'classic'           => __( 'Classic', 'the7mk2' ),
					'bottom_overlap'    => __( 'Overlapping content area', 'the7mk2' ),
					'gradient_overlap'  => __( 'Blurred content area', 'the7mk2' ),
					'gradient_overlay'  => __( 'Simple overlay on hover', 'the7mk2' ),
					'gradient_rollover' => __( 'Blurred bottom overlay on hover', 'the7mk2' ),
				],
			]
		);

		$this->add_control(
			'classic_image_visibility',
			[
				'label'        => __( 'Image Visibility', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'show',
				'default'      => 'show',
				'condition'    => [
					'post_layout' => 'classic',
				],
			]
		);

		$this->add_basic_responsive_control(
			'classic_image_max_width',
			[
				'label'      => __( 'Max Image Width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => '%',
					'size' => '',
				],
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 2000,
						'step' => 1,
					],
					'%'  => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .classic-layout-list .post-thumbnail' => 'max-width: {{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'post_layout'              => 'classic',
					'classic_image_visibility' => 'show',
				],
			]
		);

		$this->add_basic_responsive_control(
			'bo_content_overlap',
			[
				'label'      => __( 'Content Box Overlap', 'the7mk2' ) . ' (px)',
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .bottom-overlap-layout-list article:not(.no-img) .post-entry-content' => 'margin-top: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bottom-overlap-layout-list article:not(.no-img) .project-links-container' => 'height: calc(100% - {{SIZE}}{{UNIT}});',
				],
				'condition'  => [
					'post_layout' => 'bottom_overlap',
				],
			]
		);

		$this->add_control(
			'go_animation',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'              => __( 'Fade', 'the7mk2' ),
					'direction_aware'   => __( 'Direction aware', 'the7mk2' ),
					'redirection_aware' => __( 'Reverse direction aware', 'the7mk2' ),
					'scale_in'          => __( 'Scale in', 'the7mk2' ),
				],
				'condition' => [
					'post_layout' => 'gradient_overlay',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_box_style_controls() {
		$this->start_controls_section(
			'box_section',
			[
				'label' => __( 'Box', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'box_border_width',
			[
				'label'      => __( 'Border Width', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} article' => 'border-style: solid; border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'box_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} article, {{WRAPPER}} .content-rollover-layout-list article.post' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_basic_responsive_control(
			'box_padding',
			[
				'label'      => __( 'Padding', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} article' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->start_controls_tabs( 'box_style_tabs' );

		$this->start_controls_tab(
			'classic_style_normal',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .the7-elementor-widget:not(.class-1) article',
			]
		);

		$this->add_control(
			'box_background_color',
			[
				'label'     => __( 'Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} article' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'box_border_color',
			[
				'label'     => __( 'Border Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} article' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'classic_style_hover',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow_hover',
				'selector' => '{{WRAPPER}} .the7-elementor-widget:not(.class-1) article:hover',
			]
		);

		$this->add_control(
			'box_background_color_hover',
			[
				'label'     => __( 'Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} article:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'box_border_color_hover',
			[
				'label'     => __( 'Border Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} article:hover' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function add_hover_icon_style_controls() {
		$this->start_controls_section(
			'icon_style_section',
			[
				'label'     => __( 'Hover Icon', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_details_icon' => 'y',
				],
			]
		);

		$this->add_control(
			'project_icon_size',
			[
				'label'      => __( 'Icon Size', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a > span:before' => 'font-size: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .project-links-container a > svg' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'project_icon_bg_size',
			[
				'label'      => __( 'Background Size', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a > span:before' => 'line-height: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .project-links-container a'               => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'project_icon_border_width',
			[
				'label'      => __( 'Border Width', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; border-style: solid;',
				],
			]
		);

		$this->add_control(
			'project_icon_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'project_icon_margin',
			[
				'label'      => __( 'Icon Margin', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->start_controls_tabs( 'icon_style_tabs' );

		$this->start_controls_tab(
			'icons_colors',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_control(
			'project_icon_color',
			[
				'label'     => __( 'Icon Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .portfolio-shortcode .project-links-container a:not(:hover) > span'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .portfolio-shortcode .project-links-container a > span'                 => 'color: {{VALUE}};',
					'{{WRAPPER}} .portfolio-shortcode .project-links-container a > svg, {{WRAPPER}} .project-links-container a > svg'             => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'project_icon_border_color',
			[
				'label'     => __( 'Icon Border Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .project-links-container a' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'project_icon_bg_color',
			[
				'label'     => __( 'Icon Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .project-links-container a' => 'background: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'icons_hover_colors',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_control(
			'project_icon_color_hover',
			[
				'label'     => __( 'Icon Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .portfolio-shortcode .project-links-container a:hover > span'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .portfolio-shortcode .project-links-container a:hover > svg, {{WRAPPER}} .project-links-container a:hover > svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'project_icon_border_color_hover',
			[
				'label'     => __( 'Icon Border Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .project-links-container a { transition: all 0.3s ease;}
					{{WRAPPER}} .project-links-container a:hover'  => 'border-color: {{VALUE}};',

				],
			]
		);

		$this->add_control(
			'project_icon_bg_color_hover',
			[
				'label'     => __( 'Icon Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .project-links-container a { transition: all 0.3s ease;}
					{{WRAPPER}} .project-links-container a:hover'  => 'background: {{VALUE}}; box-shadow: none;',

				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function add_image_style_controls() {
		$this->start_controls_section(
			'section_design_image',
			[
				'label'     => __( 'Image', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'classic_image_visibility!' => '',
				],
			]
		);

		$this->add_control(
			'item_ratio',
			[
				'label'       => __( 'Image Ratio', 'the7mk2' ),
				'description' => __( 'Lieve empty to use original proportions', 'the7mk2' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'size' => '',
				],
				'range'       => [
					'px' => [
						'min'  => 0.1,
						'max'  => 2,
						'step' => 0.01,
					],
				],
			]
		);

		$this->add_control(
			'img_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .post-thumbnail-wrap .post-thumbnail'                                => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .post-thumbnail-wrap .post-thumbnail > .post-thumbnail-rollover'     => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .post-thumbnail-wrap .post-thumbnail > .post-thumbnail-rollover img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .content-rollover-layout-list article'                               => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_scale_animation_on_hover',
			[
				'label'   => __( 'Scale Animation On Hover', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'quick_scale',
				'options' => [
					'disabled'    => __( 'Disabled', 'the7mk2' ),
					'quick_scale' => __( 'Quick scale', 'the7mk2' ),
					'slow_scale'  => __( 'Slow scale', 'the7mk2' ),
				],
			]
		);

		$this->start_controls_tabs( 'thumbnail_effects_tabs' );

		$this->start_controls_tab(
			'normal',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'overlay_background',
				'types'          => [ 'classic', 'gradient' ],
				'exclude'        => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => __( 'Background Overlay', 'the7mk2' ),
					],
				],
				'selector'       => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail > .post-thumbnail-rollover:before,
				{{WRAPPER}} .description-on-hover article .post-thumbnail > .post-thumbnail-rollover:before,
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail > .post-thumbnail-rollover:after,
				{{WRAPPER}} .description-on-hover article .post-thumbnail > .post-thumbnail-rollover:after
				',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'img_shadow',
				'selector'  => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail,
				{{WRAPPER}} .description-on-hover article .post-thumbnail
				',
				'condition' => [
					'post_layout!' => [ 'gradient_rollover', 'gradient_overlay' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'thumbnail_filters',
				'selector' => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail img,
				{{WRAPPER}} .description-on-hover article .post-thumbnail img
				',
			]
		);

		$this->add_control(
			'thumbnail_opacity',
			[
				'label'      => __( 'Opacity', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => '%',
					'size' => '100',
				],
				'size_units' => [ '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail img,
					{{WRAPPER}} .description-on-hover article .post-thumbnail img' => 'opacity: calc({{SIZE}}/100)',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'hover',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'overlay_hover_background',
				'types'          => [ 'classic', 'gradient' ],
				'exclude'        => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => __( 'Background Overlay', 'the7mk2' ),
					],
					'color'      => [
						'selectors' => [
							'{{SELECTOR}} { transition: all 0.3s; }
							{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail > .post-thumbnail-rollover:before,
							{{WRAPPER}} .gradient-overlap-layout-list article .post-thumbnail > .post-thumbnail-rollover:before,
							{{WRAPPER}} .description-on-hover article .post-thumbnail > .post-thumbnail-rollover:before { transition: opacity 0.3s;}
							{{WRAPPER}} .post-thumbnail:hover > .post-thumbnail-rollover:before,
							{{WRAPPER}} .post-thumbnail:not(:hover) > .post-thumbnail-rollover:after {transition-delay: 0.15s;}
							{{SELECTOR}}' => 'background: {{VALUE}};',
						],

					],
				],
				'selector'       => '
					{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail > .post-thumbnail-rollover:after,
					{{WRAPPER}} .gradient-overlap-layout-list article .post-thumbnail > .post-thumbnail-rollover:after,
					{{WRAPPER}} .description-on-hover article .post-thumbnail > .post-thumbnail-rollover:after
				',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'img_hover_shadow',
				'selector'  => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap:hover .post-thumbnail,
				{{WRAPPER}} .description-on-hover article:hover .post-thumbnail
				',
				'condition' => [
					'post_layout!' => [ 'gradient_rollover', 'gradient_overlay' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'thumbnail_hover_filters',
				'selector' => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap:hover .post-thumbnail img,
				{{WRAPPER}} .description-on-hover article:hover .post-thumbnail img
				',
			]
		);
		$this->add_control(
			'thumbnail_hover_opacity',
			[
				'label'      => __( 'Opacity', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => '%',
					'size' => '100',
				],
				'size_units' => [ '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .description-under-image .post-thumbnail-wrap:hover .post-thumbnail img,
					{{WRAPPER}} .description-on-hover article:hover .post-thumbnail img'               => 'opacity: calc({{SIZE}}/100)',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function add_content_style_controls() {
		$this->start_controls_section(
			'content_style_section',
			[
				'label' => __( 'Content', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'custom_content_bg_color',
			[
				'label'     => __( 'Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}}' => '--content-bg-color:{{VALUE}}',
				],
			]
		);

		$this->add_basic_responsive_control(
			'bo_content_width',
			[
				'label'      => __( 'Content Width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => '%',
					'size' => '',
				],
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .description-under-image .post-entry-content'               => 'max-width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .description-on-hover .post-entry-content .post-entry-body' => 'max-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_basic_responsive_control(
			'post_content_padding',
			[
				'label'      => __( 'Content Padding', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} article .post-entry-content'                       => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .content-rollover-layout-list .post-entry-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_basic_responsive_control(
			'post_content_box_alignment',
			[
				'label'                => __( 'Horizontal Position', 'the7mk2' ),
				'type'                 => Controls_Manager::CHOOSE,
				'toggle'               => false,
				'default'              => 'left',
				'options'              => [
					'left'   => [
						'title' => __( 'Left', 'the7mk2' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'the7mk2' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'the7mk2' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'selectors_dictionary' => [
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				],
				'selectors'            => [
					'{{WRAPPER}} .description-under-image .post-entry-content'                       => 'align-self: {{VALUE}};',
					'{{WRAPPER}} .description-on-hover .post-entry-content .post-entry-body'         => 'align-self: {{VALUE}};',
					'{{WRAPPER}} .description-on-hover .post-entry-content .project-links-container' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_basic_responsive_control(
			'post_content_alignment',
			[
				'label'     => __( 'Text Alignment', 'the7mk2' ),
				'type'      => Controls_Manager::CHOOSE,
				'toggle'    => false,
				'default'   => 'left',
				'options'   => [
					'left'   => [
						'title' => __( 'Left', 'the7mk2' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'the7mk2' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'the7mk2' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .post-entry-content' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .classic-layout-list .post-thumbnail-wrap' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_post_title_style_controls() {
		$this->start_controls_section(
			'post_title_style_section',
			[
				'label'     => __( 'Post Title', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_post_title' => 'y',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'post_title',
				'label'          => __( 'Typography', 'the7mk2' ),
				'selector'       => '{{WRAPPER}} .ele-entry-title',
				'fields_options' => [
					'font_family' => [
						'default' => '',
					],
					'font_size'   => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
					'font_weight' => [
						'default' => '',
					],
					'line_height' => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
				],
			]
		);

		$this->start_controls_tabs( 'post_title_style_tabs' );

		$this->start_controls_tab(
			'post_title_normal_style',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_control(
			'custom_title_color',
			[
				'label'     => __( 'Font Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'#page {{WRAPPER}} article:not(.class-1):not(.keep-custom-css) .ele-entry-title a'       => 'color: {{VALUE}}',
					'#page {{WRAPPER}} article:not(.class-1):not(.keep-custom-css) .ele-entry-title span'    => 'color: {{VALUE}}',
					'#page {{WRAPPER}} article:not(.class-1):not(.keep-custom-css) .ele-entry-title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'post_title_hover_style',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_control(
			'post_title_color_hover',
			[
				'label'     => __( 'Font Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'#page {{WRAPPER}} article:not(.class-1):not(.keep-custom-css) .ele-entry-title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'post_title_bottom_margin',
			[
				'label'      => __( 'Spacing', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .ele-entry-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .content-rollover-layout-list.meta-info-off .post-entry-wrapper' => 'bottom: -{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_post_meta_style_controls() {
		$this->start_controls_section(
			'post_meta_style_section',
			[
				'label'      => __( 'Meta Information', 'the7mk2' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'post_date',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'post_terms',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'post_author',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'post_comments',
							'operator' => '!==',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'post_meta_separator',
			[
				'label'       => __( 'Separator Between', 'the7mk2' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '•',
				'placeholder' => '',
				'selectors'   => [
					'{{WRAPPER}} .entry-meta .meta-item:not(:first-child):before' => 'content: "{{VALUE}}";',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'post_meta',
				'label'          => __( 'Typography', 'the7mk2' ),
				'fields_options' => [
					'font_family' => [
						'default' => '',
					],
					'font_size'   => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
					'font_weight' => [
						'default' => '',
					],
					'line_height' => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
				],
				'selector'       => '{{WRAPPER}} .entry-meta, {{WRAPPER}} .entry-meta > span',
			]
		);

		$this->add_control(
			'post_meta_font_color',
			[
				'label'     => __( 'Font Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .entry-meta > a, {{WRAPPER}} .entry-meta > span'             => 'color: {{VALUE}}',
					'{{WRAPPER}} .entry-meta > a:after, {{WRAPPER}} .entry-meta > span:after' => 'background: {{VALUE}}; -webkit-box-shadow: none; box-shadow: none;',
				],
			]
		);

		$this->add_control(
			'post_meta_bottom_margin',
			[
				'label'      => __( 'Spacing', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .entry-meta' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .content-rollover-layout-list .post-entry-wrapper' => 'bottom: -{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_text_style_controls() {
		$this->start_controls_section(
			'post_text_style_section',
			[
				'label'     => __( 'Text', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'post_content' => 'show_excerpt',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'post_content',
				'label'          => __( 'Typography', 'the7mk2' ),
				'fields_options' => [
					'font_family' => [
						'default' => '',
					],
					'font_size'   => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
					'font_weight' => [
						'default' => '',
					],
					'line_height' => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
				],
				'selector'       => '{{WRAPPER}} .entry-excerpt *',
				'condition'      => [
					'post_content!' => 'off',
				],
			]
		);

		$this->add_control(
			'post_content_color',
			[
				'label'     => __( 'Font Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .entry-excerpt' => 'color: {{VALUE}}',
				],
				'condition' => [
					'post_content!' => 'off',
				],
			]
		);

		$this->add_control(
			'post_content_bottom_margin',
			[
				'label'      => __( 'Spacing', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .entry-excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'post_content!' => 'off',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_filter_bar_style_controls() {
		$this->start_controls_section(
			'filter_bar_style_section',
			[
				'label'      => __( 'Filter Bar', 'the7mk2' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'condition'  => [
					'post_type!' => [ 'current_query', 'related' ],
				],
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'show_categories_filter',
							'operator' => '!==',
							'value'    => 'hide',
						],
						[
							'name'     => 'show_categories_filter_tablet',
							'operator' => '!==',
							'value'    => 'hide',
						],
						[
							'name'     => 'show_categories_filter_mobile',
							'operator' => '!==',
							'value'    => 'hide',
						],
						[
							'name'     => 'show_orderby_filter',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'show_order_filter',
							'operator' => '!==',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_basic_responsive_control(
			'filter_position',
			[
				'label'                => __( 'Align', 'the7mk2' ),
				'type'                 => Controls_Manager::CHOOSE,
				'toggle'               => false,
				'default'              => 'center',
				'options'              => [
					'left'   => [
						'title' => __( 'Left', 'the7mk2' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'the7mk2' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'the7mk2' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors_dictionary' => [
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				],
				'selectors'            => [
					'{{WRAPPER}} .filter'                => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .filter .filter-categories' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .filter .filter-extras' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_style',
			[
				'label'          => __( 'Pointer', 'the7mk2' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => 'default',
				'options'        => [
					'default'     => __( 'Default', 'the7mk2' ),
					'none'        => __( 'None', 'the7mk2' ),
					'underline'   => __( 'Underline', 'the7mk2' ),
					'overline'    => __( 'Overline', 'the7mk2' ),
					'double-line' => __( 'Double Line', 'the7mk2' ),
					'framed'      => __( 'Framed', 'the7mk2' ),
					'background'  => __( 'Background', 'the7mk2' ),
					'text'        => __( 'Text', 'the7mk2' ),
				],
				'style_transfer' => true,
				'conditions'     => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'show_categories_filter',
							'operator' => '=',
							'value'    => 'show',
						],
						[
							'name'     => 'show_categories_filter_tablet',
							'operator' => '!==',
							'value'    => 'hide',
						],
						[
							'name'     => 'show_categories_filter_mobile',
							'operator' => '!==',
							'value'    => 'hide',
						],
					],
				],
			]
		);

		$this->add_control(
			'animation_line',
			[
				'label'      => __( 'Animation', 'the7mk2' ),
				'type'       => Controls_Manager::SELECT,
				'default'    => 'fade',
				'options'    => [
					'fade'     => 'Fade',
					'slide'    => 'Slide',
					'grow'     => 'Grow',
					'drop-in'  => 'Drop In',
					'drop-out' => 'Drop Out',
					'none'     => 'None',
				],
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'terms' => [
								[
									'name'     => 'show_categories_filter',
									'operator' => '=',
									'value'    => 'show',
								],
								[
									'name'     => 'filter_style',
									'operator' => 'in',
									'value'    => [ 'underline', 'overline', 'double-line' ],
								],
							],
						],
						[
							'terms' => [
								[
									'name'     => 'show_categories_filter_tablet',
									'operator' => '!==',
									'value'    => 'hide',
								],
								[
									'name'     => 'filter_style',
									'operator' => 'in',
									'value'    => [ 'underline', 'overline', 'double-line' ],
								],
							],
						],
						[
							'terms' => [
								[
									'name'     => 'show_categories_filter_mobile',
									'operator' => '!==',
									'value'    => 'hide',
								],
								[
									'name'     => 'filter_style',
									'operator' => 'in',
									'value'    => [ 'underline', 'overline', 'double-line' ],
								],
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'animation_framed',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'    => 'Fade',
					'grow'    => 'Grow',
					'shrink'  => 'Shrink',
					'draw'    => 'Draw',
					'corners' => 'Corners',
					'none'    => 'None',
				],
				'condition' => [
					'filter_style'           => 'framed',
					'show_categories_filter' => 'show',
				],
			]
		);

		$this->add_control(
			'animation_background',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'                   => 'Fade',
					'grow'                   => 'Grow',
					'shrink'                 => 'Shrink',
					'sweep-left'             => 'Sweep Left',
					'sweep-right'            => 'Sweep Right',
					'sweep-up'               => 'Sweep Up',
					'sweep-down'             => 'Sweep Down',
					'shutter-in-vertical'    => 'Shutter In Vertical',
					'shutter-out-vertical'   => 'Shutter Out Vertical',
					'shutter-in-horizontal'  => 'Shutter In Horizontal',
					'shutter-out-horizontal' => 'Shutter Out Horizontal',
					'none'                   => 'None',
				],
				'condition' => [
					'filter_style'           => 'background',
					'show_categories_filter' => 'show',
				],
			]
		);

		$this->add_control(
			'animation_text',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'grow',
				'options'   => [
					'grow'   => 'Grow',
					'shrink' => 'Shrink',
					'sink'   => 'Sink',
					'float'  => 'Float',
					'skew'   => 'Skew',
					'rotate' => 'Rotate',
					'none'   => 'None',
				],
				'condition' => [
					'filter_style'           => 'text',
					'show_categories_filter' => 'show',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'filter_typography',
				'label'    => __( 'Typography', 'the7mk2' ),
				'selector' => '{{WRAPPER}} .filter a',
			]
		);

		$this->add_control(
			'filter_underline_height',
			[
				'label'      => __( 'Pointer Height', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-pointer-border-width: {{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'filter_style!' => [ 'background', 'none', 'text', 'default' ],
				],
			]
		);

		$this->start_controls_tabs( 'filter_elemenets_style' );

		$this->start_controls_tab(
			'filter_normal_style',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_control(
			'navigation_font_color',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-title-color-normal: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'filter_hover_style',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_control(
			'filter_hover_text_color',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-title-color-hover: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'filter_hover_pointer_color',
			[
				'label'     => __( 'Pointer Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-pointer-bg-color-hover: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'filter_active_style',
			[
				'label' => __( 'Active', 'the7mk2' ),
			]
		);

		$this->add_control(
			'filter_active_text_color',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-title-color-active: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'filter_active_pointer_color',
			[
				'label'     => __( 'Pointer Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-pointer-bg-color-active: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'filter_bg_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-pointer-bg-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
				'condition'  => [
					'filter_style' => 'background',
				],
			]
		);

		$this->add_basic_responsive_control(
			'filter_element_padding',
			[
				'label'      => __( 'Item Padding', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .filter .filter-categories a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					'{{WRAPPER}} .filter .filter-by'      => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					'{{WRAPPER}} .filter .filter-sorting' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_basic_responsive_control(
			'filter_element_margin',
			[
				'label'      => __( 'Item Margin', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .filter .filter-categories a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					'{{WRAPPER}} .filter .filter-by'      => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					'{{WRAPPER}} .filter .filter-sorting' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_basic_responsive_control(
			'gap_below_category_filter',
			[
				'label'     => __( 'Filter Bar Spacing', 'the7mk2' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'default'   => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .filter' => '--filter-top-gap: {{TOP}}{{UNIT}}; --filter-right-gap: {{RIGHT}}{{UNIT}};  --filter-bottom-gap: {{BOTTOM}}{{UNIT}}; --filter-left-gap: {{LEFT}}{{UNIT}}; margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'post_type!' => 'current_query',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * @return bool
	 */
	protected function filter_is_visible() {
		$show   = $this->get_responsive_setting( 'show_categories_filter' );
		$show_t = $this->get_responsive_setting( 'show_categories_filter', 'tablet' );
		$show_m = $this->get_responsive_setting( 'show_categories_filter', 'mobile' );

		return isset( $show, $show_t, $show_m ) && ! ( $show === 'hide' && $show_t === 'hide' && $show_m === 'hide' );
	}
}
