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

use Womnipay\Engine\Base;

/**
 * Init new Payments
 */
class Payment_Methods extends Base {

	/**
	 * Initialize the class.
	 *
	 * @return void|bool
	 */
	public function initialize() {
		if ( !parent::initialize() ) {
			return;
		}

        \add_filter( 'woocommerce_payment_gateways', array( $this, 'add_omnipay_dummy_class' ) );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since 1.0.0
	 * @param array $gateways List of available payment gateways.
	 * @return array
	 */
    public function add_omnipay_dummy_class( $gateways ) {
		$gateways[] = 'Womnipay\Integrations\Dummy';

		return $gateways;
	}

}
