<?php
/**
 * Global Settings
 *
 * @package RSFA
 */

namespace RSFA\Settings;

use RSFA\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Global_Settings.
 */
class Global_Settings extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'global';
		$this->label = __( 'Global', 'really-simple-featured-audio' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		$settings = array(
			array(
				'title' => esc_html_x( 'Blogs & Archives', 'settings title', 'really-simple-featured-audio' ),
				'desc'  => '',
				'class' => 'rsfa-blog-archives-title',
				'type'  => 'content',
				'id'    => 'rsfa-blog-archives-title',
			),
			array(
				'type' => 'title',
				'id'   => 'rsfa_archives_visibilitiy',
			),
			array(
				'title'   => __( 'Show audios at Blog archives', 'really-simple-featured-audio' ),
				'desc'    => __( 'When toggled on, it shows set audios at blog home and archives such as category, tag archives etc.', 'really-simple-featured-audio' ),
				'id'      => 'blog_archives_visibility',
				'default' => true,
				'type'    => 'checkbox',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rsfa_archives_visibilitiy',
			),
		);

		$settings = apply_filters(
			'rsfa_global_settings',
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

return new Global_Settings();
