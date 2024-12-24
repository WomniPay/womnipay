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

use WC_Payment_Gateway;

/**
 * Payment Gateway class for WomniPay integration with WooCommerce.
 *
 * @package Womnipay
 */
class Payment_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the Payment Gateway class.
	 */
	public function __construct() {
		\add_filter( 'womnipay_woo_init_form', array( $this, 'womnipay_woo_init_form_fields' ), 10, 0 );
		\add_filter( 'womnipay_payment_error_message', array( $this, 'payment_message_failure' ), 10, 2 );
		\add_filter( 'womnipay_payment_success_message', array( $this, 'payment_message_success' ), 10, 2 );
	}

	/**
	 * Initialize form fields for WomniPay.
	 *
	 * @return array Form fields for the payment gateway.
	 */
	public function womnipay_woo_init_form_fields(): array {
		return array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', WOPNP_TEXTDOMAIN ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable WomniPay Payment method', WOPNP_TEXTDOMAIN ),
				'default' => 'no',
			),
			'title'       => array(
				'title'       => __( 'Title', WOPNP_TEXTDOMAIN ),
				'type'        => 'text',
				'description' => __( 'WomniPay Payment method', WOPNP_TEXTDOMAIN ),
				'default'     => __( 'WomniPay Payment', WOPNP_TEXTDOMAIN ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'   => __( 'Description', WOPNP_TEXTDOMAIN ),
				'type'    => 'textarea',
				'default' => 'WomniPay Payment method description.',
			),

		);
	}

	/**
	 * Handles payment failure messages.
	 *
	 * @param \WC_Order $order The order object.
	 * @param string    $message The error message.
     * @return array The result array with failure status.
	 */
	public function payment_message_failure( \WC_Order $order, string $message ): array {
		$order_note = __( 'Payment error: ', WOPNP_TEXTDOMAIN ) . $message;
		$order->add_order_note( $order_note );
		wc_add_notice( $order_note, 'error' );

		return array(
			'result' => 'failure',
		);
	}

	/**
	 * Handles payment success messages.
	 *
	 * @param \WC_Order $order The order object.
	 * @param string    $message The success message.
     * @return array The result array with success status and redirect URL.
	 */
	public function payment_message_success( \WC_Order $order, string $message ): array {
		$order_note = __( 'Transaction Reference: ', WOPNP_TEXTDOMAIN ) . $message;
		$order->add_order_note( $order_note );
		$order->payment_complete();

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

}
