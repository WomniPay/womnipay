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

namespace Womnipay\Backend;

use Womnipay\Engine\Base;

/**
 * Activate and deactive method of the plugin and relates.
 */
class ActDeact extends Base {

	/**
	 * Initialize the class.
	 *
	 * @return void|bool
	 */
	public function initialize() {
		if ( !parent::initialize() ) {
			return;
		}

		// Activate plugin when new blog is added
		\add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
		\add_action( 'admin_init', array( $this, 'upgrade_procedure' ) );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param int $blog_id ID of the new blog.
	 * @since 1.0.0
	 * @return void
	 */
	public function activate_new_site( int $blog_id ) {
		if ( 1 !== \did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		\switch_to_blog( $blog_id );
		self::single_activate();
		\restore_current_blog();
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param bool|null $network_wide True if active in a multiste, false if classic site.
	 * @since 1.0.0
	 * @return void
	 */
	public static function activate( $network_wide ) {
		if ( \function_exists( 'is_multisite' ) && \is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				/** @var array<\WP_Site> $blogs */
				$blogs = \get_sites();

				foreach ( $blogs as $blog ) {
					\switch_to_blog( (int) $blog->blog_id );
					self::single_activate();
					\restore_current_blog();
				}

				return;
			}
		}

		self::single_activate();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param bool $network_wide True if WPMU superadmin uses
	 * "Network Deactivate" action, false if
	 * WPMU is disabled or plugin is
	 * deactivated on an individual blog.
	 * @since 1.0.0
	 * @return void
	 */
	public static function deactivate( bool $network_wide ) {
		if ( \function_exists( 'is_multisite' ) && \is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				/** @var array<\WP_Site> $blogs */
				$blogs = \get_sites();

				foreach ( $blogs as $blog ) {
					\switch_to_blog( (int) $blog->blog_id );
					self::single_deactivate();
					\restore_current_blog();
				}

				return;
			}
		}

		self::single_deactivate();
	}

	/**
	 * Upgrade procedure
	 *
	 * @return void
	 */
	public static function upgrade_procedure() {
		if ( !\is_admin() ) {
			return;
		}

		$version = \strval( \get_option( 'womnipay-version' ) );

		if ( !\version_compare( WOP_VERSION, $version, '>' ) ) {
			return;
		}

		\update_option( 'womnipay-version', WOP_VERSION );
		\delete_option( WOP_TEXTDOMAIN . '_fake-meta' );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function single_activate() {
		self::upgrade_procedure();
		// Clear the permalinks
		\flush_rewrite_rules();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function single_deactivate() {
		// Clear the permalinks
		\flush_rewrite_rules();
	}

}
