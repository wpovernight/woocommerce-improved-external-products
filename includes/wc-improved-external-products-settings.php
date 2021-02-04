<?php

class ImprovedExternalProducts_Settings {
	
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) ); // Registers settings
		add_action( 'admin_menu', array( $this, 'iepp_add_page' ) );
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
		$improvedexternalproducts_page = add_submenu_page(
			'woocommerce',
			__( 'Improved External Products', 'woocommerce-improved-external-products' ),
			__( 'Improved External Products', 'woocommerce-improved-external-products' ),
			'manage_options',
			'iepp_options_page',
			array( $this, 'improvedexternalproducts_options_do_page' )
		);
		add_action( 'admin_print_styles-' . $improvedexternalproducts_page, array( &$this, 'improvedexternalproducts_admin_styles' ) );
	}

	/**
	 * Add settings link to plugins page
	 */
	public function improvedexternalproducts_add_settings_link( $links ) {
	    $settings_link = '<a href="options-general.php?page=iepp_options_page">'. __( 'Settings', 'woocommerce' ) . '</a>';
	  	array_push( $links, $settings_link );
	  	return $links;
	}
	
	/**
	 * Styles for settings page
	 */
	public function improvedexternalproducts_admin_styles() {
		wp_enqueue_style( 'improvedexternalproducts-admin' );
	}
	 
	/**
	 * Default settings.
	 */
	public function default_settings() {
		global $options;
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
			<h2><?php _e('Improved External Products','woocommerce-improved-external-products') ?></h2>
			
			<?php if (!class_exists('ImprovedExternalProductsPro')){ ?>
			<div class="improved-external-products-pro-ad">
				<img src="<?php echo plugins_url( 'assets/images/wpo-helper.png', dirname(__FILE__) ); ?>" class="wpo-helper">
				<h3><?php _e( 'Supercharge Improved External Products with the following features:', 'woocommerce-improved-external-products' ); ?></h3>
				<ul>
					<li><?php _e('Open in new tab or current tab on a product level.','woocommerce-improved-external-products') ?></li>
					<li><?php _e('Open in new tab or current tab on a category level.','woocommerce-improved-external-products') ?></li>
					<li><?php _e('Setup variable external products.','woocommerce-improved-external-products') ?></li>
				</ul>
				<a href="https://wpovernight.com/downloads/improved-external-products-pro/" target="_blank"class="button button-primary"><?php _e("Get Improved External Products Pro!", 'woocommerce-improved-external-products'); ?></a>
			</div>
			<style>
				.improved-external-products-pro-ad {
					position: relative;
					min-height: 90px;
					border: 1px solid #3D5C99;
					background-color: #EBF5FF;	
					border-radius: 5px;
					padding: 15px;
					padding-left: 100px;
					margin-top: 15px;
					margin-bottom: 15px;
				}
				img.wpo-helper {
					position: absolute;
					bottom: 0;
					left: 3px;
				}
				.improved-external-products-pro-ad h3 {
					margin: 0;
				}
				.improved-external-products-pro-ad ul {
					list-style-type: square;
					margin: 0;
					margin-left: 1.5em;
					margin-top: 1em;
					margin-bottom: 1.5em;
				}
				ul#datafeedr li:before{
				    content: '✔';   
				    margin-left: -1em;
				    margin-right: .100em;
				    color:green;
				}

				ul#datafeedr{
				   padding-left: 20px;
				   text-indent: 2px;
				   list-style: none;
				   list-style-position: outside;
				}
			</style>
			<?php } ?>
			<?php
			global $options;
			$sections = get_option('improvedexternalproducts_sections');
			//print_r($sections);
			//$option = get_option('woocommerce-improved-external-products');
			//print_r($option); //for debugging
			?>
			<form method="post" action="options.php">
				<?php 
					settings_fields( 'woocommerce-improved-external-products' );
					do_settings_sections( 'woocommerce-improved-external-products' );
					submit_button();
				?>
				<div id="datafeedr-intro">
					<a href="https://affiliates.datafeedr.com/idevaffiliate.php?id=39_0_3_7"><img src="<?php echo plugins_url( 'assets/images/datafeedr.png', dirname(__FILE__) ); ?>"></a>
					<h3><?php _e( 'Supercharge your Affiliate Store with Datafeedr</h3>','woocommerce-improved-external-products' ); ?>
					<div>
						<p><?php _e( 'Are you looking for a great way to easily add affiliate products to a new or existing WooCommerce affiliate store?','woocommerce-improved-external-products' ); ?></p>
						<p><?php _e( 'Then checkout one of our partners','woocommerce-improved-external-products' ); ?> <a href="https://affiliates.datafeedr.com/idevaffiliate.php?id=39_0_3_7" target="_blank" rel="nofollow">Datafeedr</a>!</p>
						<p><?php echo wp_sprintf( 
							// translators: 1 & 2: <a> tags
							__( 'They have a great %1$saffiliate integration for WooCommerce%2$s that plays well with our external products plugin.','woocommerce-improved-external-products' ), '<a href="https://affiliates.datafeedr.com/idevaffiliate.php?id=39_7_3_11" target="_blank" rel="nofollow">', '</a>' ); ?></p>
						<p><?php _e( 'Subscriptions for Datafeedr start at just $29/month','woocommerce-improved-external-products' ); ?></p>
						<p><?php _e( 'With your subscription you get:','woocommerce-improved-external-products' ); ?></p>
						<ul id="datafeedr">
							<li><?php _e( 'Access to 360+ million products','woocommerce-improved-external-products' ); ?></li>
							<li><?php _e( '12,000+ merchants','woocommerce-improved-external-products' ); ?></li>
							<li><?php _e( '30+ affiliate networks','woocommerce-improved-external-products' ); ?></li>
							<li><?php _e( 'Automatic product updates (updating, adding, removing, etc.)','woocommerce-improved-external-products' ); ?></li>
							<li><?php _e( 'Easy product importing (no feeds to download, no coding required)','woocommerce-improved-external-products' ); ?></li>
							<li><?php _e( 'Cloaked affiliate links','woocommerce-improved-external-products' ); ?></li>
							<li><a href="https://affiliates.datafeedr.com/idevaffiliate.php?id=39_8_3_12" target="_blank" rel="nofollow"><?php _e( 'and much more...','woocommerce-improved-external-products' ); ?></a></li>
						</ul>
						<p><a href="https://affiliates.datafeedr.com/idevaffiliate.php?id=39_5_3_9" target="_blank" rel="nofollow" class="button button-primary"><?php _e( 'Click here to signup','woocommerce-improved-external-products' ); ?></a></p>
					</div>
				</div>
        		<div style="margin-top:20px;margin-bottom:40px">
	        		<h2><?php _e( 'Having Trouble?','woocommerce-improved-external-products' ); ?></h2>
					<p><?php _e( 'Email support@wpovernight.com and we\'ll answer your question as quickly as possible.','woocommerce-improved-external-products' ); ?></p>
				</div>
			</form>
			<script type="text/javascript">
			jQuery('.hidden-input').click(function() {
				jQuery(this).closest('.hidden-input').prev('.pro-feature').show('slow');
				jQuery(this).closest('.hidden-input').hide();
			});
			jQuery( document ).ready(function( $ ) {
			    $("input.wcbulkorder-disabled").attr('disabled',true);
			});
		</script>
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
		$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"%5$s/>', $id, $menu, $current, $size, $disabled );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		if (isset( $args['disabled'] ) && !class_exists('ImprovedExternalProductsPro')) {
			$html .= ' <span style="display:none;" class="pro-feature"><i>'. __('This feature only available in', 'woocommerce-improved-external-products') .' <a href="https://wpovernight.com/downloads/improved-external-products-pro/">Improved External Products Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
		}
	
		echo $html;
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
		$html = sprintf( '<textarea rows="4" cols="100" id="%1$s" name="%2$s[%1$s]" %5$s/>%3$s</textarea>', $id, $menu, $current, $size, $disabled );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		if (isset( $args['disabled'] ) && !class_exists('ImprovedExternalProductsPro')) {
			$html .= ' <span style="display:none;" class="pro-feature"><i>'. __('This feature only available in', 'woocommerce-improved-external-products') .' <a href="https://wpovernight.com/downloads/improved-external-products-pro/">Improved External Products Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
		}
	
		echo $html;
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
		
		$html = sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled );
		$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
		
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
		}
		$html .= sprintf( '</select>' );
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
		
		echo $html;
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
			
			$html .= sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled);
			$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
			
			foreach ( (array) $boxes['options'] as $key => $label ) {
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
			}
			$html .= '</select>';
	
			if ( isset( $boxes['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $boxes['description'] );
			}
			$html .= '<br />';
		}
		
		
		echo $html;
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
		$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s %4$s/>', $id, $menu, checked( 1, $current, false ), $disabled );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
	
		echo $html;
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
			$html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s %5$s />', $menu, $id, $key, checked( $current, $key, false ), $disabled );
			$html .= sprintf( '<label for="%1$s[%2$s][%3$s]"> %4$s</label><br>', $menu, $id, $key, $label);
		}
		
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		if (isset( $args['disabled'] ) && !class_exists('ImprovedExternalProductsPro')) {
			$html .= ' <span style="display:none;" class="pro-feature"><i>'. __('This feature only available in', 'woocommerce-improved-external-products') .' <a href="https://wpovernight.com/downloads/improved-external-products-pro/">Improved External Products Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
		}

		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function icons_radio_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		$icons = '';
		$radios = '';
		
		foreach ( $args['options'] as $key => $iconnumber ) {
			$icons .= sprintf( '<td style="padding-bottom:0;font-size:16pt;" align="center"><label for="%1$s[%2$s][%3$s]"><i class="improvedexternalproducts-icon-shopping-cart-%4$s"></i></label></td>', $menu, $id, $key, $iconnumber);
			$radios .= sprintf( '<td style="padding-top:0" align="center"><input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s /></td>', $menu, $id, $key, checked( $current, $key, false ) );
		}
		$html = '<table><tr>'.$icons.'</tr><tr>'.$radios.'</tr></table>';
		$html .= '<p class="description"><i>'. __('<strong>Please note:</strong> you need to open your website in a new tab/browser window after updating the cart icon for the change to be visible!','woocommerce-improved-external-products').'</p>';
		
		echo $html;
	}

	function multicheckbox_element_callback( $args ) {
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
	        $html .= sprintf( '<input type="checkbox" id="%1$s[%4$s][%2$s]" name="%1$s[%4$s][%2$s]" value="%2$s" %3$s %5$s />', $pag, $term->term_id, $checked, $args['id'], $disabled );
	        $html .= sprintf( '<label for="%1$s[%4$s][%3$s]"> %2$s</label><br>', $pag, $term->name, $term->term_id, $args['id'] );
	    }

	    $html .= sprintf( '<span class="description"> %s</span>', '' );
	    if(!class_exists('ImprovedExternalProductsPro')){
		    $html .= ' <span style="display:none;" class="pro-feature"><i>'. __('This feature only available in', 'woocommerce-improved-external-products') .' <a href="https://wpovernight.com/downloads/improved-external-products-pro/">Improved External Products Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
	    }
	    echo $html;

	}

	/**
	 * Text field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Text field.
	 */
	public function heading_element_callback( $args ) {

		$html = '';
		echo $html;

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
						$output[$key] = strip_tags( stripslashes( $input[$key] ) );
					}
				}
				//print_r($output);
			}
		}
		// Return the array processing any additional functions filtered by this action.
		return apply_filters( 'improvedexternalproducts_validate_input', $output, $input );
	}
}