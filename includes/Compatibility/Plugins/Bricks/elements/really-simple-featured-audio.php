<?php
/**
 * Bricks element for Really Simple Featured Audio
 *
 * @package RSFA
 * @subpackage Bricks\Elements
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RSFA\FrontEnd;

/**
 * Bricks element class for Really Simple Featured Audio.
 *
 * @since 1.0.0
 */
class Bricks_Really_Simple_Featured_Audio extends \Bricks\Element {

	/**
	 * Element name.
	 *
	 * @var string
	 */
	public $name = 'bricks_really_simple_featured_audio';

	/**
	 * Element category.
	 *
	 * @var string
	 */
	public $category = 'media';

	/**
	 * Element icon.
	 *
	 * @var string
	 */
	public $icon = 'ti-audio-clapper';

    /**
	 * CSS selector.
	 *
	 * @var string
	 */
    public $css_selector = '.bricks-really-simple-featured-audio';

    /**
	 * Element label.
	 *
	 * @return string Element label
	 */
	public function get_label() {
		return esc_html__( 'Really Simple Featured Audio', 'really-simple-featured-audio' );
	}

	/**
	 * Get element keywords.
	 *
	 * @return array Element keywords.
	 */
	public function get_keywords() {
		return array( 'audio', 'featured', 'media', 'really-simple-featured-audio' );
	}

	/**
	 * Render the element output on the frontend.
	 *
	 * @void
	 */
	public function render() {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			if ( is_admin() ) {
				echo '<p>' . esc_html__( 'Make sure Really Simple Featured Audio element is inside a Query Loop. In case you have done that, you can safely ignore this.', 'really-simple-featured-audio' ) . '</p>';
			}
			return;
		}

		// Set element attributes.
		$root_classes[] = 'bricks-really-simple-featured-audio';

		// Add 'class' attribute to element root tag.
		$this->set_attribute( '_root', 'class', $root_classes );

		$audio_markup = FrontEnd::get_featured_audio_markup( $post_id, '', 'bricks' );

		if ( $audio_markup ) {
			echo '<div ' . $this->render_attributes( '_root' ) . '>';
			echo $audio_markup;
			echo '</div>';
		} else {
			$image_url = get_the_post_thumbnail_url( $post_id );

			if ( $image_url ) {
				echo '<figure ' . $this->render_attributes( '_root' ) . '>';
				echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" class="bricks-really-simple-featured-audio">';
				echo '</figure>';
			}
		}
	}
}
