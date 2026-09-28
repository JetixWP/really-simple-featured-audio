<?php
/**
 * Elementor widget for Really Simple Featured Audio.
 *
 * @package RSFA
 * @subpackage Elementor\Widgets
 */

namespace RSFA\Compatibility\Plugins\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use function RSFA\Settings\get_post_types;

/**
 * RSFA Audio Widget.
 *
 * Displays a featured audio using the same rendering pipeline as the RSFA
 * metabox / shortcodes. Users can choose between showing the current post's
 * featured audio or specifying a post by ID, and when using the current post
 * they can pick Self-Hosted (media library) or Embed as the audio source.
 *
 * The widget reads existing RSFA post meta to auto-populate its controls
 * and writes changes back to post meta on save so the featured audio
 * stays in sync with the CPT edit-screen metabox.
 *
 * @since 1.6.0
 */
class RSFA_Audio_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'rsfa_audio';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Really Simple Featured Audio', 'really-simple-featured-audio' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-headphones';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Get widget keywords.
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array( 'audio', 'featured', 'really-simple-featured-audio', 'media', 'really simple featured audio', 'featured audio' );
	}

	/**
	 * Register widget controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->register_content_controls();
	}

	/**
	 * Register content tab controls.
	 *
	 * @return void
	 */
	private function register_content_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'RSFA Audio', 'really-simple-featured-audio' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// ── Source selector.
		$this->add_control(
			'audio_source',
			array(
				'label'   => esc_html__( 'Audio Source', 'really-simple-featured-audio' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'current_post',
				'options' => array(
					'current_post' => esc_html__( 'Current Post', 'really-simple-featured-audio' ),
					'by_post_id'   => esc_html__( 'By Post ID', 'really-simple-featured-audio' ),
				),
			)
		);

		// ── By Post ID controls.
		$this->add_control(
			'post_id',
			array(
				'label'       => esc_html__( 'Post ID', 'really-simple-featured-audio' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Enter the post ID', 'really-simple-featured-audio' ),
				'description' => esc_html__( 'Enter the Post ID whose featured audio you want to display.', 'really-simple-featured-audio' ),
				'label_block' => true,
				'condition'   => array(
					'audio_source' => 'by_post_id',
				),
			)
		);

		// ── Current Post – audio type selector.
		$this->add_control(
			'audio_type',
			array(
				'label'     => esc_html__( 'Audio Type', 'really-simple-featured-audio' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'self',
				'options'   => array(
					'self'  => esc_html__( 'Self-Hosted', 'really-simple-featured-audio' ),
					'embed' => esc_html__( 'Embed', 'really-simple-featured-audio' ),
				),
				'condition' => array(
					'audio_source' => 'current_post',
				),
			)
		);

		// ── Self-Hosted: audio file from media library.
		$this->add_control(
			'self_audio',
			array(
				'label'       => esc_html__( 'Choose Audio', 'really-simple-featured-audio' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'audio' ),
				'default'     => array(
					'url' => '',
				),
				'condition'   => array(
					'audio_source' => 'current_post',
					'audio_type'   => 'self',
				),
			)
		);

		// ── Embed: audio URL.
		$this->add_control(
			'embed_url',
			array(
				'label'       => esc_html__( 'Audio URL', 'really-simple-featured-audio' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://example.com/audio.mp3', 'really-simple-featured-audio' ),
				'description' => esc_html__( 'Paste the full link to an audio file, such as .mp3 or .wav.', 'really-simple-featured-audio' ),
				'label_block' => true,
				'options'     => false,
				'condition'   => array(
					'audio_source' => 'current_post',
					'audio_type'   => 'embed',
				),
			)
		);

		// ── Cover image, shown in the player for either audio type.
		$this->add_control(
			'cover_image',
			array(
				'label'     => esc_html__( 'Cover Image', 'really-simple-featured-audio' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array(
					'url' => '',
				),
				'condition' => array(
					'audio_source' => 'current_post',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Persist widget settings to the RSFA post meta keys so they stay in
	 * sync with the metabox on the CPT edit screen.
	 *
	 * Called by Elementor for every widget instance when the document is saved.
	 *
	 * @since 1.6.0
	 *
	 * @param array $settings The sanitised widget settings.
	 *
	 * @return array Settings (unchanged).
	 */
	public function on_save( array $settings ) {
		// Only sync meta when the source is "current_post".
		$audio_source = $settings['audio_source'] ?? 'current_post';

		if ( 'current_post' !== $audio_source ) {
			return $settings;
		}

		$document = \Elementor\Plugin::$instance->documents->get_current();

		if ( ! $document ) {
			return $settings;
		}

		$post_id = $document->get_main_id();

		if ( ! $post_id ) {
			return $settings;
		}

		// Only sync meta for post types enabled in RSFA settings.
		$post_type     = get_post_type( $post_id );
		$enabled_types = get_post_types();

		if ( ! $post_type || ! in_array( $post_type, $enabled_types, true ) ) {
			return $settings;
		}

		$audio_type = $settings['audio_type'] ?? 'self';

		// ── Build what we would write.
		$new_source   = in_array( $audio_type, array( 'self', 'embed' ), true ) ? $audio_type : 'self';
		$new_audio_id = '';
		$new_embed    = '';
		$new_cover    = absint( $settings['cover_image']['id'] ?? 0 );
		$new_cover    = $new_cover ? $new_cover : '';

		if ( 'self' === $new_source ) {
			$new_audio_id = absint( $settings['self_audio']['id'] ?? 0 );
			$new_audio_id = $new_audio_id ? $new_audio_id : '';
		} else {
			$new_embed = esc_url_raw( $settings['embed_url']['url'] ?? '' );
		}

		// ── Read current meta.
		$cur_source   = (string) get_post_meta( $post_id, RSFA_SOURCE_META_KEY, true );
		$cur_audio_id = (string) get_post_meta( $post_id, RSFA_META_KEY, true );
		$cur_cover    = (string) get_post_meta( $post_id, RSFA_COVER_META_KEY, true );
		$cur_embed    = (string) get_post_meta( $post_id, RSFA_EMBED_META_KEY, true );

		// Nothing changed – skip the write.  This prevents two widgets
		// on the same page from fighting: the one the user actually
		// edited will have new values while the untouched one will
		// still match the current meta and be silently skipped.
		if (
			$new_source === $cur_source &&
			(string) $new_audio_id === $cur_audio_id &&
			(string) $new_cover === $cur_cover &&
			$new_embed === $cur_embed
		) {
			return $settings;
		}

		// ── Write only when values differ.
		update_post_meta( $post_id, RSFA_SOURCE_META_KEY, $new_source );
		update_post_meta( $post_id, RSFA_META_KEY, $new_audio_id );
		update_post_meta( $post_id, RSFA_COVER_META_KEY, $new_cover );
		update_post_meta( $post_id, RSFA_EMBED_META_KEY, $new_embed );

		return $settings;
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Delegates to the existing RSFA shortcodes so the full rendering
	 * pipeline runs – including hover-autoplay, container data-attributes,
	 * and every filter other parts of the plugin hook into.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$audio_source = $settings['audio_source'] ?? 'current_post';

		// ── By Post ID.
		if ( 'by_post_id' === $audio_source ) {
			$post_id = trim( $settings['post_id'] ?? '' );

			if ( empty( $post_id ) ) {
				$this->render_placeholder( esc_html__( 'Please enter a Post ID.', 'really-simple-featured-audio' ) );
				return;
			}

			$output = $this->render_tracked_shortcode( '[rsfa_by_postid post_id="' . esc_attr( $post_id ) . '"]' );
			if ( ! empty( $output ) ) {
				echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is generated by RSFA shortcodes and properly escaped there.
			} else {
				$this->render_placeholder( esc_html__( 'No featured audio found for this Post ID.', 'really-simple-featured-audio' ) );
			}
			return;
		}

		// ── Current Post.
		// The [rsfa] shortcode reads directly from the post's RSFA meta
		// (synced by on_save / prefill) and runs through the full pipeline
		// including hover-autoplay support.
		$output = $this->render_tracked_shortcode( '[rsfa]' );

		if ( ! empty( $output ) ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is generated by RSFA shortcodes and properly escaped there.
		} else {
			$this->render_placeholder( esc_html__( 'No featured audio configured for this post.', 'really-simple-featured-audio' ) );
		}
	}

	/**
	 * Run a shortcode as an Elementor surface so analytics can tell it apart.
	 *
	 * @param string $shortcode Shortcode string.
	 * @return string
	 */
	private function render_tracked_shortcode( $shortcode ) {
		$previous = class_exists( '\RSFA\Analytics\Stamp' ) ? \RSFA\Analytics\Stamp::swap_surface( 'elementor' ) : '';
		$output   = do_shortcode( $shortcode );

		if ( class_exists( '\RSFA\Analytics\Stamp' ) ) {
			\RSFA\Analytics\Stamp::swap_surface( $previous );
		}

		return $output;
	}

	/**
	 * Get the post ID of the current document being edited / rendered.
	 *
	 * @return int|false
	 */
	private function get_current_post_id() {
		$document = \Elementor\Plugin::$instance->documents->get_current();

		return $document ? $document->get_main_id() : get_the_ID();
	}

	/**
	 * Render a placeholder message (visible only in the Elementor editor).
	 *
	 * @param string $message Placeholder text.
	 *
	 * @return void
	 */
	private function render_placeholder( $message ) {
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			printf(
				'<div class="rsfa-widget-placeholder" style="padding:20px;background:#f0f0f0;text-align:center;color:#666;border:1px dashed #ccc;">%s</div>',
				esc_html( $message )
			);
		}
	}
}
