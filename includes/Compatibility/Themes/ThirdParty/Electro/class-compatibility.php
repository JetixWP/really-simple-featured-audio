<?php
/**
 * Electro theme compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Themes\ThirdParty\Electro;

use RSFA\Compatibility\Themes\Base_Compatibility;
use RSFA\Options;
use RSFA\Compatibility\Plugins\WooCommerce\Compatibility as BaseWooCompatibility;
use RSFA\Plugin;
use RSFA\FrontEnd;
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

		$this->id = 'electro';

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
		wp_register_style( 'rsfa-electro', $this->get_current_dir_url() . 'ThirdParty/Electro/styles.css', array(), filemtime( $this->get_current_dir() . 'ThirdParty/Electro/styles.css' ) );

		// Enqueue styles.
		wp_enqueue_style( 'rsfa-electro' );

		// Add generated CSS.
		wp_add_inline_style( 'rsfa-electro', Plugin::get_instance()->frontend_provider->generate_dynamic_css() );
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

		$base_woo_compat_instance = BaseWooCompatibility::get_instance();

		if ( ( ! $options->has( 'product_archives_visibility' ) && ! $product_archives_visibility ) || $product_archives_visibility ) {
			remove_action( 'woocommerce_before_shop_loop_item_title', array( $base_woo_compat_instance, 'get_woo_archives_audio' ), 10 );
			add_filter( 'electro_template_loop_product_thumbnail', array( $this, 'set_loop_thumbnail' ), 10 );
		}

		remove_action( 'post_thumbnail_html', array( FrontEnd::get_instance(), 'get_post_audio' ), 10, 5 );

		add_filter( 'electro_wc_single_product_image_thumbnail_html', array( $this, 'woo_get_audio' ), 10 );
	}

	/**
	 * Sets the loop thumbnail with audio support.
	 *
	 * @param string $html The HTML markup for the thumbnail.
	 *
	 * @return string
	 */
	public function set_loop_thumbnail( $html ) {
		global $product;

		if ( ! is_object( $product ) ) {
			return $html;
		}

		$product_id = $product->get_id();

		// Exit if no audio.
		if ( ! FrontEnd::has_featured_audio( $product_id ) ) {
			return $html;
		}

		// The archive method prints its markup, so capture it for this filter.
		ob_start();
		BaseWooCompatibility::get_instance()->get_woo_archives_audio( $product_id );

		return ob_get_clean();
	}


	/**
	 * Adds featured audio to WooCommerce single product thumbnails.
	 *
	 * @param string $html          The HTML markup for the thumbnail.
	 *
	 * @return string
	 */
	public function woo_get_audio( $html ) {
		global $product;

		$product_id = $product->get_id();
		$post_type  = get_post_type( $product_id ) ?? '';

		// Get enabled post types.
		$post_types = get_post_types();

		$has_audio  = FrontEnd::has_featured_audio( $product_id );
		$audio_html = '';

		if ( ! empty( $post_types ) ) {
			if ( in_array( $post_type, $post_types, true ) ) {
				$audio_html = BaseWooCompatibility::woo_audio_markup( $product->get_id(), 'electro-wc-product-gallery__image', '', true );
			}
		}

		if ( $has_audio ) {
      $html = $audio_html . $html; // phpcs:ignore;
		}

		return $html;
	}
}
