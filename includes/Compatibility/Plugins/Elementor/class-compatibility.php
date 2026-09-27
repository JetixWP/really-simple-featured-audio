<?php
/**
 * Elementor's compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Plugins\Elementor;

defined( 'ABSPATH' ) || exit;

use RSFA\Compatibility\Plugins\Base_Compatibility;
use RSFA\Compatibility\Plugins\Elementor\Widgets\RSFA_Audio_Widget;
use RSFA\FrontEnd;
use RSFA\Options;
use function RSFA\Settings\get_post_types;

/**
 * Class Compatibility
 *
 * @package RSFA
 */
class Compatibility extends Base_Compatibility {
	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->id = 'elementor';

		$this->setup();
	}

	/**
	 * Register Settings.
	 *
	 * @param array $settings Active settings file array.
	 *
	 * @return array
	 */
	public function register_settings( $settings ) {
		// Settings.
		$settings[] = include 'class-settings.php';

		return $settings;
	}

	/**
	 * Sets up hooks and filters.
	 *
	 * @return void
	 */
	public function setup() {
		add_filter( 'rsfa_get_settings_pages', array( $this, 'register_settings' ) );

		$options = Options::get_instance();

		$disable_elementor_support = $options->get( 'disable_elementor_support' );

		if ( ! $options->has( 'disable_elementor_support' ) || ! $disable_elementor_support ) {
			add_filter( 'elementor/image_size/get_attachment_image_html', array( $this, 'update_with_audio_html' ), 10, 4 );
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_filter( 'get_post_metadata', array( $this, 'prefill_widget_meta' ), 10, 4 );
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
		add_filter( 'rsfa_load_player_assets_early', array( $this, 'load_player_assets_in_preview' ) );
	}

	/**
	 * Load the player assets in the Elementor preview, where widgets are
	 * re-rendered over AJAX and would otherwise have no player script.
	 *
	 * @since 1.6.0
	 *
	 * @param bool $load_early Whether to enqueue now.
	 *
	 * @return bool
	 */
	public function load_player_assets_in_preview( $load_early ) {
		if ( isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			return true;
		}

		return $load_early;
	}

	/**
	 * Register Elementor widgets.
	 *
	 * @since 1.6.0
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 *
	 * @return void
	 */
	public function register_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/class-rsfa-audio-widget.php';

		$widgets_manager->register( new RSFA_Audio_Widget() );
	}

	/**
	 * Enqueue editor-only JS that populates the RSFA Audio widget
	 * controls from existing post meta when the widget is freshly added
	 * (before it has been saved into `_elementor_data`).
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function enqueue_editor_scripts() {
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		// Only for RSFA-enabled post types.
		$post_type     = get_post_type( $post_id );
		$enabled_types = get_post_types();

		if ( ! $post_type || ! in_array( $post_type, $enabled_types, true ) ) {
			return;
		}

		$rsfa_meta = $this->get_rsfa_meta( $post_id );

		// Nothing to localize if no featured audio exists.
		if ( empty( $rsfa_meta ) ) {
			return;
		}

		wp_enqueue_script(
			'rsfa-elementor-editor',
			RSFA_PLUGIN_URL . 'assets/js/rsfa-elementor-editor.js',
			array( 'elementor-editor' ),
			RSFA_VERSION,
			true
		);

		wp_localize_script( 'rsfa-elementor-editor', 'rsfaElementorMeta', $rsfa_meta );
	}

	/**
	 * Filter `_elementor_data` on read so that every `rsfa_audio` widget
	 * whose controls are still at their defaults is pre-filled from the
	 * post's existing RSFA meta. Runs server-side before the editor JS
	 * even loads, so the controls appear populated instantly.
	 *
	 * @since 1.6.0
	 *
	 * @param mixed  $value    Current meta value (null on first call).
	 * @param int    $post_id  Post ID.
	 * @param string $meta_key Meta key being read.
	 * @param bool   $single   Whether a single value was requested.
	 *
	 * @return mixed
	 */
	public function prefill_widget_meta( $value, $post_id, $meta_key, $single ) {
		if ( '_elementor_data' !== $meta_key ) {
			return $value;
		}

		// Only run for post types that are enabled in RSFA settings.
		$post_type     = get_post_type( $post_id );
		$enabled_types = get_post_types();

		if ( ! $post_type || ! in_array( $post_type, $enabled_types, true ) ) {
			return $value;
		}

		// Prevent infinite recursion: unhook → read → re-hook.
		remove_filter( 'get_post_metadata', array( $this, 'prefill_widget_meta' ), 10 );
		$raw = get_post_meta( $post_id, '_elementor_data', true );
		add_filter( 'get_post_metadata', array( $this, 'prefill_widget_meta' ), 10, 4 );

		if ( empty( $raw ) ) {
			return $value;
		}

		$elements = is_string( $raw ) ? json_decode( $raw, true ) : $raw;

		if ( ! is_array( $elements ) ) {
			return $value;
		}

		// Gather RSFA meta once.
		$rsfa_meta = $this->get_rsfa_meta( $post_id );

		// Nothing to pre-fill if no featured audio is configured.
		if ( empty( $rsfa_meta ) ) {
			return $value;
		}

		$changed  = false;
		$elements = $this->walk_elements( $elements, $rsfa_meta, $changed );

		if ( ! $changed ) {
			return $value;
		}

		// Return in the format WordPress expects from this filter.
		// When $single is true WP takes index [0] of the returned array.
		return array( wp_json_encode( $elements ) );
	}

	/**
	 * Collect the post's RSFA meta into a handy array.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array
	 */
	private function get_rsfa_meta( $post_id ) {
		// Verify the post type is enabled in RSFA settings.
		$post_type     = get_post_type( $post_id );
		$enabled_types = get_post_types();

		if ( ! $post_type || ! in_array( $post_type, $enabled_types, true ) ) {
			return array();
		}

		$source = get_post_meta( $post_id, RSFA_SOURCE_META_KEY, true );
		$source = $source ? $source : 'self';

		$audio_id  = get_post_meta( $post_id, RSFA_META_KEY, true );
		$cover_id  = get_post_meta( $post_id, RSFA_COVER_META_KEY, true );
		$embed_url = get_post_meta( $post_id, RSFA_EMBED_META_KEY, true );

		// If neither a self-hosted audio nor an embed URL is set there is
		// nothing to pre-fill.
		if ( 'self' === $source && empty( $audio_id ) ) {
			if ( empty( $embed_url ) ) {
				return array();
			}
		}
		if ( 'embed' === $source && empty( $embed_url ) ) {
			if ( empty( $audio_id ) ) {
				return array();
			}
		}

		return array(
			'source'     => $source,
			'audio_id'   => $audio_id ? absint( $audio_id ) : '',
			'audio_url'  => $audio_id ? wp_get_attachment_url( $audio_id ) : '',
			'cover_id'   => $cover_id ? absint( $cover_id ) : '',
			'cover_url'  => $cover_id ? wp_get_attachment_url( $cover_id ) : '',
			'embed_url'  => $embed_url ? $embed_url : '',
		);
	}

	/**
	 * Recursively walk the Elementor element tree and inject RSFA meta
	 * into any `rsfa_audio` widget that has empty/default controls.
	 *
	 * @param array $elements Elementor element tree.
	 * @param array $meta     RSFA meta values.
	 * @param bool  $changed  Reference flag flipped when a widget is updated.
	 *
	 * @return array Modified elements.
	 */
	private function walk_elements( array $elements, array $meta, bool &$changed ) {
		foreach ( $elements as &$element ) {
			if (
				'widget' === ( $element['elType'] ?? '' ) &&
				'rsfa_audio' === ( $element['widgetType'] ?? '' )
			) {
				$element = $this->maybe_inject_meta( $element, $meta, $changed );
			}

			if ( ! empty( $element['elements'] ) ) {
				$element['elements'] = $this->walk_elements( $element['elements'], $meta, $changed );
			}
		}
		unset( $element );

		return $elements;
	}

	/**
	 * Inject RSFA post meta into a single widget's settings when they are
	 * still at their defaults (empty).
	 *
	 * @param array $element Widget element data.
	 * @param array $meta    RSFA meta values.
	 * @param bool  $changed Reference flag.
	 *
	 * @return array
	 */
	private function maybe_inject_meta( array $element, array $meta, bool &$changed ) {
		$s = $element['settings'] ?? array();

		// Only inject when source is "current_post" (or not set = default).
		$audio_source = $s['audio_source'] ?? 'current_post';
		if ( 'current_post' !== $audio_source ) {
			return $element;
		}

		// Already has explicit values – don't overwrite.
		$has_self_audio = ! empty( $s['self_audio']['url'] ) || ! empty( $s['self_audio']['id'] );
		$has_embed_url  = ! empty( $s['embed_url']['url'] );

		if ( $has_self_audio || $has_embed_url ) {
			return $element;
		}

		$source = $meta['source'] ?? 'self';

		$s['audio_type'] = $source;

		if ( 'self' === $source && ! empty( $meta['audio_id'] ) ) {
			$s['self_audio'] = array(
				'id'  => $meta['audio_id'],
				'url' => $meta['audio_url'],
			);

			$changed = true;
		} elseif ( 'embed' === $source && ! empty( $meta['embed_url'] ) ) {
			$s['embed_url'] = array(
				'url' => $meta['embed_url'],
			);

			$changed = true;
		}

		// The cover shows for either audio type.
		if ( $changed && ! empty( $meta['cover_id'] ) && empty( $s['cover_image']['id'] ) ) {
			$s['cover_image'] = array(
				'id'  => $meta['cover_id'],
				'url' => $meta['cover_url'],
			);
		}

		$element['settings'] = $s;

		return $element;
	}

	/**
	 * Override Elementor Pro's post widget featured image html.
	 *
	 * @since 0.8.6
	 *
	 * @param string $html ex html markup.
	 * @param array  $settings Settings array of parent widget/element.
	 * @param string $image_size_key Image size key.
	 * @param string $image_key Image key.
	 *
	 * @return string
	 */
	public function update_with_audio_html( $html, $settings, $image_size_key, $image_key ) {
		// Exit early if Elementor Pro isn't active.
		if ( ! class_exists( 'ElementorPro\Plugin' ) ) {
			return $html;
		}

		// Exit if the image contains site-logo.
		if ( isset( $settings['__dynamic__'] ) ) {
			$image = $settings['__dynamic__']['image'] ?? '';
			if ( str_contains( $image, 'site-logo' ) ) {
				return $html;
			}
		}

		// If the image markup is from posts/archive/featured image widgets.
		if ( is_array( $settings ) && ( isset( $settings['posts_post_type'] ) || isset( $settings['archive_classic_thumbnail'] ) || isset( $settings['__dynamic__'] ) ) ) {
			global $post;

			// Check if the $post object is not defined.
			if ( 'object' !== gettype( $post ) ) {
				return $html;
			}

			$post_id = $post->ID;

			return FrontEnd::get_featured_audio_markup( $post_id, $html );
		}

		return $html;
	}
}
