<?php
/**
 * Plugin Name:          Improved External Products for WooCommerce
 * Plugin URI:           https://wpovernight.com/downloads/improved-external-products-pro/
 * Description:          Opens External/Affiliate products in a new tab.
 * Version:              1.6.9
 * Author:               WP Overnight
 * Author URI:           https://wpovernight.com/
 * License:              GPLv2 or later
 * License URI:          https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:          woocommerce-improved-external-products
 * WC requires at least: 3.0
 * WC tested up to:      9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ImprovedExternalProducts {
	
	/**
	 * @var string
	 */
	protected $plugin_version = '1.6.9';
	
	/**
	 * @var WPO_WCIEP_Settings
	 */
	public $settings;
	
	/**
	 * @var WPO_WCIEP_Order_Util
	 */
	public $order_util;

	/**
	 * Construct.
	 */
	public function __construct() {
		
		// Load plugin text domain.
		add_action( 'init', array( $this, 'translations' ), 9 );
		
		// Modify external product links
		add_action( 'init',array( $this,'modify_external_product_links' ) );
		
		// Get included files
		add_action( 'wp_loaded', array( $this,'includes' ), 9 );

		$this->define( 'WC_IEP_VERSION', $this->plugin_version );

		// Redirect to the Settings Page
		// Settings Page URL
		define("IEPP_SETTINGS_URL", "admin.php?page=iepp_options_page");
		// Redirect to settings page on activation
		register_activation_hook(__FILE__, array($this,'iepp_activate'));
		add_action('admin_init', array($this,'iepp_redirect'));
		
		// Display the admin notification
		add_action( 'admin_notices', array( $this, 'go_pro_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'backend_scripts_styles' ) );
		
		// HPOS compatibility
		add_action( 'before_woocommerce_init', array( $this, 'woocommerce_hpos_compatible' ) );
	}

	/**
	 * Load plugin text domain
	 */
	public function translations() {
		load_plugin_textdomain( 'woocommerce-improved-external-products', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Redirect: Make It So
	 *
	 */
	public function iepp_activate() {
		add_option('iepp_do_activation_redirect', true);
	}
	
	/**
	 * Declares WooCommerce HPOS compatibility.
	 *
	 * @return void
	 */
	public function woocommerce_hpos_compatible() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}

	/**
	 * Shows a notice for the Pro version on the order admin pages
	 */
	public function go_pro_notice() {
		$screen = $this->order_util->custom_order_table_screen();

		if (
			( isset( $_REQUEST['page'] ) && 'iepp_options_page' !== $_REQUEST['page'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			! in_array( $screen, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders', 'edit-product', 'product' ) )
		) {
			return;
		}
				
		if ( get_option( 'wpo_iepp_pro_notice_dismissed' ) !== false || 'gopro' === get_option( 'iepp_go_pro_notice' ) ) {
			return;
		} else {
			if ( isset( $_GET['wpo_iepp_dismis_pro'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpo_iepp_dismis_pro_notice' ) ) {
				update_option( 'wpo_iepp_pro_notice_dismissed', true );
				return;
			}

			// keep track of how many days this notice is show so we can remove it after 7 days
			$notice_shown_on = get_option( 'wpo_iepp_pro_notice_shown', array() );
			$today = gmdate('Y-m-d');
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
				<h3><?php esc_html_e( 'Thank you for using Improved External Products! Check out our pro version:', 'woocommerce-improved-external-products' ); ?></h3>
				<ul class="ul-square">
					<li><?php esc_html_e( 'Ability to open external products in a new tab from product archives', 'woocommerce-improved-external-products' ) ?></li>
					<li><?php esc_html_e( 'Set tab action on a per-product basis', 'woocommerce-improved-external-products' ) ?></li>
					<li><?php esc_html_e( 'Set tab action on a product category basis', 'woocommerce-improved-external-products' ) ?></li>
					<li><?php esc_html_e( 'Priority Customer Support', 'woocommerce-improved-external-products' ) ?></li>
				</ul>
				<p><a href="https://wpovernight.com/downloads/improved-external-products-pro/" target="_blank"><?php esc_html_e( 'Click here to go Pro now!', 'woocommerce-improved-external-products' ) ?></a></p>
				<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_iepp_dismis_pro', true ), 'wpo_iepp_dismis_pro_notice' ) ); ?>" class="wpo-iepp-dismiss"><?php esc_html_e( 'Dismiss this notice', 'woocommerce-improved-external-products' ); ?></a></p>
			</div>
			<?php
		}
	}

	public function backend_scripts_styles() {
		$screen = $this->order_util->custom_order_table_screen();

		if (
			( isset( $_REQUEST['page'] ) && 'iepp_options_page' === $_REQUEST['page'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			in_array( $screen, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders', 'edit-product', 'product' ) )
		) {
			wp_enqueue_script(
				'wpo-iepp-admin',
				untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/js/admin-script.js',
				array( 'jquery' ),
				WC_IEP_VERSION,
				true
			);
		}
	}

	public function iepp_redirect() {
		if ( get_option( 'iepp_do_activation_redirect', false ) ) {
			delete_option( 'iepp_do_activation_redirect' );
			if ( ! isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_redirect( IEPP_SETTINGS_URL );
			}
		}
	}

	public function modify_external_product_links(){
		/* single product actions */
		remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
		add_action( 'woocommerce_external_add_to_cart', array($this,'iepp_external_add_to_cart'), 30 );
	}

	/**
	 * Output the external product add to cart area.
	 *
	 * @subpackage  Product
	 */
	public function iepp_external_add_to_cart() {
		global $product;

		$product     = wc_get_product($product);
		$product_url = is_callable( array( $product, 'get_product_url' ) ) ? $product->get_product_url() : false;
		$button_text = $product->single_add_to_cart_text();
		if ( ! $product_url || ! $button_text  ) {
			return;
		}

		$target     = $this->determine_link_target( $product->get_id() );
		$price_html = $product->get_price_html();
		if ( $target ) {
			$target = '_blank';
		} else {
			$target = '_self';
		}
		?>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php
		$options = get_option('woocommerce-improved-external-products');
		if ( ! empty( $options['custom_single_button_html'] ) ) {
			$html = wp_kses_post( $options['custom_single_button_html'] );
			$html = str_replace( '{product_url}', esc_url( $product_url ), $html );
			$html = str_replace( '{target}', esc_attr( $target ), $html );
			$html = str_replace( '{button_text}', esc_html( $button_text ), $html );
			$html = str_replace( '{price_html}', esc_html( $price_html ), $html );
			echo wp_kses_post( $html );
		} else {
		?>
			<p class="cart">
				<a href="<?php echo esc_url( $product_url ); ?>" rel="nofollow" class="single_add_to_cart_button button alt" target="<?php echo esc_attr( $target ); ?>"><?php echo esc_html( $button_text ); ?></a>
			</p>
		<?php } ?>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
		<?php
	}

	public function includes() {
		if ( ! class_exists( 'WPO_WCIEP_Order_Util' ) ) {
			require_once( 'includes/class-wciep-order-util.php' );
			// Get settings
			$this->order_util = WPO_WCIEP_Order_Util::instance();
		}
		if ( ! class_exists( 'WPO_WCIEP_Settings' ) ) {
			require_once( 'includes/class-wciep-settings.php' );
			// Get settings
			$this->settings = WPO_WCIEP_Settings::instance();
		}
	}

	public function determine_link_target($product_id){
		return true;
	}
}

$ImprovedExternalProducts = new ImprovedExternalProducts();