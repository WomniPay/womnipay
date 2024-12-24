<?php
/**
 * WomniPay
 *
 * @package           WomniPay
 * @author            WomniPay
 * @copyright         2025 WomniPay
 * @license           GPL 3
 * @wordpress-plugin
 * Plugin Name:       WomniPay
 * Plugin URI:        https://womnipay.com/
 * Description:       OmniPay for WooCommerce.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            WomniPay
 * Author URI:        https://womnipay.com
 * Text Domain:       womnipay
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://update.womnipay.com/
 * Requires Plugins:  woocommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'WOP_VERSION', '1.0.0' );
define( 'WOP_TEXTDOMAIN', 'womnipay' );
define( 'WOP_NAME', 'womnipay' );
define( 'WOP_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'WOP_PLUGIN_ABSOLUTE', __FILE__ );
define( 'WOP_MIN_PHP_VERSION', '8.0' );
define( 'WOP_WP_VERSION', '6.0' );

add_action(
	'init',
	static function () {
		load_plugin_textdomain( WOP_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

$womnipay_libraries = require WOP_PLUGIN_ROOT . 'vendor/autoload.php';

require_once WOP_PLUGIN_ROOT . 'functions/functions.php';

if ( ! wp_installing() ) {
	register_activation_hook( WOP_TEXTDOMAIN . '/' . WOP_TEXTDOMAIN . '.php', array( new \Womnipay\Backend\ActDeact, 'activate' ) );
	register_deactivation_hook( WOP_TEXTDOMAIN . '/' . WOP_TEXTDOMAIN . '.php', array( new \Womnipay\Backend\ActDeact, 'deactivate' ) );
	add_action(
		'plugins_loaded',
		static function () use ( $womnipay_libraries ) {
			new \Womnipay\Engine\Initialize( $womnipay_libraries );
		}
	);
}
