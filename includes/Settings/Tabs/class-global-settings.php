<?php
/**
 * Global Settings
 *
 * @package RSFA
 */

namespace RSFA\Settings;

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
				'title'   => __( 'Show audio at Blog, Category and Tag archives', 'really-simple-featured-audio' ),
				'desc'    => __( 'When toggled on, it shows set audio at blog home and archives such as category, tag archives etc.', 'really-simple-featured-audio' ),
				'id'      => 'blog_archives_visibility',
				'default' => true,
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Show audio at Blog Single Post', 'really-simple-featured-audio' ),
				'desc'    => __( 'When toggled on, it shows set audio at Blog Single Post.', 'really-simple-featured-audio' ),
				'id'      => 'blog_single_visibility',
				'default' => true,
				'type'    => 'checkbox',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rsfa_archives_visibilitiy',
			),
		);

		if ( ! \RSFA\Plugin::get_instance()->has_pro_active() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'title' => esc_html_x( 'Player Appearance', 'settings title', 'really-simple-featured-audio' ),
						'desc'  => __( 'Match the audio player to your site with an accent color and a light, dark or automatic theme. Available in Pro.', 'really-simple-featured-audio' ),
						'class' => 'promo-player-appearance',
						'type'  => 'promo-content',
						'id'    => 'promo-rsfa-pro-player-appearance',
					),
					array(
						'type' => 'title',
						'id'   => 'promo_player_appearance_title',
					),
					array(
						'title'     => __( 'Player Theme', 'really-simple-featured-audio' ),
						'id'        => 'promo-player-theme',
						'type'      => 'promo-select',
						'options'   => array(
							'auto'  => __( 'Automatic (follows device)', 'really-simple-featured-audio' ),
							'light' => __( 'Light', 'really-simple-featured-audio' ),
							'dark'  => __( 'Dark', 'really-simple-featured-audio' ),
						),
						'disabled'  => true,
						'is_option' => false,
					),
					array(
						'title'     => __( 'Accent Color', 'really-simple-featured-audio' ),
						'id'        => 'promo-player-theme-color',
						'type'      => 'promo-text',
						'default'   => '',
						'is_option' => false,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'promo_player_appearance_title',
					),
				)
			);
		}

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
