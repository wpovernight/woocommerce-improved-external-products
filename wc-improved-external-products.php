<?php
/*
Plugin Name: WooCommerce Improved External Products
Plugin URI: https://wpovernight.com/
Description: Opens External/Affiliate products in a new tab.
Version: 1.4.1
Author: Jeremiah Prummer
Author URI: https://wpovernight.com/
License: GPL2
*/
/*  Copyright 2012-2016 Jeremiah Prummer (email : support@wpovernight.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/
?>
<?php
class ImprovedExternalProducts {
	
	/**
	 * Construct.
	 */
	public function __construct() {
		
		// Print the js
		add_action( 'wp_footer', array($this,'add_js_to_footer') );

		// Redirect to the Settings Page
		// Settings Page URL
		define("IEPP_SETTINGS_URL", "admin.php?page=iepp_options_page");
		// Redirect to settings page on activation
		register_activation_hook(__FILE__, array($this,'iepp_activate'));
		add_action('admin_init', array($this,'iepp_redirect'));
		// Get included files
		add_action('wp_loaded',array($this,'includes'));

		add_action('init',array($this,'modify_external_product_links'));
		// Display the admin notification
		add_action( 'admin_notices', array( $this, 'plugin_activation' ) ) ;
	}

	/**
	 * Redirect: Make It So
	 *
	 */
	function iepp_activate() {
		add_option('iepp_do_activation_redirect', true);
	}

	/**
	 * Saves the version of the plugin to the database and displays an activation notice on where users
	 * can access the new options.
	 */
	function plugin_activation() {

		if( 'gopro' != get_option( 'iepp_go_pro_notice' ) ) {

			add_option( 'iepp_go_pro_notice', 'gopro' );

			$html = '<div class="notice-success is-dismissible">';
				$html .= '<p>';
					$html .= 'Thank you for using Improved External Products. Check out our pro version! <a href="https://wpovernight.com/downloads/improved-external-products-pro/" target="_blank">Click to Go Pro!</a>.';
				$html .= '</p>';
			$html .= '</div>';

			echo $html;

		} // end if

	} // end plugin_activation
	
	function iepp_redirect() {
		if (get_option('iepp_do_activation_redirect', false)) {
			delete_option('iepp_do_activation_redirect');
			if(!isset($_GET['activate-multi'])){
				wp_redirect(IEPP_SETTINGS_URL);
			}
		}
	}

	function modify_external_product_links(){
		/* single product actions */
		remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
		add_action( 'woocommerce_external_add_to_cart', array($this,'iepp_external_add_to_cart'), 30 );
	}

	/**
	 * Output the external product add to cart area.
	 *
	 * @subpackage	Product
	 */
	function iepp_external_add_to_cart() {
		global $product;
		if ( ! $product->add_to_cart_url() ) {
			return;
		}

		$product_url = $product->add_to_cart_url();
		$button_text = $product->single_add_to_cart_text();
		$target = $this->determine_link_target($product->id);
		$price_html = $product->get_price_html();
		if($target == true){
			$target = '_blank';
		} else {
			$target = '_self';
		}
		?>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php
		$options = get_option('improvedexternalproducts');
		if(!empty($options['custom_single_button_html'])){
			$html = $options['custom_single_button_html'];
			$html = str_replace('{product_url}', esc_url( $product_url ), $html);
			$html = str_replace('{target}', $target, $html);
			$html = str_replace('{button_text}', esc_html( $button_text ), $html);
			$html = str_replace('{price_html}', $price_html, $html);
			echo $html;
		} else {
		?>
			<p class="cart">
				<a href="<?php echo esc_url( $product_url ); ?>" rel="nofollow" class="single_add_to_cart_button button alt" target="<?php echo $target; ?>"><?php echo esc_html( $button_text ); ?></a>
			</p>
		<?php } ?>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
		<?php
	}

	function includes(){
		if(!class_exists('ImprovedExternalProducts_Settings')){
			require_once( 'includes/wc-improved-external-products-settings.php' );
			// Get settings
			$this->settings = new ImprovedExternalProducts_Settings();
		}
	}

	function add_js_to_footer(){
		global $woocommerce, $product;
		$options = get_option('improvedexternalproducts');
		//$extra_selectors = $options['additional_javascript_selectors'];
		/* Add code to product page */
		if(is_product()){
			$product = get_product(get_the_ID());

			/* If the product is external */
			if($product->is_type( 'external' )){
				if($this->determine_link_target($product->id) == true){
					$target = '_blank';
				} else {
					$target = '';
				}
				/*
				if($target == '_blank'){
					?>
					<script type="text/javascript">
						jQuery( document ).ready(function( $ ) {
							$('a.single_add_to_cart_button <?php echo $extra_selectors; ?>').attr('target','_blank');
						});
					</script>
					<?php
				}*/
			}
		}
	}

	function determine_link_target($product_id){
		return true;
	}
	
}
$ImprovedExternalProducts = new ImprovedExternalProducts();