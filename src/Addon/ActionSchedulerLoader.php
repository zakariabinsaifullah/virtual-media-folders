<?php
/**
 * Action Scheduler loader helper for VMF add-ons.
 *
 * @package VirtualMediaFolders
 * @since   2.0.0
 */

declare(strict_types=1);

namespace VirtualMediaFolders\Addon;

defined( 'ABSPATH' ) || exit;

/**
 * Ensures Action Scheduler is loaded exactly once.
 *
 * Action Scheduler ships inside each add-on's vendor/ directory and uses
 * an internal version-negotiation registry (`ActionScheduler_Versions`),
 * so multiple copies coexist safely; only the highest version boots.
 */
final class ActionSchedulerLoader {

	/**
	 * Load Action Scheduler from the given plugin directory if it isn't
	 * already available.
	 *
	 * Safe to call multiple times — returns early once the AS scheduling
	 * functions exist in the global scope.
	 *
	 * @param string $plugin_dir Absolute path to the add-on root (with trailing slash).
	 * @return bool True when Action Scheduler scheduling functions are available.
	 */
	public static function maybe_load( string $plugin_dir ): bool {
		if ( function_exists( 'as_schedule_single_action' ) ) {
			return true;
		}

		// Guard: WordPress must be loaded.
		if ( ! function_exists( 'add_action' ) ) {
			return false;
		}

		$paths = [
			$plugin_dir . 'vendor/woocommerce/action-scheduler/action-scheduler.php',
			$plugin_dir . 'woocommerce/action-scheduler/action-scheduler.php',
		];

		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				require_once $path;
				break;
			}
		}

		return function_exists( 'as_schedule_single_action' );
	}
}
