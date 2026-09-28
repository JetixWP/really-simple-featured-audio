<?php
/**
 * Admin Tools Class
 *
 * @package  RSFA
 */

namespace RSFA\Tools;

use function RSFA\Settings\get_post_types;

defined( 'ABSPATH' ) || exit;

/**
 * Admin_Tools Class.
 */
class Admin_Tools {
	/**
	 * Handles the output of the Tools page.
	 */
	public static function output() {
		// Hide admin notices on Tools page.
		self::hide_admin_notices();

		// Enqueue necessary assets.
		self::enqueue_assets();

		include RSFA_PLUGIN_DIR . 'includes/Tools/Views/html-admin-tools.php';
	}

	/**
	 * Hide admin notices on the Tools page.
	 */
	public static function hide_admin_notices() {
		// Remove standard notice hooks.
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
		remove_all_actions( 'user_admin_notices' );
		remove_all_actions( 'network_admin_notices' );
	}

	/**
	 * Get inline CSS to hide any notices that still appear.
	 *
	 * @return string
	 */
	public static function get_hide_notices_css() {
		return '
			.rsfa-tools-page .notice,
			.rsfa-tools-page .updated,
			.rsfa-tools-page .update-nag,
			.rsfa-tools-page .error,
			.rsfa-tools-page #tgmpa-notice,
			.rsfa-tools-page .tgmpa-notice {
				display: none !important;
			}
		';
	}

	/**
	 * Enqueue assets for the Bulk Actions page.
	 */
	public static function enqueue_assets() {
		$asset_file = RSFA_PLUGIN_DIR . 'assets/js/tools/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		// Enqueue media scripts for audio upload.
		wp_enqueue_media();

		wp_enqueue_script(
			'rsfa-tools',
			RSFA_PLUGIN_URL . 'assets/js/tools/index.js',
			$asset['dependencies'],
			filemtime( RSFA_PLUGIN_DIR . 'assets/js/tools/index.js' ),
			true
		);

		wp_enqueue_style(
			'rsfa-tools',
			RSFA_PLUGIN_URL . 'assets/js/tools/style-index.css',
			array( 'wp-components' ),
			filemtime( RSFA_PLUGIN_DIR . 'assets/js/tools/style-index.css' )
		);

		// Add inline CSS to hide notices.
		wp_add_inline_style( 'rsfa-tools', self::get_hide_notices_css() );

		wp_localize_script(
			'rsfa-tools',
			'rsfaTools',
			self::get_localized_data()
		);
	}

	/**
	 * Get localized data for the Tools app.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		$data = array(
			'postTypes'   => self::get_enabled_post_types_options(),
			'perPage'     => 20,
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'columns'     => self::get_table_columns(),
			'isPro'       => defined( 'RSFA_PRO_VERSION' ),
			'upgradeUrl'  => RSFA_PLUGIN_PRO_URL . '/#pricing',
			'settingsUrl' => admin_url( 'admin.php?page=rsfa-settings' ),
		);

		/**
		 * Filter the localized data for the Tools app.
		 *
		 * @since 1.6.0
		 *
		 * @param array $data Localized data.
		 */
		return apply_filters( 'rsfa_tools_localized_data', $data );
	}

	/**
	 * Get table columns configuration.
	 *
	 * @return array
	 */
	public static function get_table_columns() {
		$columns = array(
			'thumbnail'     => array(
				'label'    => __( 'Thumbnail', 'really-simple-featured-audio' ),
				'class'    => 'column-thumbnail',
				'sortable' => false,
			),
			'title'         => array(
				'label'    => __( 'Title', 'really-simple-featured-audio' ),
				'class'    => 'column-title',
				'sortable' => false,
			),
			'status_type'   => array(
				'label'    => __( 'Audio Status & Type', 'really-simple-featured-audio' ),
				'class'    => 'column-status-type',
				'sortable' => false,
			),
			'audio_action'  => array(
				'label'    => __( 'Action', 'really-simple-featured-audio' ),
				'class'    => 'column-audio-action',
				'sortable' => false,
			),
			'audio_preview' => array(
				'label'    => __( 'Audio', 'really-simple-featured-audio' ),
				'class'    => 'column-audio-preview',
				'sortable' => false,
			),
		);

		/**
		 * Filter the table columns for the Tools page.
		 *
		 * @since 1.6.0
		 *
		 * @param array $columns Table columns configuration.
		 */
		return apply_filters( 'rsfa_tools_table_columns', $columns );
	}

	/**
	 * Get enabled post types as options array.
	 *
	 * @return array
	 */
	public static function get_enabled_post_types_options() {
		$enabled_types = get_post_types();
		$options       = array();

		foreach ( $enabled_types as $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );

			if ( $post_type_obj ) {
				$labels    = $post_type_obj->labels;
				$options[] = array(
					'value'    => sanitize_key( $post_type ),
					'label'    => esc_html( $labels->name ),
					'singular' => wp_strip_all_tags( $labels->singular_name ),
					'search'   => wp_strip_all_tags( $labels->search_items ),
					'notFound' => wp_strip_all_tags( $labels->not_found ),
				);
			}
		}

		return $options;
	}
}
