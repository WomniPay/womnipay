<?php
/**
 * Womnipay
 *
 * @package   Womnipay
 * @author    WomniPay <info@womnipay.com>
 * @copyright WomniPay
 * @license   GPL 3
 * @link      https://womnipay.com
 */

namespace Womnipay\Integrations\Woocommerce;

use Omnipay\Omnipay;
use WC_Payment_Gateway_CC;

/**
 * Wp_Payment_Gateway_Cc class.
 *
 * Extends the WooCommerce Payment Gateway CC class to integrate Womnipay.
 */
class Wp_Payment_Gateway_Cc extends WC_Payment_Gateway_CC {

	/**
	 * Constructor for the Wp_Payment_Gateway_Cc class.
	 */
	public function __construct() {
		$this->icon               = plugin_dir_url( __DIR__ ) . 'assets/img/icon-credit-card.svg';
		$this->has_fields         = true;
		$this->method_title       = __( 'WomniPay', WOPNP_TEXTDOMAIN );
		$this->method_description = __( 'Allows your store to use Womnipay Payment methods.', 'womnipay-textdomain' );
		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		\add_action( "woocommerce_update_options_payment_gateways_{$this->id}", array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize form fields for the payment gateway.
	 */
	public function init_form_fields() {
		$this->form_fields = \apply_filters( 'womnipay_woo_init_form', array(), 10, 0 );
	}

	/**
	 * Outputs the payment fields on the checkout page.
	 */
	public function payment_fields() {
		echo esc_html( $this->description );

		parent::payment_fields();
	}

	/**
	 * Process the payment for an order.
	 *
	 * @param int $order_id The ID of the order being processed.
	 * @return array The result of the payment processing.
	 */
	public function process_payment( $order_id ): array {
		$order = wc_get_order( $order_id );

		$gateway = Omnipay::create( $this->omnipay_gateway );

		$gateway->initialize( $this->omnipay_initialize );

		$card = \apply_filters( 'womnipay_credit_card', $order, $this->id );

		return \apply_filters( 'womnipay_process_payment_purchase', $order, $gateway, $card );
	}

}
