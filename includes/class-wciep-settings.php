<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPO_WCIEP_Settings {
	
	/**
	 * @var WPO_WCIEP_Settings
	 */
	protected static $_instance = null;
	
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts_and_styles' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'admin_menu', array( $this, 'iepp_add_page' ) );
	}
	
	/**
	 * Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * User settings.
	 */
	public function init_settings() {

		$option = 'woocommerce-improved-external-products';
	
		// Create option in wp_options.
		if ( false == get_option( $option ) ) {
			add_option( $option );
		}
	
		// Template Selection Section.
		add_settings_section(
			'plugin_settings',
			__( 'Select Your Options', 'woocommerce-improved-external-products' ),
			array( $this, 'section_options_callback' ),
			$option
		);

		add_settings_field(
			'standard_free_settings_header',
			__( 'Standard Settings', 'woocommerce-improved-external-products' ),
			array( $this, 'heading_element_callback' ),
			$option,
			'plugin_settings'
		);

		add_settings_field(
			'default_option_for_new_tab',
			__( 'Default setting for external products', 'woocommerce-improved-external-products' ),
			array( $this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'default_option_for_new_tab',
				'options' 		=> array(
					'yes'			=> __( 'Open all external products in new tab by default' , 'woocommerce-improved-external-products' ),
					'no'			=> __( 'Open all products in same tab by default' , 'woocommerce-improved-external-products' )
				),
				'default'		=> 'no',
			)
		);

		add_settings_field(
			'custom_single_button_html',
			__( 'Custom Single Product Button HTML', 'woocommerce-improved-external-products' ),
			array( $this, 'textarea_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'custom_single_button_html',
				'default'		=> '<a href="{product_url}" rel="nofollow" class="single_add_to_cart_button button alt" target="{target}">{button_text}</a>',
			)
		);



		add_settings_field(
			'pro_settings_header',
			__( 'Pro Settings', 'woocommerce-improved-external-products' ),
			array( $this, 'heading_element_callback' ),
			$option,
			'plugin_settings'
		);

		add_settings_field(
			'new_tab_by_product_cat',
			__( 'Check categories to open in new tab', 'woocommerce-improved-external-products' ),
			array( $this, 'multicheckbox_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'new_tab_by_product_cat',
				'default'		=> 'yes',
				'disabled'		=> true,
			)
		);

		add_settings_field(
			'category_image_options',
			__( 'Category Page Image Link', 'woocommerce-improved-external-products' ),
			array( $this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'category_image_options',
				'options' 		=> array(
					'no'			=> __( 'Link Image to Product Page from Category' , 'woocommerce-improved-external-products' ),
					'yes'			=> __( 'Link Image to External Product from Category' , 'woocommerce-improved-external-products' )
				),
				'default'		=> 'no',
				'disabled'		=> true,
			)
		);

		add_settings_field(
			'category_button_options',
			__( 'Category Page Button Link', 'woocommerce-improved-external-products' ),
			array( $this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'category_button_options',
				'options' 		=> array(
					'no'			=> __( 'Link Add to Cart Button to Product Page from Category' , 'woocommerce-improved-external-products' ),
					'yes'			=> __( 'Link Add to Cart Button to External Product from Category' , 'woocommerce-improved-external-products' )
				),
				'default'		=> 'yes',
				'disabled'		=> true,
			)
		);

		add_settings_field(
			'variation_custom_single_button_html',
			__( 'Variation Custom Single Product Button HTML', 'woocommerce-improved-external-products' ),
			array( $this, 'textarea_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'variation_custom_single_button_html',
				'default'       => '<a href="{product_url}" rel="nofollow" class="single_add_to_cart_button button alt" target="{target}">{button_text}</a>',
				'disabled'		=> true,
			)
		);

		add_settings_field(
			'shop_category_image_selector',
			__( 'Selector for product images on category page (change only if category image settings are not working)', 'woocommerce-improved-external-products' ),
			array( $this, 'text_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'shop_category_image_selector',
				'default'       => "item.closest('.product').find('a').not('.add_to_cart_button').has('img')",
				'disabled'		=> true,
			)
		);

		add_settings_field(
			'shop_category_button_selector',
			__( 'Selector for product button on category page (change only if category button settings are not working)', 'woocommerce-improved-external-products' ),
			array( $this, 'text_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'shop_category_button_selector',
				'default'       => "item.closest('.product').find('a.add_to_cart_button')",
				'disabled'		=> true,
			)
		);

		// Register settings.
		register_setting( $option, $option, array( $this, 'improvedexternalproducts_options_validate' ) );

		// Register defaults if settings empty (might not work in case there's only checkboxes and they're all disabled)
		$option_values = get_option($option);
		
		if ( empty( $option_values ) ) {
			$this->default_settings();
		}
	}

	/*
	 * Add menu page
	*/
	public function iepp_add_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Improved External Products', 'woocommerce-improved-external-products' ),
			__( 'Improved External Products', 'woocommerce-improved-external-products' ),
			'manage_options',
			'iepp_options_page',
			array( $this, 'improvedexternalproducts_options_do_page' )
		);
	}

	/**
	 * Add settings link to plugins page
	 */
	public function improvedexternalproducts_add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=iepp_options_page">'. esc_html__( 'Settings', 'woocommerce-improved-external-products' ) . '</a>';
	  	array_push( $links, $settings_link );
	  	return $links;
	}
	
	/**
	 * Styles for settings page
	 */
	public function enqueue_admin_scripts_and_styles(): void {
		if ( isset( $_REQUEST['page'] ) && 'iepp_options_page' === $_REQUEST['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_style(
				'wpo-iepp-admin-style',
				untrailingslashit( plugin_dir_url( dirname( __FILE__ ) ) ) . '/assets/css/admin-styles.css',
				array(),
				WC_IEP_VERSION
			);
		}
	}
	 
	/**
	 * Default settings.
	 */
	public function default_settings() {
		$sections = get_option('improvedexternalproducts_sections');
		if(empty($sections['templates'])){
			$sections['templates'] = array();
		}
		update_option('improvedexternalproducts_sections',$sections);
	}

	/**
	 * Build the options page.
	 */
	public function improvedexternalproducts_options_do_page() {
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br /></div>
			<h2><?php esc_html_e('Improved External Products','woocommerce-improved-external-products') ?></h2>

			<?php if ( ! class_exists('ImprovedExternalProductsPro') ) { ?>
			<div class="improved-external-products-pro-ad">
				<img src="<?php echo esc_url( plugins_url( 'assets/images/wpo-helper.png', dirname(__FILE__) ) ); ?>" class="wpo-helper">
				<h3><?php esc_html_e( 'Supercharge Improved External Products with the following features:', 'woocommerce-improved-external-products' ); ?></h3>
				<ul>
					<li><?php esc_html_e('Open in new tab or current tab on a product level.','woocommerce-improved-external-products') ?></li>
					<li><?php esc_html_e('Open in new tab or current tab on a category level.','woocommerce-improved-external-products') ?></li>
					<li><?php esc_html_e('Setup variable external products.','woocommerce-improved-external-products') ?></li>
				</ul>
				<a href="https://wpovernight.com/downloads/improved-external-products-pro/" target="_blank"class="button button-primary"><?php esc_html_e("Get Improved External Products Pro!", 'woocommerce-improved-external-products'); ?></a>
			</div>
			<?php } ?>
			<form method="post" action="options.php">
				<?php 
					settings_fields( 'woocommerce-improved-external-products' );
					do_settings_sections( 'woocommerce-improved-external-products' );
					submit_button();
				?>
				<div style="margin-top:20px;margin-bottom:40px">
					<h2><?php esc_html_e( 'Having Trouble?','woocommerce-improved-external-products' ); ?></h2>
					<p><?php esc_html_e( 'Email support@wpovernight.com and we\'ll answer your question as quickly as possible.','woocommerce-improved-external-products' ); ?></p>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Text field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Text field.
	 */
	public function text_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		$size = isset( $args['size'] ) ? $args['size'] : '25';
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		if(!class_exists('ImprovedExternalProductsPro')){
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		} else {
			$disabled = '';
		}
		$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"%5$s/>', esc_attr( $id ), esc_attr( $menu ), esc_attr( $current ), esc_attr( $size ), esc_attr( $disabled ) );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', wp_kses_post( $args['description'] ) );
		}

		if (isset( $args['disabled'] ) && !class_exists('ImprovedExternalProductsPro')) {
			$html .= ' <span style="display:none;" class="pro-feature"><i>'. esc_html__( 'This feature only available in', 'woocommerce-improved-external-products' ) .' <a href="https://wpovernight.com/downloads/improved-external-products-pro/">Improved External Products Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
		}
	
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Text Area field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Text field.
	 */
	public function textarea_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		$size = isset( $args['size'] ) ? $args['size'] : '25';
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		if(!class_exists('ImprovedExternalProductsPro')){
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		} else {
			$disabled = '';
		}
		$html = sprintf( '<textarea rows="4" cols="100" id="%1$s" name="%2$s[%1$s]" %5$s/>%3$s</textarea>', esc_attr( $id ), esc_attr( $menu) , esc_attr( $current ), esc_attr( $size ), esc_attr( $disabled ) );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', wp_kses_post( $args['description'] ) );
		}

		if (isset( $args['disabled'] ) && !class_exists('ImprovedExternalProductsPro')) {
			$html .= ' <span style="display:none;" class="pro-feature"><i>'. esc_html__( 'This feature only available in', 'woocommerce-improved-external-products' ) .' <a href="https://wpovernight.com/downloads/improved-external-products-pro/">Improved External Products Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
		}
	
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	
	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function select_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		
		$options = get_option( $menu );
		
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		if(!class_exists('ImprovedExternalProductsPro')){
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		} else {
			$disabled = '';
		}
		
		$html = sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', esc_attr( $menu ), esc_attr( $id ), esc_attr( $disabled ) );
		$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
		
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $key ), esc_attr( selected( $current, $key, false ) ), esc_attr( $label ) );
		}
		$html .= sprintf( '</select>' );
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', wp_kses_post( $args['description'] ) );
		}
		
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Displays a multiple selectbox for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function multiple_select_element_callback( $args ) {
		$html = '';
		foreach ($args as $id => $boxes) {
			$menu = $boxes['menu'];
			
			$options = get_option( $menu );
			
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $boxes['default'] ) ? $boxes['default'] : '';
			}
			
			if(!class_exists('ImprovedExternalProductsPro')){
				$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			} else {
				$disabled = '';
			}
			
			$html .= sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', esc_attr( $menu ), esc_attr( $id ), esc_attr( $disabled ) );
			$html .= sprintf( '<option value="%s"%s>%s</option>', '0', esc_attr( selected( $current, '0', false ) ), esc_attr( '' ) );
			
			foreach ( (array) $boxes['options'] as $key => $label ) {
				$html .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $key ), esc_attr( selected( $current, $key, false ) ), esc_attr( $label ) );
			}
			$html .= '</select>';
	
			if ( isset( $boxes['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', wp_kses_post( $boxes['description'] ) );
			}
			$html .= '<br />';
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Checkbox field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Checkbox field.
	 */
	public function checkbox_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];

		$options = get_option( $menu );

		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}

		if(!class_exists('ImprovedExternalProductsPro')){
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		} else {
			$disabled = '';
		}
		$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s %4$s/>', esc_attr( $id ), esc_attr( $menu ), checked( 1, $current, false ), esc_attr( $disabled ) );

		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', wp_kses_post( $args['description'] ) );
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function radio_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		$html = '';
		if(!class_exists('ImprovedExternalProductsPro')){
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		} else {
			$disabled = '';
		}

		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s %5$s />', esc_attr( $menu ), esc_attr( $id ), esc_attr( $key ), checked( $current, $key, false ), esc_attr( $disabled ) );
			$html .= sprintf( '<label for="%1$s[%2$s][%3$s]"> %4$s</label><br>', esc_attr( $menu ), esc_attr( $id ), esc_attr( $key ), esc_attr( $label ) );
		}
		
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', wp_kses_post( $args['description'] ) );
		}

		if (isset( $args['disabled'] ) && !class_exists('ImprovedExternalProductsPro')) {
			$html .= ' <span style="display:none;" class="pro-feature"><i>'. esc_html__( 'This feature only available in', 'woocommerce-improved-external-products' ) .' <a href="https://wpovernight.com/downloads/improved-external-products-pro/">Improved External Products Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function multicheckbox_element_callback( $args ) {
		$options    = get_option('woocommerce-improved-external-products');
		$pag        = 'woocommerce-improved-external-products';
		$_cats      = get_terms( 'product_cat' );
		$html       = '';

		foreach ($_cats as $term) {
			if(!class_exists('ImprovedExternalProductsPro')){
				$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			} else {
				$disabled = '';
			}
			$category = !empty($options['new_tab_by_product_cat']) ? $options['new_tab_by_product_cat'] : array();
			$checked = in_array($term->term_id, $category) ? 'checked="checked"' : '';
			$html .= sprintf( '<input type="checkbox" id="%1$s[%4$s][%2$s]" name="%1$s[%4$s][%2$s]" value="%2$s" %3$s %5$s />', esc_attr( $pag ), esc_attr( $term->term_id ), esc_attr( $checked ), esc_attr( $args['id'] ), esc_attr( $disabled ) );
			$html .= sprintf( '<label for="%1$s[%4$s][%3$s]"> %2$s</label><br>', esc_attr( $pag ), esc_attr( $term->name ), esc_attr( $term->term_id ), esc_attr( $args['id'] ) );
		}

		$html .= sprintf( '<span class="description"> %s</span>', '' );
		if(!class_exists('ImprovedExternalProductsPro')){
			$html .= ' <span style="display:none;" class="pro-feature"><i>'. esc_html__('This feature only available in', 'woocommerce-improved-external-products') .' <a href="https://wpovernight.com/downloads/improved-external-products-pro/">Improved External Products Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Text field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Text field.
	 */
	public function heading_element_callback( $args ) {
		echo esc_attr( '' );
	}

	/**
	 * Section null callback.
	 *
	 * @return void.
	 */
	public function section_options_callback() {
	
	}

	/**
	 * Validate/sanitize options input
	 */
	public function improvedexternalproducts_options_validate( $input ) {
		// Create our array for storing the validated options
		$output = array();
		// Loop through each of the incoming options.
		//print_r($input);
		//echo '<br /><br />';
		foreach ( $input as $key => $value ) {
			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[$key] ) ) {
				if(is_array($input[$key])){
					$output[$key] = $input[$key];
				} else {
					// Strip all HTML and PHP tags and properly handle quoted strings.
					if($key == 'custom_single_button_html' || $key == 'variation_custom_single_button_html'){
						$output[$key] = $input[$key];
					} else {
						$output[$key] = wp_strip_all_tags( stripslashes( $input[$key] ) );
					}
				}
				//print_r($output);
			}
		}
		// Return the array processing any additional functions filtered by this action.
		return apply_filters( 'improvedexternalproducts_validate_input', $output, $input );
	}
}