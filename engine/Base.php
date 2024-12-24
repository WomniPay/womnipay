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

namespace Womnipay\Engine;

/**
 * Base skeleton of the plugin
 */
class Base {

	/**
	 * The settings of the plugin.
	 *
	 * @var array The settings of the plugin.
	 */
	public $settings = array();

	/**
	 * Initialize the plugin settings.
	 *
	 * This method initializes the class and gets the plugin settings.
	 *
	 * This method sets the plugin settings by calling the \wop_get_settings() function.
	 *
	 * @return bool
	 */
	public function initialize() {
		$this->settings = \wop_get_settings();

		return true;
	}

}
