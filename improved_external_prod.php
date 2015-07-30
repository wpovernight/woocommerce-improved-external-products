<?php
/*
Plugin Name: WooCommerce Improved External Products
Plugin URI: https://wpovernight.com/
Description: Opens External/Affiliate products in a new tab.
Version: 1.2.1
Author: Jeremiah Prummer
Author URI: https://wpovernight.com/
License: GPL2
*/
/*  Copyright 2012-2015 Jeremiah Prummer (email : jeremiah.prummer@yahoo.com)
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
		if(!class_exists('ImprovedExternalProductsPro')){
			add_filter( 'woocommerce_loop_add_to_cart_link', array($this,'shop_loop_link'), 10, 2 );
			add_filter( 'woocommerce_locate_template', array($this,'myplugin_woocommerce_locate_template'), 10, 3 );
			add_action('wp_footer', array($this,'javascript_backup'));
		}
		$this->includes();
		$this->settings = new ImprovedExternalProducts_Settings();

		// Redirect to the Settings Page
		// Settings Page URL
		define("IEPP_SETTINGS_URL", "admin.php?page=iepp_options_page");
		// Redirect to settings page on activation
		register_activation_hook(__FILE__, array(&$this,'iepp_activate'));
		add_action('admin_init', array(&$this,'iepp_redirect'));
	}

	/**
	 * Redirect: Make It So
	 *
	 */
	function iepp_activate() {
		add_option('iepp_do_activation_redirect', true);
	}
	
	function iepp_redirect() {
		if (get_option('iepp_do_activation_redirect', false)) {
			delete_option('iepp_do_activation_redirect');
			if(!isset($_GET['activate-multi'])){
				wp_redirect(IEPP_SETTINGS_URL);
			}
		}
	}

	function includes(){
		require_once( 'includes/settings.php' );
	}

	function shop_loop_link($link,$product){
	    if($product->product_type == 'external'){
	    	$doc = new DOMDocument();
			$doc->loadHTML($link);
			$links = $doc->getElementsByTagName('a');
			foreach ($links as $item) {
			    if (!$item->hasAttribute('target'))
			        $item->setAttribute('target','_blank');  
			}
			$link=$doc->saveHTML();
			$link=html_entity_decode($link);
	    }
		return $link;
	}
	
	function myplugin_plugin_path() {
		// gets the absolute path to this plugin directory
		return untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/woocommerce/'; 
	}
	 
	function myplugin_woocommerce_locate_template( $template, $template_name, $template_path ) {
	 
		global $woocommerce;
		$_template = $template;

		//echo $template_name;
	 
		if ( ! $template_path ) $template_path = $woocommerce->template_url;
	 
	 	$plugin_path  = $this->myplugin_plugin_path();

		// Look within passed path within the theme - this is priority 
		$template = locate_template(
		    array(
		    	$template_path . $template_name,
		      	$template_name 
		    )
		);
		 
		// Modification: Get the template from this plugin, if it exists
		if ( ! $template && file_exists( $plugin_path . $template_name ) )
		    $template = $plugin_path . $template_name; 
		
		// Use default template 
		if ( ! $template )
		    $template = $_template;
		
		// Return what we found 
		return $template;

	}

	function javascript_backup(){
		if(is_product()){
			?>
			<script type="text/javascript">
			jQuery( document ).ready(function( $ ) {
				if($('div.product-type-external').length > 0){
					var attr = $('a.single_add_to_cart_button').attr('target');
					if (typeof attr === typeof undefined || attr === false) {
					    $('a.single_add_to_cart_button').attr('target','_blank');
					}
				}
			});
			</script>
			<?php
		}
	}
}
$ImprovedExternalProducts = new ImprovedExternalProducts();