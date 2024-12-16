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

namespace Womnipay\Integrations;

use Omnipay\Omnipay;
use WC_Payment_Gateway_CC;

/**
 * Dummy Payment Gateway
 *
 * This class provides a dummy payment gateway for testing purposes.
 *
 * @package Womnipay\Integrations
 */
class Dummy extends WC_Payment_Gateway_CC {

    public function __construct() {
        $this->id                 = 'wop_dummy';
        $this->icon               = plugin_dir_url( __DIR__ ) . 'assets/img/icon-credit-card.svg';
        $this->has_fields         = true;
        $this->method_title       = __( 'WomniPay - Dummy', WOPNP_TEXTDOMAIN );
        $this->method_description = __( 'Allows your store to use Omnipay Dummy Payment methods.', WOPNP_TEXTDOMAIN );
        $this->init_form_fields();
        $this->init_settings();

        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );

        \add_action( "woocommerce_update_options_payment_gateways_{$this->id}", array( $this, 'process_admin_options' ) );
    }

    public function admin_options() {
        ?>
        <h3><?php esc_html_e( 'WomniPay Dummy', WOPNP_TEXTDOMAIN ); ?></h3>
        <p><?php esc_html_e( 'This is a dummy gateway intended for testing purposes. If you provide a card number ending in an even number, the driver will return a success response. If it ends in an odd number, the driver will return a generic failure response.', WOPNP_TEXTDOMAIN ); ?></p>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table>
		<?php
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'     => array(
                'title'   => __( 'Enable/Disable', WOPNP_TEXTDOMAIN ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable WomniPay Dummy Payment method', WOPNP_TEXTDOMAIN ),
                'default' => 'no',
            ),
            'title'       => array(
                'title'       => __( 'Title', WOPNP_TEXTDOMAIN ),
                'type'        => 'text',
                'description' => __( 'WomniPay Dummy Payment method', WOPNP_TEXTDOMAIN ),
                'default'     => __( 'WomniPay Payment', WOPNP_TEXTDOMAIN ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'   => __( 'Description', WOPNP_TEXTDOMAIN ),
                'type'    => 'textarea',
                'default' => 'Test Credit Cards: Success - 4929000000006 | Failed - 4444333322221111',
            ),
            'instruction' => array(
                'title'   => __( 'Instruction', WOPNP_TEXTDOMAIN ),
                'type'    => 'textarea',
                'default' => 'Instruction',
            ),
            
        );
    }

    public function payment_fields() {
        echo esc_html( $this->description );

        parent::payment_fields();
    }

    public function validate_fields() {
        parent::validate_fields();
    }

    /**
     * Process the payment
     *
     * Process the payment for the given order ID.
     *
     * @param int $order_id The order ID.
     */
    public function process_payment( $order_id ): array {
        $order = wc_get_order( $order_id );

        $gateway = Omnipay::create( 'Dummy' );

        $gateway->initialize(
            array(
				'testMode' => true, // Doesn't really matter what you use here.
        )
            );

        $card_expiry = $this->validate_card_field( 'card-expiry' );

        $card = array(
            'firstName'   => $order->get_billing_first_name(),
            'lastName'    => $order->get_billing_last_name(),
            'number'      => $this->validate_card_field( 'card-number' ),
            'expiryMonth' => explode( '/', $card_expiry )[0],
            'expiryYear'  => explode( '/', $card_expiry )[1],
            'cvv'         => $this->validate_card_field( 'card-cvc' ),
        );

        try {
            $response = $gateway->purchase(
                array(
					'amount'   => $order->get_total(),
					'currency' => $order->get_currency(),
					'card'     => $card,
            )
                )->send();

            if ( !$response->isSuccessful() ) {
                return $this->payment_error_message( $order, $response->getMessage() );
            }
        } catch ( \Throwable $e ) {
            return $this->payment_error_message( $order, $e->getMessage() );
        }

        return $this->payment_success_message( $order, $response->getTransactionReference() );
	}

    /**
     * Validate card field
     *
     * @param string $card_field The card field to validate.
     * @return int The sanitized card field value.
     */
    public function validate_card_field( string $card_field ): string {
        if ( isset( $_POST["$this->id-$card_field"] ) ) {
            if ( isset( $_POST['woocommerce-process-checkout-nonce'] ) &&
                wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' )
            ) {
                return sanitize_text_field( wp_unslash( $_POST["$this->id-$card_field"] ) );
            }

            return 0;
        }

        return 0;
    }

    /**
     * Handle payment error message
     *
     * @param \WC_Order $order The order object.
     * @param string    $message  The error message to be added to the order note.
     */
    public function payment_error_message( \WC_Order $order, string $message ): array {
        $order_note = __( 'Payment error: ', WOPNP_TEXTDOMAIN ) . $message;
        $order->add_order_note( $order_note );
        wc_add_notice( $order_note, 'error' );

        return array(
            'result' => 'failure',
        );
    }

    /**
     * Handle payment success message
     *
     * @param \WC_Order $order The order object.
     * @param string    $message The success message to be added to the order note.
     */
    public function payment_success_message( \WC_Order $order, string $message ): array {
        $order_note = __( 'Transaction Reference: ', WOPNP_TEXTDOMAIN ) . $message;
        $order->add_order_note( $order_note );
        $order->payment_complete();

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }

}