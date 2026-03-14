<?php
/**
 * Abstract Plugin bootstrap for VMF add-ons.
 *
 * Provides the singleton lifecycle, textdomain loading, and parent-tab
 * detection shared by every add-on plugin.
 *
 * @package VirtualMediaFolders
 * @since   2.0.0
 */

declare(strict_types=1);

namespace VirtualMediaFolders\Addon;

defined( 'ABSPATH' ) || exit;

/**
 * Base class for VMF add-on plugins.
 *
 * Subclasses must implement the abstract getters and override
 * the three template methods (init_services, init_hooks, init_cli)
 * as needed.
 */
abstract class AbstractPlugin {

	/**
	 * Singleton instance — stored per concrete subclass.
	 *
	 * @var array<class-string, static>
	 */
	private static array $instances = [];

	/**
	 * Prevent direct instantiation.
	 */
	private function __construct() {}

	/**
	 * Get the singleton instance of the concrete subclass.
	 *
	 * @return static
	 */
	final public static function get_instance(): static {
		$class = static::class;

		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new static();
		}

		return self::$instances[ $class ];
	}

	/**
	 * Boot the plugin.
	 *
	 * Call once from the main plugin file after autoload is set up.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->init_services();
		$this->init_hooks();
		$this->init_cli();

		add_action( 'init', [ $this, 'load_textdomain' ] );
	}

	// ------------------------------------------------------------------
	//  Template methods — override in subclass as needed.
	// ------------------------------------------------------------------

	/**
	 * Create service objects.
	 *
	 * @return void
	 */
	protected function init_services(): void {}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	protected function init_hooks(): void {}

	/**
	 * Register WP-CLI commands.
	 *
	 * @return void
	 */
	protected function init_cli(): void {}

	// ------------------------------------------------------------------
	//  Abstract getters — every add-on must provide these.
	// ------------------------------------------------------------------

	/**
	 * The plugin text domain (e.g. 'vmfa-rules-engine').
	 *
	 * @return string
	 */
	abstract protected function get_text_domain(): string;

	/**
	 * Absolute path to the main plugin file (used for basename resolution).
	 *
	 * @return string
	 */
	abstract protected function get_plugin_file(): string;

	// ------------------------------------------------------------------
	//  Concrete helpers available to every add-on.
	// ------------------------------------------------------------------

	/**
	 * Load the plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			$this->get_text_domain(),
			false,
			dirname( plugin_basename( $this->get_plugin_file() ) ) . '/languages'
		);
	}

	/**
	 * Whether the parent VMF plugin supports add-on settings tabs.
	 *
	 * @return bool
	 */
	protected function supports_parent_tabs(): bool {
		return defined( 'VirtualMediaFolders\Settings::SUPPORTS_ADDON_TABS' )
			&& \VirtualMediaFolders\Settings::SUPPORTS_ADDON_TABS;
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization.
	 *
	 * @throws \Exception Always.
	 */
	public function __wakeup(): void {
		throw new \Exception( 'Cannot unserialize singleton.' );
	}
}
