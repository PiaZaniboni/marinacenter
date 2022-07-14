<?php
/**
 * @package The7
 */

namespace The7\Mods\Compatibility\Elementor\Widget_Templates;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use The7\Mods\Compatibility\Elementor\The7_Elementor_Less_Vars_Decorator_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class Arrows.
 */
class Arrows extends Abstract_Template {

	/**
	 * @return void
	 */
	public function add_content_controls() {
		$this->widget->start_controls_section(
			'arrows_section',
			[
				'label' => __( 'Arrows', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$arrow_options            = [
			'never'  => __( 'Never', 'the7mk2' ),
			'always' => __( 'Always', 'the7mk2' ),
			'hover'  => __( 'On Hover', 'the7mk2' ),
		];
		$arrow_options_on_devices = [
			'' => __( 'Default', 'the7mk2' ),
		] + $arrow_options;

		$this->widget->add_basic_responsive_control(
			'arrows',
			[
				'label'       => __( 'Show Arrows', 'the7mk2' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $arrow_options,
				'device_args' => [
					'tablet' => [
						'default' => '',
						'options' => $arrow_options_on_devices,
					],
					'mobile' => [
						'default' => '',
						'options' => $arrow_options_on_devices,
					],
				],
				'default'     => 'always',
			]
		);

		$arrow_position_options            = [
			'box_area' => 'Box Area',
			'image'    => 'Image',
		];
		$arrow_position_options_on_devices = [
			'' => __( 'Default', 'the7mk2' ),
		] + $arrow_position_options;

		$this->widget->add_basic_responsive_control(
			'arrows_position',
			[
				'label'                => __( 'Vertically aligned to', 'the7mk2' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'box_area',
				'options'              => $arrow_position_options,
				'device_args'          => [
					'tablet' => [
						'default' => '',
						'options' => $arrow_position_options_on_devices,
					],
					'mobile' => [
						'default' => '',
						'options' => $arrow_position_options_on_devices,
					],
				],
				'selectors_dictionary' => [
					'image'    => $this->widget->combine_to_css_vars_definition_string(
						[

							'offset-v-t-img' => 'var(--stage-top-gap) + var(--box-padding-top)',
							'offset-v-m-img' => 'calc(var(--stage-top-gap) + var(--box-padding-top) + var(--arrow-height)/2)',
							'arrow-height'   => 'var(--dynamic-img-height)',
							'top-b-img'      => '0px',
							'offset-v-b-img' => 'calc(var(--stage-top-gap) + var(--box-padding-top) + var(--arrow-height) - var(--arrow-bg-height, var(--arrow-icon-size)))',
						]
					),
					'box_area' => $this->widget->combine_to_css_vars_definition_string(
						[

							'offset-v-t-img' => '0px',
							'offset-v-m-img' => '50%',
							'top-b-img'      => '100%',
							'offset-v-b-img' => '0px',
						]
					),
				],
				'selectors'            => [
					'{{WRAPPER}} .owl-carousel' => '{{VALUE}}',
				],
				'prefix_class'         => 'arrows%s-relative-to-',
			]
		);

		$this->widget->end_controls_section();
	}

	/**
	 * @return void
	 */
	public function add_style_controls() {
		$this->widget->start_controls_section(
			'arrows_style',
			[
				'label'      => __( 'Arrows', 'the7mk2' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'arrows',
							'operator' => '!=',
							'value'    => 'never',
						],
						[
							'name'     => 'arrows_tablet',
							'operator' => '!=',
							'value'    => 'never',
						],
						[
							'name'     => 'arrows_mobile',
							'operator' => '!=',
							'value'    => 'never',
						],
					],
				],
			]
		);

		$this->widget->add_control(
			'arrows_heading',
			[
				'label'     => __( 'Arrow Icon', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->widget->add_control(
			'next_icon',
			[
				'label'       => __( 'Next Arrow', 'the7mk2' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => [
					'value'   => 'icomoon-the7-font-the7-arrow-09',
					'library' => 'the7-icons',
				],
				'skin'        => 'inline',
				'label_block' => false,
				'classes'     => [ 'elementor-control-icons-svg-uploader-hidden' ],
			]
		);

		$this->widget->add_control(
			'prev_icon',
			[
				'label'       => __( 'Previous Arrow', 'the7mk2' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => [
					'value'   => 'icomoon-the7-font-the7-arrow-08',
					'library' => 'the7-icons',
				],
				'skin'        => 'inline',
				'label_block' => false,
				'classes'     => [ 'elementor-control-icons-svg-uploader-hidden' ],
			]
		);

		$this->widget->add_basic_responsive_control(
			'arrow_icon_size',
			[
				'label'      => __( 'Arrow Icon Size', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => 16,
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
					'{{WRAPPER}} .owl-carousel'  => '--arrow-icon-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .owl-nav i'     => 'font-size: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .owl-nav a svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->widget->add_control(
			'arrows_background_heading',
			[
				'label'     => __( 'Arrow style', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->widget->add_basic_responsive_control(
			'arrow_bg_width',
			[
				'label'      => __( 'Background Width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => 30,
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
					'{{WRAPPER}} .owl-nav a' => 'width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->widget->add_basic_responsive_control(
			'arrow_bg_height',
			[
				'label'      => __( 'Background Height', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => 30,
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
					'{{WRAPPER}} .owl-carousel' => '--arrow-bg-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .owl-nav a'    => 'height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->widget->add_control(
			'arrow_border_radius',
			[
				'label'      => __( 'Arrow Border Radius', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => 500,
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .owl-nav a' => 'border-radius: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->widget->add_control(
			'arrow_border_width',
			[
				'label'      => __( 'Arrow Border Width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => 2,
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 25,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .owl-nav a' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid',
				],
			]
		);

		$this->widget->start_controls_tabs( 'arrows_style_tabs' );

		$this->widget->start_controls_tab(
			'arrows_colors',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->widget->add_control(
			'arrow_icon_color',
			[
				'label'     => __( 'Icon Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .owl-nav a i, {{WRAPPER}} .owl-nav a i:before' => 'color: {{VALUE}};',
					'{{WRAPPER}} .owl-nav a svg path' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->widget->add_control(
			'arrow_border_color',
			[
				'label'     => __( 'Border Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .owl-nav a'       => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .owl-nav a:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->widget->add_control(
			'arrow_bg_color',
			[
				'label'     => __( 'Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .owl-nav a'       => 'background: {{VALUE}};',
					'{{WRAPPER}} .owl-nav a:hover' => 'background: {{VALUE}};',
				],
			]
		);

		$this->widget->end_controls_tab();

		$this->widget->start_controls_tab(
			'arrows_hover_colors',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->widget->add_control(
			'arrow_icon_color_hover',
			[
				'label'     => __( 'Icon Color Hover', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .owl-nav a:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .owl-nav a i:before { transition: color 0.3s; } {{WRAPPER}} .owl-nav a:hover i:before' => 'color: {{VALUE}};',
					'{{WRAPPER}} .owl-nav a:hover svg path' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->widget->add_control(
			'arrow_border_color_hover',
			[
				'label'     => __( 'Border Color Hover', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'
					{{WRAPPER}} .owl-nav a { transition: all 0.3s; }
					{{WRAPPER}} .owl-nav a:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->widget->add_control(
			'arrow_bg_color_hover',
			[
				'label'     => __( 'Background Hover Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'
					{{WRAPPER}} .owl-nav a { transition: all 0.3s; }
					{{WRAPPER}} .owl-nav a:hover' => 'background: {{VALUE}};',
				],
			]
		);

		$this->widget->end_controls_tab();

		$this->widget->end_controls_tabs();

		$this->widget->add_control(
			'left_arrow_position_heading',
			[
				'label'     => __( 'Left Arrow Position', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->widget->add_basic_responsive_control(
			'l_arrow_v_position',
			[
				'label'       => __( 'Vertical Position', 'the7mk2' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'top'    => [
						'title' => __( 'Top', 'the7mk2' ),
						'icon'  => 'eicon-v-align-top',
					],
					'center' => [
						'title' => __( 'Middle', 'the7mk2' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'the7mk2' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'     => 'center',
			]
		);

		$this->widget->add_basic_responsive_control(
			'l_arrow_h_position',
			[
				'label'       => __( 'Horizontal Position', 'the7mk2' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
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
				'default'     => 'left',
			]
		);

		$this->widget->add_basic_responsive_control(
			'l_arrow_v_offset',
			[
				'label'      => __( 'Vertical Offset', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => 0,
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
				],
			]
		);

		$this->widget->add_basic_responsive_control(
			'l_arrow_h_offset',
			[
				'label'      => __( 'Horizontal Offset', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => -15,
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
				],
			]
		);

		$this->widget->add_control(
			'right_arrow_position_heading',
			[
				'label'     => __( 'Right Arrow Position', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->widget->add_basic_responsive_control(
			'r_arrow_v_position',
			[
				'label'       => __( 'Vertical Position', 'the7mk2' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'top'    => [
						'title' => __( 'Top', 'the7mk2' ),
						'icon'  => 'eicon-v-align-top',
					],
					'center' => [
						'title' => __( 'Middle', 'the7mk2' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'the7mk2' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'     => 'center',
			]
		);

		$this->widget->add_basic_responsive_control(
			'r_arrow_h_position',
			[
				'label'       => __( 'Horizontal Position', 'the7mk2' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
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
				'default'     => 'right',
			]
		);

		$this->widget->add_basic_responsive_control(
			'r_arrow_v_offset',
			[
				'label'      => __( 'Vertical Offset', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => 0,
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
				],
			]
		);

		$this->widget->add_basic_responsive_control(
			'r_arrow_h_offset',
			[
				'label'      => __( 'Horizontal Offset', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => -15,
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
				],
			]
		);

		$this->widget->end_controls_section();
	}

	/**
	 * @param string $element Element name.
	 *
	 * @return void
	 */
	public function add_container_render_attributes( $element ) {
		$settings = $this->widget->get_settings_for_display();

		$class = [
			the7_array_match(
				$settings['arrows'],
				[
					'never'  => 'carousel-nav-display-never',
					'always' => 'carousel-nav-display-always',
					'hover'  => 'carousel-nav-display-hover',
				]
			),
			the7_array_match(
				$settings['arrows_tablet'],
				[
					'never'  => 'carousel-nav-display-tablet-never',
					'always' => 'carousel-nav-display-tablet-always',
					'hover'  => 'carousel-nav-display-tablet-hover',
				]
			),
			the7_array_match(
				$settings['arrows_mobile'],
				[
					'never'  => 'carousel-nav-display-mobile-never',
					'always' => 'carousel-nav-display-mobile-always',
					'hover'  => 'carousel-nav-display-mobile-hover',
				]
			),
		];

		if ( $settings['arrow_bg_color'] === $settings['arrow_bg_color_hover'] ) {
			$class[] = 'disable-arrows-hover-bg';
		}

		$this->widget->add_render_attribute( $element, 'class', $class );

		$this->widget->add_render_attribute(
			$element,
			[
				'data-arrows'        => $settings['arrows'] !== 'never' ? 'true' : 'false',
				'data-arrows_tablet' => $settings['arrows_tablet'] !== 'never' ? 'true' : 'false',
				'data-arrows_mobile' => $settings['arrows_mobile'] !== 'never' ? 'true' : 'false',
			]
		);
	}

	/**
	 * @param The7_Elementor_Less_Vars_Decorator_Interface $less_vars Less vars manager.
	 *
	 * @return void
	 */
	public function add_less_vars( $less_vars ) {
		$settings = $this->widget->get_settings_for_display();

		$less_vars->add_pixel_number( 'arrow-bg-width', $settings['arrow_bg_width'] );

		if ( $settings['arrows'] !== 'never' || $settings['arrows_tablet'] !== 'never' || $settings['arrows_mobile'] !== 'never' ) {
			foreach ( $this->widget->get_supported_devices() as $device => $dep ) {
				$less_vars->start_device_section( $device );

				$less_vars->add_keyword(
					'arrow-right-v-position',
					$this->widget->get_responsive_setting( 'r_arrow_v_position' ) ?: 'center'
				);
				$less_vars->add_keyword(
					'arrow-right-h-position',
					$this->widget->get_responsive_setting( 'r_arrow_h_position' ) ?: 'right'
				);
				$less_vars->add_unitized_number(
					'r-arrow-v-position',
					$this->widget->get_responsive_setting( 'r_arrow_v_offset' )
				);
				$less_vars->add_unitized_number(
					'r-arrow-h-position',
					$this->widget->get_responsive_setting( 'r_arrow_h_offset' )
				);

				$less_vars->add_keyword(
					'arrow-left-v-position',
					$this->widget->get_responsive_setting( 'l_arrow_v_position' ) ?: 'center'
				);
				$less_vars->add_keyword(
					'arrow-left-h-position',
					$this->widget->get_responsive_setting( 'l_arrow_h_position' ) ?: 'left'
				);
				$less_vars->add_unitized_number(
					'l-arrow-v-position',
					$this->widget->get_responsive_setting( 'l_arrow_v_offset' )
				);
				$less_vars->add_unitized_number(
					'l-arrow-h-position',
					$this->widget->get_responsive_setting( 'l_arrow_h_offset' )
				);

				$less_vars->close_device_section();
			}
		}
	}

	/**
	 * @return void
	 */
	public function render() {
		echo '<div class="owl-nav disabled">';
		echo '<a class="owl-prev" role="button">';
		Icons_Manager::render_icon( $this->widget->get_settings_for_display( 'prev_icon' ) );
		echo '</a>';
		echo '<a class="owl-next" role="button">';
		Icons_Manager::render_icon( $this->widget->get_settings_for_display( 'next_icon' ) );
		echo '</a>';
		echo '</div>';
	}
}
