<?php
/**
 * Main plugin class.
 *
 * @package RSFA
 */

namespace RSFA;

use RSFA\Compatibility\Plugin_Provider;
use RSFA\Settings\Register;
use RSFA\Compatibility\Theme_Provider;

/**
 * Class RSFA_featured_audio
 */
final class Plugin {
	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Self Updater.
	 *
	 * @var $plugin_updater
	 */
	public $self_updater;

	/**
	 * Register instance.
	 *
	 * @var $registration_provider
	 */
	public $registration_provider;

	/**
	 * Metabox instance.
	 *
	 * @var $metabox_provider
	 */
	public $metabox_provider;

	/**
	 * Shortcode instance.
	 *
	 * @var $shortcode_provider
	 */
	public $shortcode_provider;

	/**
	 * Frontend instance.
	 *
	 * @var $frontend_provider
	 */
	public $frontend_provider;

	/**
	 * Plugin Compat Provide
	 *
	 * @var $plugin_provider
	 */
	public $plugin_provider;

	/**
	 * Theme Compat Provide
	 *
	 * @var $theme_provider
	 */
	public $theme_provider;

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->register();
	}

	/**
	 * Get a class instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			/**
			 * RSFA loaded.
			 *
			 * Fires when RSFA is fully loaded and instantiated.
			 *
			 * @since 0.6.0
			 */
			do_action( 'rsfa_loaded' );
		}

		return self::$instance;
	}

	/**
	 * Defines constants.
	 *
	 * @retun void
	 */
	public function define_constants() {
		define( 'RSFA_SOURCE_META_KEY', 'rsfa_source' );
		define( 'RSFA_META_KEY', 'rsfa_featured_audio' );
		define( 'RSFA_EMBED_META_KEY', 'rsfa_featured_embed_audio' );
	}

	/**
	 * Registers plugin classes & translation.
	 *
	 * @return void
	 */
	public function register() {
		// Load translation.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load classes.
		// Let's call these providers.
		$this->registration_provider = Register::get_instance();
		$this->metabox_provider      = Metabox::get_instance();
		$this->shortcode_provider    = Shortcode::get_instance();
		$this->frontend_provider     = FrontEnd::get_instance();

		// Load compatibility.
		$this->plugin_provider = Plugin_Provider::get_instance();
		$this->theme_provider  = Theme_Provider::get_instance();

		// Updates.
		$this->self_updater = new Updater();

		add_action( 'admin_init', array( $this->self_updater, 'init' ) );

		// Register action links.
		add_filter( 'network_admin_plugin_action_links_really-simple-featured-audio/really-simple-featured-audio.php', array( $this, 'filter_plugin_action_links' ) );
		add_filter( 'plugin_action_links_really-simple-featured-audio/really-simple-featured-audio.php', array( $this, 'filter_plugin_action_links' ) );

		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
	}

	/**
	 *
	 * Load translation domain & files.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'really-simple-featured-audio', false, dirname( RSFA_PLUGIN_BASE ) . '/languages/' );
	}

	/**
	 * Include plugin files.
	 *
	 * @return void
	 */
	public function includes() {
		require_once RSFA_PLUGIN_DIR . 'includes/class-options.php';
		require_once RSFA_PLUGIN_DIR . 'includes/Settings/class-register.php';
		require_once RSFA_PLUGIN_DIR . 'includes/class-metabox.php';

		// Frontend loaders.
		require_once RSFA_PLUGIN_DIR . 'includes/class-shortcode.php';
		require_once RSFA_PLUGIN_DIR . 'includes/class-frontend.php';

		// Plugin compatibility.
		require_once RSFA_PLUGIN_DIR . 'includes/Compatibility/Plugins/class-base-compatibility.php';
		require_once RSFA_PLUGIN_DIR . 'includes/Compatibility/class-plugin-provider.php';

		// Theme compatibility.
		require_once RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/class-base-compatibility.php';
		require_once RSFA_PLUGIN_DIR . 'includes/Compatibility/class-theme-provider.php';

		// Database upgraders.
		require_once RSFA_PLUGIN_DIR . 'includes/class-updater.php';
	}

	/**
	 * Add settings link at plugins page action links.
	 *
	 * @param array $actions Action links.
	 *
	 * @return array
	 */
	public function filter_plugin_action_links( array $actions ) {
		$settings_url = admin_url( 'options-general.php?page=rsfa-settings' );

		return array_merge(
			array(
				'settings' => "<a href='{$settings_url}'>" . esc_html__( 'Settings', 'really-simple-featured-audio' ) . '</a>',
			),
			$actions
		);
	}

	/**
	 * Checks if pro addon is active.
	 *
	 * @return bool
	 */
	public function has_pro_active() {
		return defined( 'RSFA_PRO_VERSION' );
	}

	/**
	 * Modifies admin footer text.
	 *
	 * @param string $html Existing html markup.
	 * @return mixed
	 */
	public function admin_footer_text( $html ) {
		// Exit early if the function doesn't load for some reason.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $html;
		}

		$screen     = get_current_screen();
		$page_slugs = array(
			'settings_page_rsfa-settings',
		);

		if ( in_array( $screen->id, $page_slugs, true ) ) {
			// Modified html goes here.
			return sprintf(
			/* translators: %1$s is a link to RSFA's plugin page, %2$s is a link to JetixWP's website, and %3$s is the existing html,. */
				__( '%1$s is developed and maintained by %2$s. %3$s', 'wp-migrate-db' ),
				'<a href="https://jetixwp.com/plugins/really-simple-featured-audio">Really Simple Featured Audio</a>',
				'<a href="https://jetixwp.com/" target="_blank">JetixWP</a>',
				$html
			);
		} else {
			return $html;
		}
	}
}
