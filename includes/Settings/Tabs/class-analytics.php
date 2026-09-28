<?php
/**
 * Analytics settings.
 *
 * @package RSFA
 * @since   0.90.0
 */

namespace RSFA\Settings;

use RSFA\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics tab on the Featured Audio screen.
 */
class Analytics extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'analytics';
		$this->label = __( 'Analytics', 'really-simple-featured-audio' );

		parent::__construct();
	}

	/**
	 * Settings fields.
	 *
	 * @param string $current_section Current section ID.
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		unset( $current_section );

		$report_url = admin_url( 'admin.php?page=rsfa-tools#analytics' );

		$settings = array(
			array(
				'title' => esc_html_x( 'Audio analytics', 'settings title', 'really-simple-featured-audio' ),
				'desc'  => sprintf(
					/* translators: %s: URL of the analytics report. */
					__( 'Counts are anonymous and stay on this site. No cookie is set. The report is in <a href="%s">Audio Tools</a>.', 'really-simple-featured-audio' ),
					esc_url( $report_url )
				),
				'type'  => 'content',
				'id'    => 'rsfa-analytics-intro',
			),
			array(
				'type' => 'title',
				'id'   => 'rsfa_analytics_options',
			),
			array(
				'title'   => __( 'Enable analytics', 'really-simple-featured-audio' ),
				'desc'    => __( 'Record views and plays for audios added with this plugin.', 'really-simple-featured-audio' ),
				'id'      => 'analytics_enabled',
				'default' => true,
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Skip logged-in editors', 'really-simple-featured-audio' ),
				'desc'    => __( 'Visits from people who can edit posts are left out, so your own previews do not fill the report.', 'really-simple-featured-audio' ),
				'id'      => 'analytics_ignore_editors',
				'default' => true,
				'type'    => 'checkbox',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rsfa_analytics_options',
			),
		);

		if ( ! Plugin::get_instance()->has_pro_active() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'type' => 'title',
						'id'   => 'rsfa_promo_analytics_title',
					),
					array(
						'title'     => __( 'Keep counts for', 'really-simple-featured-audio' ),
						'desc'      => __( 'Free keeps the last 14 days. A longer history recording is a Pro setting.', 'really-simple-featured-audio' ),
						'id'        => 'promo-analytics-retention',
						'default'   => '14',
						'type'      => 'promo-select',
						'disabled'  => true,
						'is_option' => false,
						'options'   => array(
							'14'  => __( '14 days', 'really-simple-featured-audio' ),
							'30'  => __( '30 days', 'really-simple-featured-audio' ),
							'90'  => __( '90 days', 'really-simple-featured-audio' ),
							'365' => __( '365 days', 'really-simple-featured-audio' ),
							'all' => __( 'All time', 'really-simple-featured-audio' ),
						),
					),
					array(
						'title'     => __( 'Country, device, and referrer', 'really-simple-featured-audio' ),
						'desc'      => __( 'Pro can record a country when your host sends one, a general device type, and the referring site name.', 'really-simple-featured-audio' ),
						'id'        => 'promo-analytics-audience',
						'type'      => 'promo-checkbox',
						'disabled'  => true,
						'is_option' => false,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'rsfa_promo_analytics_title',
					),
				)
			);
		}

		return apply_filters( 'rsfa_get_settings_' . $this->id, $settings );
	}
}

return new Analytics();
