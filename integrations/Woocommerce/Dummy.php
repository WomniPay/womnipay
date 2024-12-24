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

/**
 * Dummy payment gateway for testing purposes.
 *
 * @package Womnipay
 */
class Dummy extends Wp_Payment_Gateway_Cc {

	/**
	 * Gateway ID
	 *
	 * @var string
	 */
	public $id = 'wop_dummy';

	/**
	 * Omnipay gateway name
	 *
	 * @var string
	 */
	public $omnipay_gateway = 'Dummy';

	/**
	 * Omnipay initialization settings
	 *
	 * @var array
	 */
	public $omnipay_initialize = array(
		'testMode' => true,
	);

	/**
	 * Initialize form fields for the dummy payment gateway.
	 */
	public function init_form_fields() {
		parent::init_form_fields();

		$this->form_fields['description'] = array(
			'title'       => __( 'Description', WOPNP_TEXTDOMAIN ),
			'type'        => 'textarea',
			'description' => __( 'Use test Credit Cards', WOPNP_TEXTDOMAIN ),
			'default'     => __( 'Test Credit Cards: Success - 4929000000006 | Failed - 4444333322221111', WOPNP_TEXTDOMAIN ),
		);
	}

}
