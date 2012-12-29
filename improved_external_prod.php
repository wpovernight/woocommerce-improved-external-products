<?php
/*
Plugin Name: WooCommerce Improved External Products
Plugin URI: www.wpovernight.com/plugins
Description: Opens External/Affiliate products in a new tab.
Version: 1.0
Author: Jeremiah Prummer
Author URI: www.wpovernight.com/about
License: GPL2
*/
/*  Copyright 2012 Jeremiah Prummer (email : jeremiah.prummer@yahoo.com)

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
global $woocommerce;
if ( ! function_exists( 'woocommerce_external_add_to_cart' ) ) {

	/**
	 * Output the external product add to cart area.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
	 */
	function woocommerce_external_add_to_cart() {
		global $product;

		$product_url = get_post_meta( $product->id, '_product_url', true  );
		$button_text = get_post_meta( $product->id, '_button_text', true  );

		if ( ! $product_url ) return;

		woocommerce_get_template( '../../external_newtab/external.php', array(
				'product_url' => $product_url,
				'button_text' => ( $button_text ) ? $button_text : __( 'Buy product', 'woocommerce' ) ,
			) );
	}
}
?>