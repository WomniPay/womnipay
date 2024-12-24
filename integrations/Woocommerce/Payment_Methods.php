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

use Womnipay\Engine\Base;

/**
 * Class Payment_Methods
 *
 * Handles the payment methods integration for Woocommerce.
 *
 * @package Womnipay\Integrations\Woocommerce
 */
class Payment_Methods extends Base {

	/**
	 * Initialize the payment methods.
	 *
	 * @return void
	 */
	public function initialize() {
		if ( ! parent::initialize() ) {
			return;
		}

		\add_filter( 'woocommerce_payment_gateways', array( $this, 'add_omnipay_dummy_class' ) );
	}

	/**
	 * Add the Omnipay dummy class to the list of available gateways.
	 *
	 * @param array $gateways List of existing gateways.
	 * @return array Modified list of gateways.
	 */
	public function add_omnipay_dummy_class( $gateways ) {
		$gateways[] = 'Womnipay\Integrations\Woocommerce\Dummy';

		return $gateways;
	}

}
