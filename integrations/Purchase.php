<?php
/**
 * Handles the purchase process for Womnipay integration.
 *
 * @package Womnipay\Integrations
 */

namespace Womnipay\Integrations;

use Omnipay\Omnipay;
use Womnipay\Engine\Base;

/**
 * Class Purchase
 *
 * Handles the purchase process for Womnipay integration.
 */
class Purchase extends Base {

	/**
	 * @var \Omnipay\Common\GatewayInterface
	 */
	private $gateway;

	/**
	 * Constructor for the Purchase class.
	 */
	public function __construct() {
		\add_action( 'womnipay_purchase', array( $this, 'womnipay_purchase_func' ), 10, 4 );
		\add_action( 'womnipay_receipt_page_purchase', array( $this, 'womnipay_receipt_page_purchase_offsite' ), 10, 3 );
		\add_filter( 'womnipay_process_payment_purchase', array( $this, 'womnipay_process_payment_purchase_onsite' ), 10, 3 );
		\add_filter( 'womnipay_card', array( $this, 'womnipay_card_fields' ), 10, 1 );
		\add_filter( 'womnipay_credit_card', array( $this, 'womnipay_credit_card_fields' ), 10, 2 );
	}

	/**
	 * Prepares the purchase data for the gateway.
	 *
	 * @param \WC_Order $order The order object.
	 * @param array     $card The credit card details.
	 * @return array The purchase data.
	 */
	public function womnipay_gateway_purchase( $order, $card ) {
		return array(
			'amount'        => $order->get_total(),
			'currency'      => $order->get_currency(),
			'card'          => $card,
			'transactionId' => $order->get_order_number(),
			'returnUrl'     => home_url( '/wc-api/' . $order->get_payment_method() ),
			'notifyUrl'     => home_url( '/wc-api/' . $order->get_payment_method() ),
		);
	}

	public function womnipay_init( string $omnipay_gateway, array $omnipay_initialize ) {
		$this->gateway = Omnipay::create( $omnipay_gateway );
		$this->gateway->initialize( $omnipay_initialize );
	}

	/**
	 * Handles the purchase process.
	 *
	 * @param string $omnipay_gateway The Omnipay gateway.
	 * @param int    $order_id The order ID.
	 * @param array  $omnipay_initialize The Omnipay initialization parameters.
	 * @param string $purchase_type The purchase type.
	 */
	public function womnipay_purchase_func( string $omnipay_gateway, int $order_id, array $omnipay_initialize, string $purchase_type ) {
		$this->womnipay_init( $omnipay_gateway, $omnipay_initialize );
		$order = wc_get_order( $order_id );
		$card  = \apply_filters( 'womnipay_card', $order );
		do_action( $purchase_type, $order, $this->gateway, $card );
	}

	/**
	 * Retrieves the credit card fields from the order.
	 *
	 * @param \WC_Order $order The order object.
	 * @return array The credit card fields.
	 */
	public function womnipay_card_fields( $order ) {
		return array(
			'firstName'       => $order->get_billing_first_name(),
			'lastName'        => $order->get_billing_last_name(),
			'phone'           => $order->get_billing_phone(),
			'email'           => $order->get_billing_email(),
			'billingAddress1' => $order->get_billing_address_1(),
			'billingAddress2' => $order->get_billing_address_2(),
			'billingCity'     => $order->get_billing_city(),
			'billingPostcode' => $order->get_billing_postcode(),
			'billingCountry'  => $order->get_billing_country(),
			'billingState'    => $order->get_billing_state(),
		);
	}

	/**
	 * Validates and retrieves the credit card fields from the order.
	 *
	 * @param \WC_Order $order The order object.
	 * @param string    $gateway_id The gateway ID.
	 * @return array The validated credit card fields.
	 */
	public function womnipay_credit_card_fields( $order, $gateway_id ) {
		$fields = $this->womnipay_card_fields( $order );

		$card_expiry = $this->validate_card_field( 'card-expiry', $gateway_id );

		$fields['number']      = $this->validate_card_field( 'card-number', $gateway_id );
		$fields['expiryMonth'] = explode( '/', $card_expiry )[0];
		$fields['expiryYear']  = explode( '/', $card_expiry )[1];
		$fields['cvv']         = $this->validate_card_field( 'card-cvc', $gateway_id );

		return $fields;
	}

	/**
	 * Validates a specific credit card field.
	 *
	 * @param string $card_field The card field to validate.
	 * @param string $gateway_id The gateway ID.
	 * @return string The sanitized card field value.
	 */
	public function validate_card_field( string $card_field, $gateway_id ): string {
		if ( isset( $_POST[ "$gateway_id-$card_field" ] ) ) {
			if ( isset( $_POST['woocommerce-process-checkout-nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' )
			) {
				return sanitize_text_field( wp_unslash( $_POST[ "$gateway_id-$card_field" ] ) );
			}

			return 0;
		}

		return 0;
	}

	/**
	 * Processes the payment purchase onsite.
	 *
	 * @param \WC_Order $order The order object.
	 * @param object    $gateway The payment gateway object.
	 * @param array     $card The credit card details.
	 * @return array The result of the payment process.
	 */
	public function womnipay_process_payment_purchase_onsite( $order, $gateway, $card ): array {
		$purchase = $this->womnipay_gateway_purchase( $order, $card, $gateway_id );

		try {
			$response = $gateway->purchase( $purchase )->send();

			if ( $response->isSuccessful() ) {
				return \apply_filters( 'womnipay_payment_success_message', $order, $response->getTransactionReference(), $redirect );
			}

            return \apply_filters( 'womnipay_payment_error_message', $order, $response->getMessage() );
		} catch ( \Throwable $e ) {
			return \apply_filters( 'womnipay_payment_error_message', $order, $e->getMessage() );
		}
	}

	/**
	 * Processes the payment purchase and redirects.
	 *
	 * @param \WC_Order $order The order object.
	 * @param object    $gateway The payment gateway object.
	 * @param array     $card The credit card details.
	 * @return void The result of the payment process.
	 */
	public function womnipay_receipt_page_purchase_offsite( $order, $gateway, $card ): array {
		$purchase = $this->womnipay_gateway_purchase( $order, $card );
		
		try {
			$response = $gateway->purchase( $purchase )->send();

			if ( $response->isSuccessful() ) {
				wp_die( $response->redirect() );
			} elseif ( $response->isRedirect() === 1 ) {
				wp_die( $response->redirect() );
			} else {
				echo $response->getMessage();
			}
		} catch ( \Throwable $e ) {
			echo $e->getMessage();
		}
	}

}
