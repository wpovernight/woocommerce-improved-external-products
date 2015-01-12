<?php
/*
Plugin Name: WooCommerce Improved External Products
Plugin URI: https://wpovernight.com/
Description: Opens External/Affiliate products in a new tab.
Version: 1.1
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
		add_filter( 'woocommerce_loop_add_to_cart_link', array(&$this,'shop_loop_link'), 10, 2 );
		//add_action('plugins_loaded', array(&$this,'product_page_link'));
		add_filter( 'woocommerce_locate_template', array(&$this,'myplugin_woocommerce_locate_template'), 10, 3 );
	}
	/**
	 * Output the external product add to cart area.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
	 */
	function product_page_link() {
		global $woocommerce;
		global $product;
		$product_url = get_post_meta( $product->id, '_product_url', true  );
		$button_text = get_post_meta( $product->id, '_button_text', true  );
		if ( ! $product_url ) return;
		woocommerce_get_template( '../../woocommerce-improved-external-products/external.php', array(
				'product_url' => $product_url,
				'button_text' => ( $button_text ) ? $button_text : __( 'Buy product', 'woocommerce' ) ,
			) );
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
}
$ImprovedExternalProducts = new ImprovedExternalProducts();