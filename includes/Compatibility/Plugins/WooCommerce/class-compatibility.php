<?php
/**
 * WooCommerce compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Plugins\WooCommerce;

defined( 'ABSPATH' ) || exit;

use RSFA\FrontEnd;
use RSFA\Options;
use RSFA\Plugin;
use RSFA\Compatibility\Plugins\Base_Compatibility;
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
	 * A counter variable.
	 *
	 * @var int $counter
	 */
	protected $counter;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->id = 'woocommerce';

		$this->counter = 0;
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
		$options = Options::get_instance();

		// Registers WooCommerce related settings tab.
		add_filter( 'rsfa_get_settings_pages', array( $this, 'register_settings' ) );

		// Include post type.
		add_filter( 'rsfa_post_types_support', array( $this, 'update_post_types' ) );

		// Update default settings for Enabled Post Types.
		add_filter( 'rsfa_default_enabled_post_types', array( $this, 'update_default_enabled_post_types' ) );

		// Enable product post type by default.
		add_filter( 'rsfa_get_enabled_post_types', array( $this, 'update_enabled_post_types' ) );

		// Custom styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Adds support for single product.
		add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'woo_get_audio' ), 10, 2 );

		// Update body classes for Woo.
		add_filter( 'rsfa_body_classes', array( $this, 'modify_body_classes' ) );

		$product_archives_visibility = $options->get( 'product_archives_visibility' );

		if ( ( ! $options->has( 'product_archives_visibility' ) && ! $product_archives_visibility ) || $product_archives_visibility ) {
			// Adds support for product archives.
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'get_woo_archives_audio' ), 10 );
		}

		add_action( 'rsfa_woo_archives_product_thumbnails', 'woocommerce_template_loop_product_thumbnail', 10 );

		if ( $options->get( 'product_audio_external_url', false ) ) {
			add_filter( 'rsfa_has_featured_audio', array( $this, 'maybe_has_external_audio' ), 10, 2 );
			add_filter( 'rsfa_audio_source_url', array( $this, 'maybe_use_external_audio' ), 10, 2 );
		}
	}

	/**
	 * Get an External product's URL when it points to an audio file.
	 *
	 * @since 1.6.0
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return string Empty when the product is not external or the URL is not an audio file.
	 */
	public function get_external_audio_url( $product_id ) {
		if ( 'product' !== get_post_type( $product_id ) ) {
			return '';
		}

		$product = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_type( 'external' ) ) {
			return '';
		}

		$url       = (string) get_post_meta( $product_id, '_product_url', true );
		$path      = (string) wp_parse_url( $url, PHP_URL_PATH );
		$extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

		if ( ! $extension || ! in_array( $extension, wp_get_audio_extensions(), true ) ) {
			return '';
		}

		return esc_url_raw( $url );
	}

	/**
	 * Count an External product's audio link as its featured audio.
	 *
	 * @since 1.6.0
	 *
	 * @param bool $has_audio Whether the post has a featured audio.
	 * @param int  $post_id   Post ID.
	 *
	 * @return bool
	 */
	public function maybe_has_external_audio( $has_audio, $post_id ) {
		return $has_audio || '' !== $this->get_external_audio_url( $post_id );
	}

	/**
	 * Use an External product's audio link when no featured audio is set.
	 *
	 * @since 1.6.0
	 *
	 * @param array $audio   Array with `url` and `source` keys.
	 * @param int   $post_id Post ID.
	 *
	 * @return array
	 */
	public function maybe_use_external_audio( $audio, $post_id ) {
		if ( ! empty( $audio['url'] ) ) {
			return $audio;
		}

		$url = $this->get_external_audio_url( $post_id );

		if ( $url ) {
			$audio = array(
				'url'    => $url,
				'source' => 'embed',
			);
		}

		return $audio;
	}

	/**
	 * Include post types.
	 *
	 * @param array $post_types Existing post types.
	 *
	 * @return array Supported post types
	 */
	public function update_post_types( $post_types ) {
		$post_types['product'] = __( 'Products', 'really-simple-featured-audio' );

		return $post_types;
	}

	/**
	 * Include Product post type at default enabled post types.
	 *
	 * @param array $post_types Default post types.
	 *
	 * @return array Supported post types
	 */
	public function update_default_enabled_post_types( $post_types ) {
		$post_types['product'] = true;

		return $post_types;
	}

	/**
	 * Enable Product post type by default.
	 *
	 * @param array $enabled_post_types Existing post types.
	 *
	 * @return array Supported post types
	 */
	public function update_enabled_post_types( $enabled_post_types ) {
		$post_types = Options::get_instance()->get( 'post_types' );
		$post_types = is_array( $post_types ) ? array_keys( $post_types ) : '';

		if ( ! is_array( $post_types ) && empty( $post_types ) ) {
			$enabled_post_types[] = 'product';
		}

		return $enabled_post_types;
	}

	/**
	 * Conditionally enqueue scripts & styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$post = get_post();

		// Dummy style for inline styles.
		wp_register_style( 'rsfa-woocommerce', false, array(), time() );

		if ( is_woocommerce() || ( ! empty( $post->post_content ) && strstr( $post->post_content, '[product_page' ) ) ) {
			wp_enqueue_style( 'rsfa-woocommerce' );
			wp_add_inline_style( 'rsfa-woocommerce', $this->generate_inline_styles() );
		}
	}

	/**
	 * Generate inline styles.
	 *
	 * @return string Inline styles.
	 */
	public function generate_inline_styles() {
		$styles = '';

		// Set product audios to 3/2 aspect ratio.
		$styles .= '.woocommerce ul.products li.product .woocommerce-product-gallery__image .rsfa-audio-wrapper,
					.woocommerce div.product div.woocommerce-product-gallery figure.woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image .rsfa-audio-wrapper,
				 .woocommerce.product.rsfa-has-audio div.woocommerce-product-gallery figure.woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image .rsfa-audio-wrapper
				 { height: auto; width: 100% !important; aspect-ratio: 3/2; display: flex; align-items: center; background: rgba(0, 0, 0, 0.1); padding: 20px; box-sizing: border-box; }';

		$styles .= '.woocommerce-loop-product__title { margin-top: 20px; }';

		$styles .= '.woocommerce.product.rsfa-has-audio .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image + .woocommerce-product-gallery__image--placeholder
					{ display: none; }';

		return apply_filters( 'rsfa_woo_generated_dynamic_css', $styles );
	}


	/**
	 * Product Audio Markup.
	 *
	 * @param int    $id Product ID.
	 * @param string $wrapper_class Wrapper markup classes.
	 * @param string $wrapper_attributes Wrapper markup attributes.
	 * @param bool   $thumbnail_only Whether only thumbnail should be returned.
	 *
	 * @return string
	 */
	public static function woo_audio_markup( $id, $wrapper_class = 'woocommerce-product-gallery__image', $wrapper_attributes = '', $thumbnail_only = false ) {
		$post_type = get_post_type( $id ) ?? 'product';

		// Get enabled post types.
		$post_types = get_post_types();

		if ( empty( $post_types ) || ! in_array( $post_type, $post_types, true ) ) {
			return '';
		}

		$img_url           = RSFA_PLUGIN_URL . 'assets/images/audio_frame.png';
		$thumbnail         = apply_filters( 'rsfa_featured_audio_thumbnail', $img_url );
		$thumbnail         = apply_filters( 'rsfa_default_woo_gallery_audio_thumb', $thumbnail );
		$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );

		// Return early if thumbnail is only required.
		if ( $thumbnail_only ) {
			return '<div class="' . esc_attr( $wrapper_class ) . '" data-thumb="' . esc_url( $thumbnail ) . '"' . esc_attr( $wrapper_attributes ) . '><img width="' . esc_attr( $gallery_thumbnail['width'] ) . '" height="' . esc_attr( $gallery_thumbnail['height'] ) . '" src="' . esc_url( $thumbnail ) . '" alt /></div>';
		}

		// A self source without a file shows nothing here, unlike posts.
		$audio = FrontEnd::get_audio_source_url( $id, false );

		if ( ! $audio['url'] ) {
			return '';
		}

		$controls = FrontEnd::get_player_controls( $audio['source'] );
		$config   = FrontEnd::get_player_config( $id, $audio['url'], $audio['source'] );

		// Fallback player for when JavaScript is off; the JWP player replaces it.
		$fallback = sprintf(
			'<audio class="rsfa-audio" id="rsfa_audio_%1$s" src="%2$s" style="max-width:100%%;display:block;" controls preload="none"%3$s%4$s></audio>',
			esc_attr( $id ),
			esc_url( $audio['url'] ),
			$controls['loop'] ? ' loop' : '',
			$controls['muted'] ? ' muted' : ''
		);

		$audio_html = '<div id="rsfa-id-' . esc_attr( $id ) . '" class="' . esc_attr( $wrapper_class ) . '" data-thumb="' . esc_url( $thumbnail ) . '"' . esc_attr( $wrapper_attributes ) . FrontEnd::get_player_attribute( $config ) . '><div class="rsfa-audio-wrapper">' . $fallback . '</div></div>';

		/**
		 * Filters the WooCommerce product audio markup.
		 *
		 * @since 1.6.0
		 *
		 * @param string $audio_html Player markup.
		 * @param int    $id Product ID.
		 * @param array  $config Player config.
		 */
		return apply_filters( 'rsfa_woo_audio_html', $audio_html, $id, $config );
	}

	/**
	 * Filter method for getting WooCommerce audio markup at products.
	 *
	 * @param string $html Thumbnail markup for products.
	 * @param int    $post_thumbnail_id Thumbnail ID.
	 * @param bool   $is_archives Whether to run at archives.
	 * @return string
	 */
	public function woo_get_audio( $html, $post_thumbnail_id, $is_archives = false ) {
		global $product;

		if ( 'object' !== gettype( $product ) ) {
			return $html;
		}

		$product_id = $product->get_id();
		$post_type  = get_post_type( $product_id ) ?? '';

		// Get enabled post types.
		$post_types = get_post_types();

		$audio_html = self::woo_audio_markup( $product->get_id() );

		if ( ! empty( $post_types ) ) {
			$updated_html = $audio_html . $html;
			if ( ! $is_archives && apply_filters( 'rsfa_has_modified_audio_thumbnail_html', false, $this->counter, $product ) ) {
				$html = apply_filters( 'rsfa_audio_thumbnail_html', $html, $audio_html, $this->counter, $product );
			} elseif ( 0 === $this->counter || $is_archives ) {
				$html = $updated_html;
			}

			if ( in_array( $post_type, $post_types, true ) && ! $is_archives ) {
				++$this->counter;
			}
		}
		return $html;
	}

	/**
	 * Get audio for woo archives.
	 *
	 * @param int $post_id Product ID.
	 * @return void
	 */
	public function get_woo_archives_audio( $post_id = '' ) {
		$audio_markup = $this->woo_get_audio( '', 0, true );

		if ( $audio_markup ) {
			echo wp_kses( $audio_markup, Plugin::get_instance()->frontend_provider->get_allowed_html() );
		} else {
			do_action( 'rsfa_woo_archives_product_thumbnails', $post_id );
		}
	}

	/**
	 * Modify page body classes.
	 *
	 * @param array $classes Body classes.
	 *
	 * @return array
	 */
	public function modify_body_classes( $classes ) {
		$options = Options::get_instance();

		// Default is enabled.
		$product_archives_visibility = $options->has( 'product_archives_visibility' ) ? $options->get( 'product_archives_visibility' ) : true;

		if ( $product_archives_visibility && ( is_shop() || is_product_category() || is_product_tag() ) ) {
			$classes[] = 'rsfa-archives-support';
		}

		return $classes;
	}
}
