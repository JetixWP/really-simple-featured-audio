<?php
/**
 * Woodmart theme compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Themes\ThirdParty\Woodmart;

use RSFA\Compatibility\Themes\Base_Compatibility;
use RSFA\Compatibility\Plugins\WooCommerce\Compatibility as BaseWooCompatibility;
use RSFA\FrontEnd;
use RSFA\Options;
use RSFA\Plugin;

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
	 * Products whose main gallery already got the audio slide.
	 *
	 * @var array $gallery_products
	 */
	protected $gallery_products = array();

	/**
	 * Product waiting for its audio thumbnail in the thumbnails carousel.
	 *
	 * @var int $pending_thumb_product
	 */
	protected $pending_thumb_product = 0;

	/**
	 * Classes of the thumbnail item the audio thumbnail takes the place of, null when none.
	 *
	 * @var string|null $pending_thumb_classes
	 */
	protected $pending_thumb_classes = null;

	/**
	 * Products whose loop thumbnail was replaced with the audio.
	 *
	 * @var array $loop_products
	 */
	protected $loop_products = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->id = 'woodmart';

		$this->override_woo_templates();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Register styles.
		wp_register_style( 'rsfa-woodmart', $this->get_current_dir_url() . 'ThirdParty/Woodmart/styles.css', array(), filemtime( $this->get_current_dir() . 'ThirdParty/Woodmart/styles.css' ) );

		// Enqueue styles.
		wp_enqueue_style( 'rsfa-woodmart' );

		// Add generated CSS.
		wp_add_inline_style( 'rsfa-woodmart', Plugin::get_instance()->frontend_provider->generate_dynamic_css() );

		// Enqueued where the audio is printed, product loops can show up on any page.
		wp_register_script( 'rsfa-woodmart', $this->get_current_dir_url() . 'ThirdParty/Woodmart/scripts.js', array( 'jquery' ), filemtime( $this->get_current_dir() . 'ThirdParty/Woodmart/scripts.js' ), true );
	}

	/**
	 * Overrides theme Woo templates.
	 *
	 * @return void
	 */
	public function override_woo_templates() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$options                     = Options::get_instance();
		$product_archives_visibility = $options->get( 'product_archives_visibility' );
		$base_woo_compat_instance    = BaseWooCompatibility::get_instance();

		if ( ( ! $options->has( 'product_archives_visibility' ) && ! $product_archives_visibility ) || $product_archives_visibility ) {
			// Woodmart prints its own loop thumbnail, so the default audio would sit on top of the image.
			remove_action( 'woocommerce_before_shop_loop_item_title', array( $base_woo_compat_instance, 'get_woo_archives_audio' ), 10 );
			add_filter( 'woodmart_get_product_thumbnail', array( $this, 'set_loop_thumbnail' ), 10, 4 );
			add_filter( 'woocommerce_placeholder_img', array( $this, 'set_loop_placeholder' ) );
		}

		// Woodmart wraps every gallery slide in `.wd-carousel-item`, so the default markup ends up outside the carousel.
		remove_filter( 'woocommerce_single_product_image_thumbnail_html', array( $base_woo_compat_instance, 'woo_get_audio' ), 10 );

		// After Woodmart's own gallery video filter, which rebuilds the slide markup.
		add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'woo_get_audio' ), 20, 2 );

		// Woodmart builds the thumbnails carousel from attachments, so the audio needs its own thumbnail there.
		add_filter( 'woodmart_single_product_thumbnail_classes', array( $this, 'mark_audio_thumbnail' ), 20 );
		add_filter( 'wp_get_attachment_image', array( $this, 'add_audio_thumbnail' ), 20 );
	}

	/**
	 * Adds the featured audio as the first slide of the single product gallery.
	 *
	 * @param string $html          Gallery slide markup.
	 * @param int    $attachment_id Attachment ID.
	 *
	 * @return string
	 */
	public function woo_get_audio( $html, $attachment_id ) {
		global $product;

		if ( ! is_object( $product ) ) {
			return $html;
		}

		$product_id = $product->get_id();

		// Only before the main image, once per gallery.
		if ( (int) $attachment_id !== (int) $product->get_image_id() || isset( $this->gallery_products[ $product_id ] ) ) {
			return $html;
		}

		if ( ! FrontEnd::has_featured_audio( $product_id ) ) {
			return $html;
		}

		$audio_html = BaseWooCompatibility::woo_audio_markup( $product_id, 'rsfa-woodmart-audio' );

		if ( ! $audio_html ) {
			return $html;
		}

		$this->gallery_products[ $product_id ] = true;
		$this->pending_thumb_product           = $product_id;

		wp_enqueue_script( 'rsfa-woodmart' );

		// Keeps the player controls draggable inside the carousel.
		$audio_html = str_replace( 'class="rsfa-audio-wrapper"', 'class="rsfa-audio-wrapper swiper-no-swiping"', $audio_html );

		// Matches the main image size, Woodmart measures the first slide for the thumbnails height.
		$image = wp_get_attachment_image_src( $product->get_image_id(), 'woocommerce_single' );
		$style = ! empty( $image[1] ) && ! empty( $image[2] ) ? ' style="--rsfa-woodmart-ratio:' . absint( $image[1] ) . '/' . absint( $image[2] ) . ';"' : '';

		return '<div class="wd-carousel-item rsfa-woodmart-slide"' . $style . '>' . $audio_html . '</div>' . $html;
	}

	/**
	 * Gives the first thumbnail item to the audio thumbnail.
	 *
	 * @param string $classes Thumbnail item classes.
	 *
	 * @return string
	 */
	public function mark_audio_thumbnail( $classes ) {
		if ( ! $this->pending_thumb_product || null !== $this->pending_thumb_classes ) {
			return $classes;
		}

		// The real classes move to the image item that follows.
		$this->pending_thumb_classes = (string) $classes;

		return 'rsfa-woodmart-thumb';
	}

	/**
	 * Prints the audio thumbnail in front of the first thumbnail image.
	 *
	 * @param string $html Attachment image markup.
	 *
	 * @return string
	 */
	public function add_audio_thumbnail( $html ) {
		if ( null === $this->pending_thumb_classes ) {
			return $html;
		}

		$thumbnail = apply_filters( 'rsfa_featured_audio_thumbnail', RSFA_PLUGIN_URL . 'assets/images/audio_frame.png' );
		$thumbnail = apply_filters( 'rsfa_default_woo_gallery_audio_thumb', $thumbnail );

		// No `img` here, Woodmart swaps the first thumbnail image for variations.
		$audio_thumb = '<span class="rsfa-woodmart-thumb-image" role="img" aria-label="' . esc_attr__( 'Featured audio', 'really-simple-featured-audio' ) . '" style="background-image:url(' . esc_url( $thumbnail ) . ');"></span>';

		$html = $audio_thumb . '</div><div class="' . esc_attr( trim( 'wd-carousel-item ' . $this->pending_thumb_classes ) ) . '">' . $html;

		$this->pending_thumb_product = 0;
		$this->pending_thumb_classes = null;

		return $html;
	}

	/**
	 * Replaces the product loop thumbnail with the featured audio.
	 *
	 * @param string      $img       Thumbnail markup.
	 * @param \WC_Product $product   Product.
	 * @param string      $size      Image size.
	 * @param int         $attach_id Attachment ID.
	 *
	 * @return string
	 */
	public function set_loop_thumbnail( $img, $product, $size, $attach_id ) {
		if ( ! is_object( $product ) ) {
			return $img;
		}

		$product_id = $product->get_id();

		// Any other attachment is the hover image, which would cover the player.
		if ( (int) $attach_id !== (int) $product->get_image_id() ) {
			return isset( $this->loop_products[ $product_id ] ) ? '' : $img;
		}

		$audio_html = $this->get_loop_audio( $product_id );

		return $audio_html ? $audio_html : $img;
	}

	/**
	 * Replaces the placeholder of a product loop item without an image with the featured audio.
	 *
	 * @param string $html Placeholder markup.
	 *
	 * @return string
	 */
	public function set_loop_placeholder( $html ) {
		global $product;

		// Woodmart skips its thumbnail filter for placeholders.
		if ( ! is_object( $product ) || ! doing_action( 'woocommerce_before_shop_loop_item_title' ) ) {
			return $html;
		}

		$audio_html = $this->get_loop_audio( $product->get_id() );

		return $audio_html ? $audio_html : $html;
	}

	/**
	 * Get featured audio markup for a product loop item.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return string
	 */
	protected function get_loop_audio( $product_id ) {
		if ( ! FrontEnd::has_featured_audio( $product_id ) ) {
			return '';
		}

		$audio_html = BaseWooCompatibility::get_instance()->woo_get_audio( '', 0, true );

		if ( ! $audio_html ) {
			return '';
		}

		$this->loop_products[ $product_id ] = true;

		wp_enqueue_script( 'rsfa-woodmart' );

		// Keeps the card as tall as the image cards around it.
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'woocommerce_thumbnail' );
		$size  = ! empty( $image[1] ) && ! empty( $image[2] ) ? array( $image[1], $image[2] ) : array_values( wc_get_image_size( 'woocommerce_thumbnail' ) );
		$style = ! empty( $size[0] ) && ! empty( $size[1] ) ? ' style="--rsfa-woodmart-ratio:' . absint( $size[0] ) . '/' . absint( $size[1] ) . ';"' : '';

		return '<div class="rsfa-woodmart-loop-audio"' . $style . '>' . $audio_html . '</div>';
	}
}
