<?php
/**
 * Codeixer Product Gallery Slider compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Plugins\CIXWooGallerySlider;

defined( 'ABSPATH' ) || exit;

use RSFA\Compatibility\Plugins\Base_Compatibility;

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

		$this->id = 'cix-woo-gallery-slider';

		$this->setup();
	}

	/**
	 * Sets up hooks and filters.
	 *
	 * @return void
	 */
	public function setup() {
		if ( function_exists( 'wpgs_get_template' ) && has_filter( 'wc_get_template', 'wpgs_get_template' ) ) {
			remove_filter( 'wc_get_template', 'wpgs_get_template' );
			add_filter( 'wc_get_template', array( $this, 'filter_product_image_template' ), 10, 5 );
		}

		// Newer versions include their gallery template directly instead of through `wc_get_template`.
		add_action( 'wp', array( $this, 'replace_gallery_output' ), 20 );
	}

	/**
	 * Swaps the slider's own gallery output for the template with featured audio.
	 *
	 * @return void
	 */
	public function replace_gallery_output() {
		global $wp_filter;

		$hook = 'woocommerce_before_single_product_summary';

		if ( empty( $wp_filter[ $hook ] ) || empty( $wp_filter[ $hook ]->callbacks[20] ) ) {
			return;
		}

		foreach ( $wp_filter[ $hook ]->callbacks[20] as $callback ) {
			$function = $callback['function'];

			if ( is_array( $function ) && is_object( $function[0] ) && is_a( $function[0], 'Product_Gallery_Sldier\Product' ) && 'wpgs_product_image' === $function[1] ) {
				remove_action( $hook, $function, 20 );
				add_action( $hook, array( $this, 'product_image' ), 20 );
				return;
			}
		}
	}

	/**
	 * Outputs the product gallery with featured audio.
	 *
	 * @return void
	 */
	public function product_image() {
		$located = $this->filter_product_image_template( '', 'single-product/product-image.php', array(), '', '' );

		if ( $located && file_exists( $located ) ) {
			include $located;
		}
	}

	/**
	 * Updates plugin's template for Featured audio.
	 *
	 * @param string $located Located at absolute file path.
	 * @param string $template_name Widget name/id.
	 * @param array  $args Arguments from widget.
	 * @param string $template_path Widget path.
	 * @param string $default_path Default location.
	 */
	public function filter_product_image_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( 'single-product/product-image.php' === $template_name ) {
			$template_directory = untrailingslashit( plugin_dir_path( __FILE__ ) );
			$located            = $template_directory . '/templates/product-image.php';
			$located            = apply_filters( 'rsfa_cix_woo_gallery_slider_template', $located );
		}

		return $located;
	}
}
