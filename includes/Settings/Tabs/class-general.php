<?php
/**
 * General Settings
 *
 * @package RSFA
 */

namespace RSFA\Settings;

use RSFA\Options;
use RSFA\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * General.
 */
class General extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'really-simple-featured-audio' );

		parent::__construct();
	}

	/**
	 * Gets available post types.
	 *
	 * @return mixed|array
	 */
	public function get_available_post_types() {
		$post_types                        = array();
		$all_post_types                    = \get_post_types();
		$post_types_with_thumbnail_support = \get_post_types_by_support( 'thumbnail' );

		// Just in case.
		if ( ! is_array( $post_types_with_thumbnail_support ) ) {
			$post_types_with_thumbnail_support = array();
		}

		foreach ( $all_post_types as $post_type ) {
			if ( ! isset( $post_types[ $post_type ] ) && in_array( $post_type, $post_types_with_thumbnail_support, true ) ) {
				$post_types[ $post_type ] = get_post_type_object( $post_type )->labels->name;
			}
		}

		return apply_filters(
			'rsfa_post_types_support',
			$post_types
		);
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		$post_types = $this->get_available_post_types();

		$compatibility_engines = Plugin::get_instance()->theme_provider->get_selectable_engine_options();

		$current_engine = Options::get_instance()->get( 'active-theme-engine' );

		$default_enabled_post_types = apply_filters(
			'rsfa_default_enabled_post_types',
			array(
				'post' => true,
			)
		);

		$settings = array(
			array(
				'title' => esc_html_x( 'Theme Compatibility Engine', 'settings title', 'really-simple-featured-audio' ),
				'desc'  => __( 'If featured audios aren\'t working as expected in your theme, you may need to set this from the list of supported theme engines. (Default engine follows standard WordPress rules, and may not work for all themes)', 'really-simple-featured-audio' ),
				'class' => 'rsfa-theme-compatibility-engine',
				'type'  => 'content',
				'id'    => 'rsfa-theme-compatibility',
			),
			array(
				'type' => 'title',
				'id'   => 'rsfa_theme_support_title',
			),
			array(
				'title'   => __( 'Status', 'really-simple-featured-audio' ),
				'desc'    => '',
				'id'      => 'theme-engine-status',
				'default' => __( 'Auto', 'really-simple-featured-audio' ),
				'class'   => 'disabled' !== $current_engine ? 'engine-active' : 'engine-inactive',
				'type'    => 'status',
				'current' => $compatibility_engines[ $current_engine ] ?? $current_engine,
			),
			array(
				'title'   => __( 'Set engine', 'really-simple-featured-audio' ),
				'desc'    => '',
				'id'      => 'theme-compatibility-engine',
				'default' => 'auto',
				'type'    => 'select',
				'options' => $compatibility_engines,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rsfa_theme_support_title',
			),
			array(
				'title' => esc_html_x( 'Enable Post Types Support', 'settings title', 'really-simple-featured-audio' ),
				'desc'  => __( 'Please select the post types you wish to enable featured audio support at.', 'really-simple-featured-audio' ),
				'class' => 'rsfa-enable-post-types',
				'type'  => 'content',
				'id'    => 'rsfa-enable-post-types',
			),
			array(
				'type' => 'title',
				'id'   => 'rsfa_post_types_title',
			),
			array(
				'title'   => '',
				'id'      => 'post_types',
				'default' => $default_enabled_post_types,
				'type'    => 'multi-checkbox',
				'options' => $post_types,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rsfa_post_types_title',
			),
		);

		$settings = apply_filters(
			'rsfa_general_settings',
			$settings
		);

		return apply_filters( 'rsfa_get_settings_' . $this->id, $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

		Admin_Settings::save_fields( $settings );
		if ( $current_section ) {
			do_action( 'rsfa_update_options_' . $this->id . '_' . $current_section );
		}
	}
}

return new General();
