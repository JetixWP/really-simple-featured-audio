<?php
/**
 * Admin View: Upgrade Tab Settings
 *
 * @package RSFA
 * @since 1.5.0
 */

namespace RSFA\Settings\views;

defined( 'ABSPATH' ) || exit;

$rsfa_pro_url     = RSFA_PLUGIN_PRO_URL . '/?utm_campaign=settings-protab&utm_source=rsfa-plugin';
$rsfa_pricing_url = $rsfa_pro_url . '#pricing';
$rsfa_compare_url = $rsfa_pro_url . '#compare';

$rsfa_pro_features = array(
	array(
		'icon'  => 'dashicons-cart',
		'title' => __( 'WooCommerce gallery control', 'really-simple-featured-audio' ),
		'desc'  => __( 'Pick where the audio sits in the product gallery and use your own gallery thumbnail for it.', 'really-simple-featured-audio' ),
	),
	array(
		'icon'  => 'dashicons-art',
		'title' => __( 'Player appearance', 'really-simple-featured-audio' ),
		'desc'  => __( 'Match the audio player to your brand with your own accent color and a light, dark or automatic player.', 'really-simple-featured-audio' ),
	),
	array(
		'icon'  => 'dashicons-controls-play',
		'title' => __( 'Extended play on hover', 'really-simple-featured-audio' ),
		'desc'  => __( 'Preview audio on hover in shop, archive and listing pages, with finer control over when it plays.', 'really-simple-featured-audio' ),
	),
	array(
		'icon'  => 'dashicons-admin-appearance',
		'title' => __( 'Premium and custom theme support', 'really-simple-featured-audio' ),
		'desc'  => __( 'Works with more premium themes out of the box. Using something unusual? Ask and we will add support.', 'really-simple-featured-audio' ),
	),
	array(
		'icon'  => 'dashicons-sos',
		'title' => __( 'Priority support from the developer', 'really-simple-featured-audio' ),
		'desc'  => __( 'Skip the forum queue. Your questions go straight to the person who builds the plugin.', 'really-simple-featured-audio' ),
	),
	array(
		'icon'  => 'dashicons-lightbulb',
		'title' => __( 'Shape the roadmap', 'really-simple-featured-audio' ),
		'desc'  => __( 'Feature and compatibility requests from PRO users go to the front of the line.', 'really-simple-featured-audio' ),
	),
);

$rsfa_pro_faqs = array(
	array(
		'q' => __( 'Is this a subscription?', 'really-simple-featured-audio' ),
		'a' => __( 'No. You pay once and receive updates for life. There are no renewals and nothing to cancel.', 'really-simple-featured-audio' ),
	),
	array(
		'q' => __( 'Will my current settings and audio carry over?', 'really-simple-featured-audio' ),
		'a' => __( 'Yes. PRO runs alongside the free plugin and adds to it. Nothing is reset or removed when you upgrade.', 'really-simple-featured-audio' ),
	),
	array(
		'q' => __( 'What if PRO does not work for me?', 'really-simple-featured-audio' ),
		'a' => __( 'Ask for a refund within 14 days of purchase and you get your money back, no questions asked.', 'really-simple-featured-audio' ),
	),
);
?>

<div class="upgrade-content">

	<section class="rsfa-pro-hero">
		<p class="rsfa-pro-eyebrow">🎉 <?php esc_html_e( 'Anniversary deal · up to 50% off for a limited time', 'really-simple-featured-audio' ); ?></p>
		<h1 class="tab-heading"><?php esc_html_e( 'Get more out of every featured audio with RSFA PRO', 'really-simple-featured-audio' ); ?></h1>
		<p class="rsfa-pro-lead"><?php esc_html_e( 'Control exactly how audio shows up in your WooCommerce store, make the player match your brand, and get help directly from the developer when you need it.', 'really-simple-featured-audio' ); ?></p>
		<div class="rsfa-pro-actions">
			<a href="<?php echo esc_url( $rsfa_pricing_url ); ?>" target="_blank" rel="noopener noreferrer" class="rsfa-button button-primary"><?php esc_html_e( 'Get PRO from $59', 'really-simple-featured-audio' ); ?></a>
			<a href="<?php echo esc_url( $rsfa_compare_url ); ?>" target="_blank" rel="noopener noreferrer" class="rsfa-button button-secondary"><?php esc_html_e( 'Compare free vs PRO', 'really-simple-featured-audio' ); ?></a>
		</div>
		<p class="rsfa-pro-trust">
			<span>✔ <?php esc_html_e( 'One-time payment', 'really-simple-featured-audio' ); ?></span>
			<span>✔ <?php esc_html_e( 'Lifetime updates', 'really-simple-featured-audio' ); ?></span>
			<span>✔ <?php esc_html_e( '14-day money-back guarantee', 'really-simple-featured-audio' ); ?></span>
		</p>
	</section>

	<section class="rsfa-pro-section">
		<h2 class="rsfa-pro-section-title"><?php esc_html_e( 'What you unlock', 'really-simple-featured-audio' ); ?></h2>
		<ul class="rsfa-pro-features">
			<?php foreach ( $rsfa_pro_features as $rsfa_feature ) : ?>
				<li class="rsfa-pro-feature">
					<span class="rsfa-pro-feature__icon dashicons <?php echo esc_attr( $rsfa_feature['icon'] ); ?>" aria-hidden="true"></span>
					<div>
						<h3><?php echo esc_html( $rsfa_feature['title'] ); ?></h3>
						<p><?php echo esc_html( $rsfa_feature['desc'] ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="rsfa-pro-section rsfa-pro-faq">
		<h2 class="rsfa-pro-section-title"><?php esc_html_e( 'Common questions', 'really-simple-featured-audio' ); ?></h2>
		<?php foreach ( $rsfa_pro_faqs as $rsfa_faq ) : ?>
			<details>
				<summary><?php echo esc_html( $rsfa_faq['q'] ); ?></summary>
				<p><?php echo esc_html( $rsfa_faq['a'] ); ?></p>
			</details>
		<?php endforeach; ?>
		<p class="rsfa-pro-contact">
			<?php
			printf(
				wp_kses(
					/* translators: 1: opening anchor tag, 2: closing anchor tag */
					__( 'Still unsure? Email %1$skrishna@jetixwp.com%2$s and I will personally get back to you.', 'really-simple-featured-audio' ),
					array( 'a' => array( 'href' => array() ) )
				),
				'<a href="mailto:krishna@jetixwp.com">',
				'</a>'
			);
			?>
		</p>
	</section>

</div>
