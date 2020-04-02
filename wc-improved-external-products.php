<?php
/**
 * Plugin Name: WooCommerce Improved External Products
 * Plugin URI: https://wpovernight.com/
 * Description: Opens External/Affiliate products in a new tab.
 * Version: 1.5.5
 * Author: Ewout Fernhout
 * Author URI: http://www.wpovernight.com
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * WC requires at least: 2.6.0
 * WC tested up to: 4.0.0
 */

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
		add_action( 'admin_notices', array( $this, 'go_pro_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'backend_scripts_styles' ) );
	}

	/**
	 * Redirect: Make It So
	 *
	 */
	function iepp_activate() {
		add_option('iepp_do_activation_redirect', true);
	}

	/**
	 * Shows a notice for the Pro version on the order admin pages
	 */
	function go_pro_notice() {
		if ( !isset($GLOBALS['post_type']) || !in_array( $GLOBALS['post_type'], array('shop_order','product') ) ) {
			return;
		}
		
		if ( get_option( 'wpo_iepp_pro_notice_dismissed' ) !== false || get_option( 'iepp_go_pro_notice' ) == 'gopro' ) {
			return;
		} else {
			if ( isset( $_GET['wpo_iepp_dismis_pro'] ) ) {
				update_option( 'wpo_iepp_pro_notice_dismissed', true );
				return;
			}

			// keep track of how many days this notice is show so we can remove it after 7 days
			$notice_shown_on = get_option( 'wpo_iepp_pro_notice_shown', array() );
			$today = date('Y-m-d');
			if ( !in_array($today, $notice_shown_on) ) {
				$notice_shown_on[] = $today;
				update_option( 'wpo_iepp_pro_notice_shown', $notice_shown_on );
			}
			// count number of days pro is shown, dismiss forever if shown more than 7
			if (count($notice_shown_on) > 7) {
				update_option( 'wpo_iepp_pro_notice_dismissed', true );
				return;
			}

			?>
			<div class="notice notice-info is-dismissible wpo-iepp-pro-notice">
				<h3><?php _e( 'Thank you for using Improved External Products! Check out our pro version:', 'woocommerce-improved-external-products' ); ?></h3>
				<ul class="ul-square">
					<li><?php _e( 'Ability to open external products in a new tab from product archives', 'woocommerce-improved-external-products' ) ?></li>
					<li><?php _e( 'Set tab action on a per-product basis', 'woocommerce-improved-external-products' ) ?></li>
					<li><?php _e( 'Set tab action on a product category basis', 'woocommerce-improved-external-products' ) ?></li>
					<li><?php _e( 'Priority Customer Support', 'woocommerce-improved-external-products' ) ?></li>
				</ul>
				<p><a href="https://wpovernight.com/downloads/improved-external-products-pro/" target="_blank"><?php _e( 'Click here to go Pro now!', 'woocommerce-improved-external-products' ) ?></a></p>
				<p><a href="<?php echo esc_url( add_query_arg( 'wpo_iepp_dismis_pro', true ) ); ?>" class="wpo-iepp-dismiss"><?php _e( 'Dismiss this notice', 'woocommerce-improved-external-products' ); ?></a></p>
			</div>
			<?php
		}
	}

	function backend_scripts_styles() {
		if ( isset($GLOBALS['post_type']) && in_array( $GLOBALS['post_type'], array('shop_order','product') ) ) {
			wp_enqueue_script(
				'wpo-iepp-admin',
				untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/js/admin-script.js',
				array( 'jquery' )
			);
		}
	}

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
	 * @subpackage  Product
	 */
	function iepp_external_add_to_cart() {
		global $product;
		$product = wc_get_product($product);
		$product_url = $product->get_product_url();
		$button_text = $product->single_add_to_cart_text();
		if ( ! $product_url || ! $button_text  ) {
			return;
		}

		$target = $this->determine_link_target( $this->get_product_id( $product ) );
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
			$product = wc_get_product(get_the_ID());

			/* If the product is external */
			if($product->is_type( 'external' )){
				if($this->determine_link_target( $this->get_product_id( $product ) ) == true){
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

	// Backwards compatible product ID getter for WC2.6 and older
	function get_product_id( $product ) {
		return method_exists( $product, 'get_id') ? $product->get_id() : $product->id;
	}
	
}
$ImprovedExternalProducts = new ImprovedExternalProducts();