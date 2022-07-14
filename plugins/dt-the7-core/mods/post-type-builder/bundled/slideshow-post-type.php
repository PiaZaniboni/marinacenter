<?php

namespace The7_Core\Mods\Post_Type_Builder\Bundled;

defined( 'ABSPATH' ) || exit;

class Slideshow_Post_Type extends Bundled_Item {

	/**
	 * @return string
	 */
	public static function get_name() {
		return 'dt_slideshow';
	}

	/**
	 * @return array
	 */
	public static function get_args() {
		return apply_filters(
			'presscore_post_type_' . ( static::get_name() ) . '_args',
			[
				'labels'                => [
					'name'                  => _x('Slideshows',						'backend albums', 'dt-the7-core'),
					'singular_name'         => _x('Slider',							'backend albums', 'dt-the7-core'),
					'add_new'               => _x('Add New',						'backend albums', 'dt-the7-core'),
					'add_new_item'          => _x('Add New Slider',					'backend albums', 'dt-the7-core'),
					'edit_item'             => _x('Edit Slider',					'backend albums', 'dt-the7-core'),
					'new_item'              => _x('New Slider',						'backend albums', 'dt-the7-core'),
					'view_item'             => _x('View Slider',					'backend albums', 'dt-the7-core'),
					'search_items'          => _x('Search for Slideshow',			'backend albums', 'dt-the7-core'),
					'not_found'             => _x('No Slideshow Found',				'backend albums', 'dt-the7-core'),
					'not_found_in_trash'    => _x('No Slideshow Found in Trash',	'backend albums', 'dt-the7-core'),
					'parent_item_colon'     => '',
					'menu_name'             => _x('Slideshows',						'backend albums', 'dt-the7-core')
				],
				'public'                => true,
				'publicly_queryable'    => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'query_var'             => true,
				'rewrite'               => true,
				'capability_type'       => 'post',
				'has_archive'           => true,
				'hierarchical'          => false,
				'menu_position'         => 41,
				'supports'              => [ 'title', 'thumbnail' ],
			]
		);
	}

	/**
	 * @return string
	 */
	public static function get_module_name() {
		return 'slideshow';
	}

}
