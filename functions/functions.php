<?php
/**
 * Womnipay
 *
 * @package   Womnipay
 * @author    WomniPay <info@womnipay.com>
 * @copyright WomniPay
 * @license   GPL v2 or later
 * @link      https://womnipay.com
 */

/**
 * Get the settings of the plugin in a filterable way
 *
 * @since 1.0.0.
 * @return array
 */
function wop_get_settings() {
	return apply_filters( 'wop_get_settings', get_option( WOP_TEXTDOMAIN . '-settings' ) );
}
