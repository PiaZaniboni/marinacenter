<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $GLOBALS['svx'] ) && version_compare( $GLOBALS['svx'], '1.0.9' ) == 0 ) :

if ( !class_exists( 'SevenVXSettings' ) ) {

	class SevenVXSettings {

		public static $version = '1.0.9';

		protected static $_instance = null;

		public static $plugin = null;

		public static $lang;

		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {
			do_action( 'svx_loading' );

			$this->init_hooks();

			do_action( 'svx_loaded' );
		}

		private function init_hooks() {

			$plugins = apply_filters( 'svx_plugins', array() );

			$page = isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'wc-settings' ? true: false;
			$slug = isset( $_REQUEST['tab'] ) && array_key_exists( $_REQUEST['tab'], $plugins ) ? $_REQUEST['tab']: '';

			if ( !empty( $plugins ) && $page ) {
				foreach( $plugins as $p ) {
					add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_tab' ), 50 );
				}
				if ( $slug ) {
					add_action( 'woocommerce_settings_tabs_' . $slug, array( $this, 'display_tab' ) );
					add_action( 'admin_enqueue_scripts', array( $this, 'admin_js' ), 10 );
					add_action( 'admin_footer', array( $this, 'add_vars' ) );
					add_filter( 'svx_settings_templates', array( $this, 'default_templates') );
				}
			}

			add_action( 'wp_ajax_svx_ajax_factory', array( $this, 'ajax_factory' ) );
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

		public function admin_js() {

			$plugins = apply_filters( 'svx_plugins', array() );

			$slug = isset( $_REQUEST['tab'] ) && array_key_exists( $_REQUEST['tab'], $plugins ) ? $_REQUEST['tab']: '';

			if ( $slug ) {
				wp_enqueue_style( 'svx-style', $this->plugin_url() .'/css/svx-style' . ( is_rtl() ? '-rtl' : '' ) . '.min.css', false, self::$version );
				//wp_enqueue_style( 'svx-style', $this->plugin_url() .'/css/svx-style.css', false, self::$version );
				wp_register_script( 'svx-settings', $this->plugin_url() . '/js/svx-core.js', array( 'jquery', 'wp-util', 'jquery-ui-core', 'jquery-ui-sortable' ), self::$version, true );
				wp_enqueue_script( 'svx-settings' );

			/*	$needed = apply_filters( 'svx-needed-js', array( 'wp-color-picker') );

				if ( in_array( 'wp-color-picker', $needed ) ) {*/
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker' );
				/*}*/

				if ( function_exists( 'wp_enqueue_media' ) ) {
					wp_enqueue_media();
				}
			}

		}

		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		public function add_tab( $tabs ) {

			$plugins = apply_filters( 'svx_plugins', array() );

			if ( !empty( $plugins ) ) {
				foreach( $plugins as $p ) {
					$tabs[$p['slug']] = $p['name'];
				}
			}

			return $tabs;

		}

		public function display_tab() {

		?>
			<div id="svx-settings" class="svx-<?php echo current_filter(); ?>"></div>
		<?php

		}

		public function add_templates() {

			$templates = apply_filters( 'svx_settings_templates', array() );

			if ( !empty( $templates ) ) {
				foreach( $templates as $slug => $template ) {
				?>
					<script type="text/template" id="tmpl-<?php echo $slug; ?>">
						<?php echo $template; ?>
					</script>
				<?php
				}
			}

		}

		public function default_templates( $templates ) {

			ob_start();
			?>
				<div id="svx-main-wrapper" data-slug="{{ data.slug }}"<?php self::get_language(); ?>>
					<span id="icon"></span>
					<div id="svx-main-header">
						<h2 class="svx-plugin">
							{{ data.name }}
						</h2>
						<p class="svx-desc">
							{{ data.desc }}
						</p>
						<p class="svx-main-buttons">
							<a href="{{ data.ref.url }}" class="svx-button" target="_blank">
								{{ data.ref.name }}
							</a>
							<a href="{{ data.doc.url }}" class="svx-button-primary" target="_blank">
								{{ data.doc.name }}
							</a>
						</p>
					</div>
					<div id="svx-main">
						<ul id="svx-settings-menu"></ul>
						<div id="svx-settings-main"></div>
					</div>
				</div>
			<?php

			$templates['svx-main-wrapper'] = ob_get_clean();

			ob_start();
			?>
				<a href="{{ data.url }}" class="svx-button{{ data.class }}">
					{{ data.name }}
				</a>
			<?php

			$templates['svx-button'] = ob_get_clean();

			ob_start();
			?>
				<li data-id="{{ data.id }}">
					{{ data.name }}
				</li>
			<?php

			$templates['svx-li-menu'] = ob_get_clean();

			ob_start();
			?>
				<div id="svx-settings-main-{{ data.id }}" data-id="{{ data.id }}">
					<div id="svx-settings-header">
						<p class="svx-desc">
							{{{ data.desc }}}
							<span id="save" class="svx-button-primary">Save</span>
						</p>
					</div>
					<div id="svx-settings-wrapper">
						{{{ data.settings }}}
					</div>
					<div id="svx-settings-footer">
						<p class="svx-desc">
							{{{ data.desc }}}
							<span id="save-alt" class="svx-button-primary">Save</span>
						</p>
					</div>
				</div>
			<?php

			$templates['svx-settings'] = ob_get_clean();

			ob_start();
			?>
				<div id="{{ data.id }}-option" class="{{ data.class }}<# if ( data.column ) { #>{{ 'svx-column svx-column-'+data.column }}<# } #>">
					<div class="svx-option-header">
						<h3>
							{{ data.name }}
						</h3>
					</div>
					<div class="svx-option-wrapper">
						{{{ data.option }}}
						<p class="svx-desc">
							{{{ data.desc }}}
						</p>
					</div>
				</div>
			<?php

			$templates['svx-option'] = ob_get_clean();

			ob_start();
			?>
				<div id="{{ data.id }}-option" class="{{ data.class }}<# if ( data.column ) { #>{{ 'svx-column svx-column-'+data.column }}<# } #>">
					{{{ data.desc }}}
				</div>
			<?php

			$templates['svx-option-html'] = ob_get_clean();

			ob_start();
			?>
				<span id="svx-export" class="svx-button">Export</span> <span id="svx-import" class="svx-button">Import</span> <span id="svx-backup" class="svx-button">Backup</span> <span id="svx-restore" class="svx-button">Restore</span> <span id="svx-reset" class="svx-button">Reset</span>
			<?php

			$templates['svx-option-utility'] = ob_get_clean();

			ob_start();
			?>
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="svx-change{{ data.class }}" name="{{ data.name }}" type="text"<# if ( data.val ) { #> value="{{ data.val }}"<# } else { #><# } #> />
			<?php

			$templates['svx-option-text'] = ob_get_clean();

			ob_start();
			?>
				<textarea id="{{ data.eid }}" data-option="{{ data.id }}" class="svx-change{{ data.class }}" name="{{ data.name }}"><# if ( data.val ) { #>{{ data.val }}<# } else { #><# } #></textarea>
			<?php

			$templates['svx-option-textarea'] = ob_get_clean();

			ob_start();
			?>
				<select id="{{ data.eid }}" data-option="{{ data.id }}" class="svx-change{{ data.class }}" name="{{ data.name }}" class="svx-multiple" multiple>
					{{{ data.options }}}
				</select>
			<?php

			$templates['svx-option-multiselect'] = ob_get_clean();

			ob_start();
			?>
				<option value="{{ data.val }}"<# if ( $.inArray(data.val, data.sel) != '-1' ) { #> selected="selected"<# } else { #><# } #>>
					{{{ data.name }}}
				</option>
			<?php

			$templates['svx-option-values-multiselect'] = ob_get_clean();

			ob_start();
			?>
				<select id="{{ data.eid }}" data-option="{{ data.id }}" class="svx-change{{ data.class }}" name="{{ data.name }}">
					{{{ data.options }}}
				</select>
			<?php

			$templates['svx-option-select'] = ob_get_clean();

			ob_start();
			?>
				<option value="{{ data.val }}"<# if ( data.sel == data.val ) { #> selected="selected"<# } else { #><# } #>>
					{{{ data.name }}}
				</option>
			<?php

			$templates['svx-option-values-select'] = ob_get_clean();

			ob_start();
			?>
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="svx-change{{ data.class }}" name="{{ data.name }}" type="checkbox" <# if ( data.val == "yes" ) { #> checked="checked"<# } else { #><# } #>/> <label for="{{ data.eid }}"></label>
			<?php

			$templates['svx-option-checkbox'] = ob_get_clean();

			ob_start();
			?>
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="svx-change{{ data.class }}" name="{{ data.name }}" type="number"<# if ( data.val ) { #> value="{{ data.val }}"<# } else { #><# } #> />
			<?php

			$templates['svx-option-number'] = ob_get_clean();

			ob_start();
			?>
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="svx-change{{ data.class }}" name="{{ data.name }}" type="hidden"<# if ( data.val ) { #> value="{{ data.val }}"<# } else { #><# } #> />
			<?php

			$templates['svx-option-hidden'] = ob_get_clean();

			ob_start();
			?>
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="svx-change{{ data.class }}" name="{{ data.name }}" type="hidden"<# if ( data.val ) { #> value="{{ data.val }}"<# } else { #><# } #> />
				<div id="{{ data.id }}-list" class="svx-option-list">
					{{{ data.options }}}
				</div>
				<span class="svx-button-primary svx-option-list-add" data-id="{{ data.id }}">Add Item +</span>
			<?php

			$templates['svx-option-list'] = ob_get_clean();

			ob_start();
			?>
				<div class="svx-option-list-item">
					<span class="svx-option-list-item-icon svx-list-expand-button" data-type="{{ data.type }}"></span>
					<span class="svx-option-list-item-title">{{ data.title }}</span>
					<span class="svx-option-list-item-icon svx-list-remove-button" data-id="{{ data.id }}"></span>
					<span class="svx-option-list-item-icon svx-list-move-button"></span>
					<# if ( data.customizer ) { #><span class="svx-option-list-item-icon svx-list-customizer-button"></span><# } #>
					<div class="svx-option-list-item-container">
						{{{ data.options }}}
					</div>
				</div>
			<?php

			$templates['svx-option-list-item'] = ob_get_clean();

			ob_start();
			?>
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="svx-change{{ data.class }}" name="{{ data.name }}" type="hidden"<# if ( data.val ) { #> value="{{ data.val }}"<# } #> />
				<div id="{{ data.id }}-list" class="svx-option-list">
					{{{ data.options }}}
				</div>
				{{{ data.selects }}} <span class="svx-button-primary svx-option-list-select-add" data-id="{{ data.id }}">Add Item +</span>
			<?php

			$templates['svx-option-list-select'] = ob_get_clean();

			ob_start();
			?>
				<div id="svx-customizer" data-id="{{ data.id }}">
					<div class="svx-customizer-wrapper">
						<div class="svx-customizer-header">
							<span id="svx-customizer-exit"></span>
							<h2>Terms Manager</h2>
							<# if ( data.taxonomy == 'meta' ) { #>
								<span id="svx-customizer-add" class="svx-button-primary">Add Term +</span>
							<# }
							else { #>
								<span id="svx-customizer-custom-order" class="<# if ( data.order == 'true' ) { #>svx-button-primary<# } else { #>svx-button<# } #>">Custom Order</span>
							<# } #>
						</div>
						<div class="svx-customizer-style">
							<div id="svx-special-options">
								<span class="svx-special-option">
									<label>Type</label>
									<select class="svx-terms-style-change" data-option="type">
										<option value="text"<# if ( data.type == 'text' ) { #> selected="selected"<# } #>>Plain Text</option>
										<option value="color"<# if ( data.type == 'color' ) { #> selected="selected"<# } #>>Color</option>
										<option value="image"<# if ( data.type == 'image' ) { #> selected="selected"<# } #>>Thumbnail</option>
										<option value="selectbox"<# if ( data.type == 'selectbox' ) { #> selected="selected"<# } #>>Select Box</option>
										<option value="html"<# if ( data.type == 'html' ) { #> selected="selected"<# } #>>HTML</option>
										<# if ( data.taxonomy == 'meta' ) { #>
											<option value="input"<# if ( data.type == 'input' ) { #> selected="selected"<# } #>>Input Field</option>
											<option value="checkbox"<# if ( data.type == 'checkbox' ) { #> selected="selected"<# } #>>Checkbox</option>
											<option value="textarea"<# if ( data.type == 'textarea' ) { #> selected="selected"<# } #>>Textarea</option>
											<option value="system"<# if ( data.type == 'system' ) { #> selected="selected"<# } #>>System Select</option>
										<# } #>
									</select>
								</span>
								{{{ data.controls }}}
							</div>
						</div>
						<div id="svx-customizer-terms" class="svx-terms-list" data-taxonomy="{{ data.taxonomy }}">
							{{{ data.terms }}}
						</div>
					</div>
				</div>
			<?php

			$templates['svx-customizer'] = ob_get_clean();

			ob_start();
			?>
				<span class="svx-special-option">
					<label>Style</label>
					<select class="svx-terms-style-change" data-option="style">
						<option value="border"<# if ( data.border == 'round' ) { #> selected="selected"<# } #>>Border</option>
						<option value="background"<# if ( data.style == 'background' ) { #> selected="selected"<# } #>>Background</option>
						<option value="round"<# if ( data.style == 'round' ) { #> selected="selected"<# } #>>Round</option>
					</select>
				</span>
				<span class="svx-special-option">
					<label>Normal</label>
					<input type="text" class="svx-terms-color svx-terms-style-change" data-option="normal"<# if ( data.normal ) { #> value="{{ data.normal }}"<# } #> />
				</span>
				<span class="svx-special-option">
					<label>Active</label>
					<input type="text" class="svx-terms-color svx-terms-style-change" data-option="active"<# if ( data.active ) { #> value="{{ data.active }}"<# } #> />
				</span>
				<span class="svx-special-option">
					<label>Disabled</label>
					<input type="text" class="svx-terms-color svx-terms-style-change" data-option="disabled"<# if ( data.disabled ) { #> value="{{ data.disabled }}"<# } #> />
				</span>
				<span class="svx-special-option">
					<label>Out of stock</label>
					<input type="text" class="svx-terms-color svx-terms-style-change" data-option="outofstock"<# if ( data.outofstock ) { #> value="{{ data.outofstock }}"<# } #> />
				</span>
			<?php

			$templates['svx-customizer-style-text'] = ob_get_clean();

			ob_start();
			?>
				<span class="svx-special-option">
					<label>Swatch Size</label>
					<input type="text" class="svx-terms-style-change" data-option="size"<# if ( data.size ) { #> value="{{ data.size }}"<# } #> />
				</span>
			<?php

			$templates['svx-customizer-style-swatch'] = ob_get_clean();
			ob_start();
			?>
				<div class="svx-terms-list-item" data-id="{{ data.id }}" data-slug="{{ data.slug }}">
					<div class="svx-term-badge">
						<span class="svx-term-item-title">{{ data.name }}</span>
						<# if ( data.taxonomy == 'meta' ) { #>
							<span class="svx-term-item-icon svx-term-remove-button" data-id="{{ data.id }}"></span>
						<# } #>
						<# if ( data.taxonomy == 'meta' || data.order == 'true' ) { #>
							<span class="svx-term-item-icon svx-term-move-button"></span>
						<# } #>
					</div>
					<div class="svx-term-options-holder">
						<div class="svx-term-option">
							<label>Name</label>
							<input type="text" class="svx-terms-change" name="name"<# if ( data.name ) { #> value="{{ data.name }}"<# } #> />
						</div>
						<div class="svx-term-option">
							<label>Value</label>
							<input type="text" class="svx-terms-change <# if ( data.style ) { #>svx-terms-{{ data.style }}<# } #>" name="value"<# if ( data.value ) { #> value="{{ data.value }}"<# } #> />
							<# if ( data.style == 'image' ) { #>
								<span class="svx-button svx-terms-image-add">Add Image +</span>
							<# } #>
						</div>
						<# if ( data.taxonomy == 'meta' ) { #>
							<div class="svx-term-option">
								<label>Price</label>
								<input type="text" class="svx-terms-change" name="price"<# if ( data.price ) { #> value="{{ data.price }}"<# } #> />
							</div>
						<# } #>
						<div class="svx-term-option">
							<label>Tooltip</label>
							<textarea class="svx-terms-change" name="tooltip"><# if ( data.tooltip ) { #>{{ data.tooltip }}<# } #></textarea>
						</div>
					</div>
				</div>
			<?php

			$templates['svx-customizer-term'] = ob_get_clean();

			return $templates;

		}

		public function add_vars() {

			if ( wp_script_is( 'svx-settings', 'enqueued' ) ) {

				$this->add_templates();

				$vars = apply_filters( 'svx_plugins_settings', array() );

				$slug = isset( $_REQUEST['tab'] ) && array_key_exists( $_REQUEST['tab'], $vars ) ? $_REQUEST['tab']: '';
				if ( isset( $vars[$slug] ) ) {
					$vars[$slug]['ajax'] = esc_url( admin_url( 'admin-ajax.php' ) );
					wp_localize_script( 'svx-settings', 'svx', $vars[$slug] );
				}

			}

		}

		public function ajax_die($opt) {
			$opt['success'] = false;
			wp_send_json( $opt );
			exit;
		}


		public function _terms_get_options( $terms, &$ready, &$level, $mode ) {
			foreach ( $terms as $term ) {
				if ( $mode == 'select' ) {
					$ready[$term->term_id] = ( $level > 0 ? str_repeat( '&nbsp;&nbsp;', $level ) : '' ) . $term->name;
				}
				else {
					$ready[] = array(
						'id' => $term->term_id,
						'name' => ( $level > 0 ? str_repeat( '&nbsp;&nbsp;', $level ) : '' ) . $term->name,
						'slug' => $term->slug,
					);
				}
				if ( !empty( $term->children ) ) {
					$level++;
					SevenVX()->_terms_get_options( $term->children, $ready, $level, $mode );
					$level--;
				}
			}
		}

		public function _terms_sort_hierarchicaly( Array &$cats, Array &$into, $parentId = 0 ) {
			foreach ( $cats as $i => $cat ) {
				if ( $cat->parent == $parentId ) {
					$into[$cat->term_id] = $cat;
					unset($cats[$i]);
				}
			}
			foreach ( $into as $topCat ) {
				$topCat->children = array();
				SevenVX()->_terms_sort_hierarchicaly( $cats, $topCat->children, $topCat->term_id );
			}
		}

		public function _terms_get( $taxonomy, $mode ) {
			$ready = array();

			if ( taxonomy_exists( $taxonomy ) ) {

				$args = array(
					'hide_empty' => 0,
					'hierarchical' => ( is_taxonomy_hierarchical( $taxonomy ) ? 1 : 0 )
				);

				$terms = get_terms( $taxonomy, $args );

				if ( is_taxonomy_hierarchical( $taxonomy ) ) {
					$terms_sorted = array();
					SevenVX()->_terms_sort_hierarchicaly( $terms, $terms_sorted );
					$terms = $terms_sorted;
				}

				if ( !empty( $terms ) && !is_wp_error( $terms ) ){
					$var =0;
					SevenVX()->_terms_get_options( $terms, $ready, $var, $mode );
				}

			}

			return $ready;
		}

		public function _terms_decode( $str ) {
			$str = preg_replace( "/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode( $str ) );
			return html_entity_decode( $str, null, 'UTF-8' );
		}

		public function _types_get() {
			$types = wc_get_product_types();
			$ready = array();
			if ( !empty( $types ) ) {
				foreach( $types as $k => $v ) {
					$ready[$k] = $v;
				}
			}
			return $ready;
		}

		public function _taxonomies_get() {
			$taxonomies = get_object_taxonomies( 'product' );
			$ready = array();
			if ( !empty( $taxonomies ) ) {
				foreach( $taxonomies as $k ) {
					if ( substr($k, 0, 3) == 'pa_' ) {
						$ready[$k] =  wc_attribute_label( $k );
					}
					else if ( taxonomy_exists( $k ) ) {
						$taxonomy = get_taxonomy( $k );
						if ( $taxonomy->public ) {
							$ready[$k] = $taxonomy->label;
						}
					}
				}
			}
			return $ready;
		}

		public function _attributes_get_alt() {
			$attributes = get_object_taxonomies( 'product' );
			$ready = array();
			if ( !empty( $attributes ) ) {
				foreach( $attributes as $k ) {
					if ( substr($k, 0, 3) == 'pa_' ) {
						$ready[$k] =  wc_attribute_label( $k );
					}
				}
			}
			return $ready;
		}

		public function _attributes_get() {
			$attributes = wc_get_attribute_taxonomies();
			$ready = array();

			if ( !empty( $attributes ) ) {
				foreach( $attributes as $attribute ) {
					$ready['pa_' . $attribute->attribute_name] = $attribute->attribute_label;
				}
			}

			return $ready;
		}

		public function array_overlay( $a, $b ) {
			foreach( $b as $k => $v ) {
				$a[$k] = $v;
			}
			return $a;
		}

		public function ajax_factory() {

			$opt = array(
				'success' => true
			);

			if ( !isset( $_POST['svx']['type'] ) ) {
				SevenVX()->ajax_die($opt);
			}

			switch( $_POST['svx']['type'] ) {

				case 'get_control_options' :

					$set = explode( ':', $_POST['svx']['settings'] );

					switch( $set[1] ) {

						case 'image_sizes' :
							$image_array = array();
							$image_sizes = get_intermediate_image_sizes();
							foreach ( $image_sizes as $image_size ) {
								$image_array[$image_size] = $image_size;
							}
							wp_send_json( $image_array );
							exit;
						break;
						case 'wp_options' :
							wp_send_json( get_option( substr( $_POST['svx']['settings'], 16 ) ) );
							exit;
						break;
						case 'users' :
							$return = array();
							$users = get_users( array( 'fields' => array( 'id', 'display_name' ) ) );

							foreach ( $users as $user ) {
								$return[$user->id] = $user->display_name;
							}

							wp_send_json( $return );
							exit;
						break;
						case 'product_attributes' :
							//wp_send_json( SevenVX()->_attributes_get() );
							wp_send_json( SevenVX()->_attributes_get_alt() );
							exit;
						break;
						case 'product_taxonomies' :
							wp_send_json( SevenVX()->_taxonomies_get() );
							exit;
						break;
						case 'product_types' :
							wp_send_json( SevenVX()->_types_get() );
							exit;
						break;
						case 'taxonomy' :
							wp_send_json( SevenVX()->_terms_get( $set[2], 'select' ) );
							exit;
						break;
						case 'terms' :
							wp_send_json( SevenVX()->_terms_get( $set[2], 'terms' ) );
							exit;
						break;
						default :
							SevenVX()->ajax_die($opt);
						break;

					}

				break;
				case 'save' :

					$sld = isset( $_POST['svx']['solids'] ) && is_array( $_POST['svx']['solids'] ) ? $_POST['svx']['solids'] : array();

					if ( !empty( $sld ) ) {
						foreach( $sld as $k => $v ) {
							$val = isset( $v['val'] ) && !empty( $v['val'] ) ? $v['val'] : false;
							if ( !is_array( $val ) ) {
								$val = array();
							}
							$std = get_option( $k, array() );
							if ( !is_array( $std ) ) {
								$std = array();
							}
							if ( empty( $val ) ) {
								update_option( $k, '', false );
							}
							else {
								update_option( $k, $this->array_overlay( $std, $val ), false );
							}
						}
					}

					$stg = isset( $_POST['svx']['settings'] ) && is_array( $_POST['svx']['settings'] ) ? $_POST['svx']['settings'] : array();

					foreach( $stg as $k => $v ) {
						if ( isset( $v['autoload'] ) ) {
							if ( $v['autoload'] == 'true' ) {
								$opt['auto'][$k] = $v['val'];
							}
							else if ( $v['autoload'] == 'false' ) {
								$opt['std'][$k] = isset( $v['val'] ) ? $v['val'] : false;
							}
						}
					}

					$opt = apply_filters( 'svx_ajax_save_settings', $opt );

					if ( isset( $opt['std'] ) && !empty( $opt['std'] ) && is_array( $opt['std'] ) ) {
						update_option( 'svx_settings_' . $_POST['svx']['plugin'], array_merge( get_option( 'svx_settings_' . $_POST['svx']['plugin'], array() ), $opt['std'] ), false );
					}

					$less = isset( $_POST['svx']['less'] ) && is_array( $_POST['svx']['less'] ) ? $_POST['svx']['less'] : array();

					if ( !empty( $less['length'] ) && $less['length'] > 0 ) {
						$option = isset( $less['option'] ) ? $less['option'] : false;
						if ( $option !== false ) {
							unset( $less['option'] );
							if ( isset( $less['solids'] ) ) {
								$solids = $less['solids'];
								if ( isset( $solids['name'] ) ) {
									$presets = $opt['std'][$solids['name']];
									foreach( $presets as $b => $j ) {
										$preset = apply_filters( 'svx_before_solid' . $solids['solid'], get_option( $solids['solid'] . sanitize_title( $b ), array() ) );
										if ( isset( $preset['name'] ) ) {
											foreach( $solids['options'] as $n ) {
												if ( isset( $preset[$n] ) ) {
													switch ( $n ) {
														case 'name' :
															$less[$n . 's'] .= $less[$n . 's'] == '' ? sanitize_title( $b ) : ',' . sanitize_title( $b );
														break;
														default :
															$less[$n . 's'] .= $less[$n . 's'] == '' ? $preset[$n] : ',' . $preset[$n];
														break;
													}
													
												}
											}
										}
									}
								}
								$less['url'] = '~"' . $less['solids']['url'] . '"';
								unset( $less['solids'] );
							}

							$compiled = self::compile_less( $solids, $less );
						}
					}

					if ( isset( $compiled ) ) {
						$opt['auto'][$option] = $compiled;
					}

					if ( isset( $opt['auto'] ) && !empty( $opt['auto'] ) && is_array( $opt['auto'] ) ) {
						$opt['auto'] = array_merge( get_option( 'svx_autoload', array() ), $opt['auto'] );
					}

					$opt = apply_filters( 'svx_ajax_save_settings_auto', $opt );

					update_option( 'svx_autoload', $opt['auto'], true );

					do_action( 'svx_ajax_saved_settings_' . $_POST['svx']['plugin'], $opt );

					/*$return = array(
						'auto' => get_option( 'svx_autoload', array() ),
						'std' => get_option( 'svx_settings_' . $_POST['svx']['plugin'], array() ),
						'sld' => $sld
					);*/

					wp_send_json( array( 'success' => true ) );
					exit;

				break;

				case 'export' :
					$stg = isset( $_POST['svx']['settings'] ) && is_array( $_POST['svx']['settings'] ) ? $_POST['svx']['settings'] : array();

					if ( isset( $stg['auto'] ) && !empty( $stg['auto'] ) && is_array( $stg['auto'] ) ) {
						$backup_auto = get_option( 'svx_autoload', array() );

						foreach( $stg['auto'] as $k ) {
							if ( isset( $backup_auto[$k] ) ) {
								$exp['auto'][$k] = $backup_auto[$k];
							}
						}
					}

					if ( isset( $stg['std'] ) && !empty( $stg['std'] ) && is_array( $stg['std'] ) ) {
						$exp['std'] = get_option( 'svx_settings_' . $_POST['svx']['plugin'], array() );
					}

					if ( isset( $stg['solids'] ) && !empty( $stg['solids'] ) && is_array( $stg['solids'] ) ) {
						foreach( $stg['solids'] as $k ) {
							$exp['solids'][$k] = get_option( $k );
						}
					}

					wp_send_json( $this->get_for_options( $exp ) );
					exit;
				break;

				case 'import' :
					$stg = isset( $_POST['svx']['settings'] ) ? $_POST['svx']['settings'] : '';

					if ( $stg !== '' ) {

						$opt = $this->get_for_options( json_decode( stripslashes( $stg ), true ) );

						$opt = apply_filters( 'svx_ajax_save_settings', $opt );

						if ( isset( $opt['auto'] ) && !empty( $opt['auto'] ) && is_array( $opt['auto'] ) ) {
							$opt['auto'] = array_merge( get_option( 'svx_autoload', array() ), $opt['auto'] );
							update_option( 'svx_autoload', $opt['auto'], true );
						}

						if ( isset( $opt['std'] ) && !empty( $opt['std'] ) && is_array( $opt['std'] ) ) {
							update_option( 'svx_settings_' . $_POST['svx']['plugin'], $opt['std'], false );
						}

						if ( isset( $opt['solids'] ) && !empty( $opt['solids'] ) && is_array( $opt['solids'] ) ) {
							foreach( $opt['solids'] as $key => $solid ) {
								update_option( $key, $solid, false );
							}
						}

						wp_send_json( array( 'success' => true ) );
						exit;
					}
					wp_send_json( array( 'success' => false ) );
					exit;
				break;

				case 'backup' :
					$bkp = array();
					$stg = isset( $_POST['svx']['settings'] ) && is_array( $_POST['svx']['settings'] ) ? $_POST['svx']['settings'] : array();

					if ( isset( $stg['auto'] ) && !empty( $stg['auto'] ) && is_array( $stg['auto'] ) ) {
						$backup_auto = get_option( 'svx_autoload', array() );
						foreach( $stg['auto'] as $k ) {
							if ( isset( $backup_auto[$k] ) ) {
								$bkp['auto'][$k] = $backup_auto[$k];
							}
						}
					}

					if ( isset( $stg['std'] ) && !empty( $stg['std'] ) && is_array( $stg['std'] ) ) {
						$bkp['std'] = get_option( 'svx_settings_' . $_POST['svx']['plugin'], array() );
					}

					if ( isset( $stg['solids'] ) && !empty( $stg['solids'] ) && is_array( $stg['solids'] ) ) {
						foreach( $stg['solids'] as $k ) {
							$bkp['solids'][$k] = get_option( $k );
						}
					}

					$bkp['time'] = time();

					update_option( '_svx_settings_backup_' . $_POST['svx']['plugin'], $bkp );

					wp_send_json( array( 'success' => true ) );
					exit;
				break;

				case 'restore' :
					$bkp = get_option( '_svx_settings_backup_' . $_POST['svx']['plugin'] );

					if ( isset( $bkp['auto'] ) && !empty( $bkp['auto'] ) && is_array( $bkp['auto'] ) ) {
						$bkp['auto'] = array_merge( get_option( 'svx_autoload', array() ), $bkp['auto'] );
						update_option( 'svx_autoload', $bkp['auto'], true );
					}

					if ( isset( $bkp['std'] ) && !empty( $bkp['std'] ) && is_array( $bkp['std'] ) ) {
						update_option( 'svx_settings_' . $_POST['svx']['plugin'], $bkp['std'], false );
					}

					if ( isset( $bkp['solids'] ) && !empty( $bkp['solids'] ) && is_array( $bkp['solids'] ) ) {
						foreach( $bkp['solids'] as $key => $solid ) {
							update_option( $key, $solid, false );
						}
					}

					wp_send_json( array( 'success' => true ) );
					exit;
				break;

				case 'reset' :
					$stg = isset( $_POST['svx']['settings'] ) && is_array( $_POST['svx']['settings'] ) ? $_POST['svx']['settings'] : array();

					if ( isset( $stg['auto'] ) && !empty( $stg['auto'] ) && is_array( $stg['auto'] ) ) {
						$opt = get_option( 'svx_autoload', array() );

						foreach( $opt as $k => $v ) {
							if ( in_array( $k, $stg['auto'] ) ) {
								unset( $opt[$k] );
							}
						}

						update_option( 'svx_autoload', $opt, true );
					}

					delete_option( 'svx_settings_' . $_POST['svx']['plugin'] );

					if ( isset( $stg['solids'] ) && !empty( $stg['solids'] ) && is_array( $stg['solids'] ) ) {
						foreach( $stg['solids'] as $key ) {
							delete_option( $key );
						}
					}

					wp_send_json( array( 'success' => true ) );
					exit;
				break;

				default :
					SevenVX()->ajax_die($opt);
				break;

			}

		}

		public function get_for_options( $stg ) {
			$opt = array(
				'auto' => array(),
				'std' => array(),
			);

			if ( isset( $stg['auto'] ) && is_array( $stg['auto'] ) ) {
				$opt['auto'] = $stg['auto'];
			}

			if ( isset( $stg['std'] ) && is_array( $stg['std'] ) ) {
				$opt['std'] = $stg['std'];
			}

			if ( isset( $stg['solids'] ) && is_array( $stg['solids'] ) ) {
				$opt['solids'] = $stg['solids'];
			}

			return $opt;
		}


		public static function compile_less( $solids, $less_variables ) {

			$access_type = get_filesystem_method();

			if( $access_type === 'direct' ) {
				$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );

				if ( !WP_Filesystem( $creds ) ) {
					return false;
				}

				require_once( 'less/lessc.inc.php' );

				$src = $solids['url'] . '/assets/less/' . $solids['file'] . '.less';

				$src_scheme = wp_parse_url( $src, PHP_URL_SCHEME );

				$wp_content_url_scheme = wp_parse_url( WP_CONTENT_URL, PHP_URL_SCHEME );

				if ( $src_scheme != $wp_content_url_scheme ) {

					$src = set_url_scheme( $src, $wp_content_url_scheme );

				}

				$file = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $src );

				$less = new lessc;

				foreach( $less_variables as $k => $v ) {
					if ( is_array( $v ) ) {
						$less_variables[$k] = implode( ',', $v );
					}
				}

				$less->setFormatter( 'compressed' );
				$less->setPreserveComments( 'false' );
				$less->setVariables( $less_variables );

				$compile = $less->cachedCompile( $file );

				$upload = wp_upload_dir();

				$id = uniqid();

				$upload_dir = untrailingslashit( $upload['basedir'] ) . '/' . $solids['file'] . '-' . $id . '.css';
				$upload_url = untrailingslashit( $upload['baseurl'] ) . '/' . $solids['file'] . '-' . $id . '.css';

				$check = get_option( 'svx_autoload', array() );

				if ( $check ){
					$cached = isset( $check[$solids['option']] ) ? $check[$solids['option']] : false;
				}

				if ( $cached === false ) {
					$cached_transient = '';
				}
				else {
					if ( isset( $cached['id'] ) ) {
						$cached_transient = $cached['id'];
						if ( $cached['last_known'] !== '' ) {
							$delete = untrailingslashit( $upload['basedir'] ) . '/' . $solids['file'] . '-' . $cached['last_known'] . '.css';
							if ( is_writable( $delete ) ) {
								unlink( $delete );
							}
						}
					}
					else {
						$cached_transient = '';
					}
				}

				global $wp_filesystem;
				if ( $wp_filesystem->put_contents( $upload_dir, self::optimize_less( $compile['compiled'] ), FS_CHMOD_FILE ) ) {
					return array(
						'id' => $id,
						'url' => $upload_url,
						'last_known' => $cached_transient,
					);
				}
			}


		}

		public static function optimize_less( $file_contents ) {
			$file_contents = preg_replace( '/([\w-]+)\s*:\s*unset;?/', '', $file_contents );
			$file_contents = preg_replace( '/([\w-]+)\s*:\s*unset?/', '', $file_contents );

			return $file_contents;
		}

		public static function get_language() {
			echo ( $lang = self::language() ) !== '' ? ' data-language="' . $lang . '"' : '';
		}

		public static function language() {
			if ( self::$lang ) {
				return self::$lang;
			}

			self::$lang = '';

			if ( class_exists( 'SitePress' ) ) {
				$default =  '_' . apply_filters( 'wpml_default_language', NULL );
				$language =  '_' . apply_filters( 'wpml_current_language', NULL );
				if ( $default !== $language ) {
					$doit = $language;
				}
			}

			if ( isset( $doit ) ) {
				self::$lang = $doit;
			}

			return self::$lang;
		}

		public static function stripslashes_deep( $value ) {
			$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
			return $value;
		}

	}

	function SevenVX() {
		return SevenVXSettings::instance();
	}

	SevenVXSettings::instance();

}

endif;

?>