<?php
/**
 * Admin View: Settings
 *
 * @package RSFA
 */

namespace RSFA\Settings\Views;

use RSFA\Settings\Register;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab_exists        = isset( $tabs[ $current_tab ] ) || has_action( 'rsfa_sections_' . $current_tab ) || has_action( 'rsfa_settings_' . $current_tab ) || has_action( 'rsfa_settings_tabs_' . $current_tab );
$current_tab_label = isset( $tabs[ $current_tab ] ) ? $tabs[ $current_tab ] : '';


if ( ! $tab_exists ) {
	wp_safe_redirect( admin_url( 'admin.php?page=rsfa-settings' ) );
	exit;
}
?>
<div class="wrap rsfa <?php echo esc_attr( $current_tab ); ?>">
	<div class="plugin-header">
		<div class="plugin-header-wrap">
			<div class="plugin-info">
				<h1 class="menu-title"><?php esc_html_e( 'Really Simple Featured Audio', 'really-simple-featured-audio' ); ?></h1>
				<?php do_action( 'rsfa_extend_plugin_header' ); ?>
				<div class="plugin-version">
					<span>v<?php echo esc_html( RSFA_VERSION ); ?></span>
				</div>
			</div>

			<div class="brand-info">
				<a href="https://jetixwp.com?utm_campaign=settings-header&utm_source=rsfa-plugin" target="_blank"><img class="brand-logo" src="<?php echo esc_url( RSFA_PLUGIN_URL . 'assets/images/jwp-icon-dark.svg' ); ?>" alt="RSFA"></a>
			</div>
		</div>
	</div>
	<div class="rsfa-wrapper">
			<div class="nav-content">
				<nav class="nav-tab-wrapper rsfa-nav-tab-wrapper">
					<?php

					foreach ( $tabs as $slug => $label ) {
						echo '<a href="' . esc_url( admin_url( 'admin.php?page=rsfa-settings&tab=' . $slug ) ) . '" class="nav-tab nav-tab-' . esc_attr( $slug ) . ' ' . ( $current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
					}

					do_action( 'rsfa_settings_tabs' );

					?>
				</nav>
			</div>
			<div class="tab-content">
				<form method="<?php echo esc_attr( apply_filters( 'rsfa_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
					<div class="content">
						<h1 class="screen-reader-text"><?php echo esc_html( $current_tab_label ); ?></h1>
						<?php
						do_action( 'rsfa_sections_' . $current_tab );

						self::show_messages();

						do_action( 'rsfa_settings_' . $current_tab );
						?>
						<p class="submit">
							<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
								<button name="save" class="button-primary rsfa-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'really-simple-featured-audio' ); ?>"><?php esc_html_e( 'Save changes', 'really-simple-featured-audio' ); ?></button>
							<?php endif; ?>
							<?php wp_nonce_field( 'rsfa-settings' ); ?>
						</p>
					</div>
				</form>

				<div class="sidebar">

					<?php if ( ! Register::is_review_card_dismissed() ) : ?>
						<?php
						$rsfa_dismiss_review_url = wp_nonce_url(
							add_query_arg( 'rsfa_dismiss_review', '1' ),
							'rsfa_dismiss_review_card'
						);
						?>
						<div class="notice-box is-dismissible" data-rsfa-dismiss="review-card">
							<a class="notice-dismiss rsfa-dismiss-review" href="<?php echo esc_url( $rsfa_dismiss_review_url ); ?>">
								<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'really-simple-featured-audio' ); ?></span>
							</a>
							<div>
								<h3><?php esc_html_e( 'Help keep this plugin free & updated', 'really-simple-featured-audio' ); ?></h3>
								<p class="desc"><?php esc_html_e( 'Your review means a lot. It helps others discover that Really Simple Featured Audio is free and actively maintained. If the plugin has helped you, please consider leaving your honest review/feedback on WordPress.org. Thank you!', 'really-simple-featured-audio' ); ?></p>
							</div>
							<div>
								<a class="button button-primary" href="https://wordpress.org/support/plugin/really-simple-featured-audio/reviews/#new-post" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a Review', 'really-simple-featured-audio' ); ?></a>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( ! class_exists( '\RSFA_Pro\Plugin' ) ) : ?>
						<?php $rsfa_pro_url = RSFA_PLUGIN_PRO_URL . '/?utm_campaign=settings-sidebar&utm_source=rsfa-plugin'; ?>
						<div class="upgrade-box">
							<div>
								<h3>🎉 &nbsp;<?php esc_html_e( 'Anniversary deal: RSFA PRO from $59', 'really-simple-featured-audio' ); ?></h3>
								<p class="desc"><?php esc_html_e( 'Get more control over product audio in WooCommerce, wider theme support, and direct help from the developer. One payment, lifetime updates.', 'really-simple-featured-audio' ); ?></p>
							</div>
							<div class="rsfa-anniversary-deal">
								<p class="rsfa-anniversary-deal__label"><?php esc_html_e( 'Special Anniversary Deal', 'really-simple-featured-audio' ); ?> <span class="rsfa-anniversary-deal__badge"><?php esc_html_e( 'Save up to 50%', 'really-simple-featured-audio' ); ?></span></p>
								<ul class="rsfa-anniversary-deal__plans">
									<li>
										<span class="rsfa-anniversary-deal__plan"><?php esc_html_e( 'Single site', 'really-simple-featured-audio' ); ?> <em><?php esc_html_e( 'Save 34%', 'really-simple-featured-audio' ); ?></em></span>
										<span class="rsfa-anniversary-deal__amounts"><s>$89</s> <strong>$59</strong></span>
									</li>
									<li>
										<span class="rsfa-anniversary-deal__plan"><?php esc_html_e( 'Unlimited sites', 'really-simple-featured-audio' ); ?> <em><?php esc_html_e( 'Save 50%', 'really-simple-featured-audio' ); ?></em></span>
										<span class="rsfa-anniversary-deal__amounts"><s>$199</s> <strong>$99</strong></span>
									</li>
								</ul>
								<p class="rsfa-anniversary-deal__note"><?php esc_html_e( 'One-time payment · lifetime updates · no renewals', 'really-simple-featured-audio' ); ?></p>
								<a class="button button-primary" href="<?php echo esc_url( $rsfa_pro_url . '#pricing' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get PRO for $59', 'really-simple-featured-audio' ); ?></a>
								<p class="rsfa-anniversary-deal__secure">🔒 <?php esc_html_e( 'Secure checkout · 14-day money-back guarantee', 'really-simple-featured-audio' ); ?></p>
							</div>
							<div>
								<p class="desc"><strong><?php esc_html_e( 'What PRO adds', 'really-simple-featured-audio' ); ?></strong></p>
								<ul class="rsfa-upgrade-features">
									<li>✅ <strong><?php esc_html_e( 'Priority support', 'really-simple-featured-audio' ); ?></strong> — <?php esc_html_e( 'direct help from the developer', 'really-simple-featured-audio' ); ?></li>
									<li>✅ <strong><?php esc_html_e( 'Full audio analytics', 'really-simple-featured-audio' ); ?></strong> — <?php esc_html_e( 'longer history, listen time, completion, CSV export', 'really-simple-featured-audio' ); ?></li>
									<li>✅ <strong><?php esc_html_e( 'WooCommerce controls', 'really-simple-featured-audio' ); ?></strong> — <?php esc_html_e( 'audio position in the product gallery and its thumbnail', 'really-simple-featured-audio' ); ?></li>
									<li>✅ <strong><?php esc_html_e( 'Player appearance', 'really-simple-featured-audio' ); ?></strong> — <?php esc_html_e( 'accent color and light or dark player', 'really-simple-featured-audio' ); ?></li>
									<li>✅ <strong><?php esc_html_e( 'Premium and custom themes', 'really-simple-featured-audio' ); ?></strong> — <?php esc_html_e( 'more supported, compatibility on request', 'really-simple-featured-audio' ); ?></li>
								</ul>
								<p class="rsfa-upgrade-compare"><a href="<?php echo esc_url( $rsfa_pro_url . '#compare' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Compare free vs PRO →', 'really-simple-featured-audio' ); ?></a></p>
							</div>

							<div class="rsfa-upgrade-founder">
								<p><em>If you like our free plugin, you will absolutely love the PRO version. Thank you for using RSFA again, you are not just any supporter but truly the founders of our small business.</em></p>
								<p><strong>Krishna</strong>, Founder and Lead Developer</p>

								<p><strong>Have questions?</strong> Send them at <a href="mailto:krishna@jetixwp.com">krishna@jetixwp.com</a>, and I will personally get back to you at the earliest :)</p>

							</div>
						</div>
					<?php endif; ?>
					<?php do_action( 'rsfa_extend_settings_sidebar' ); ?>
				</div>
			</div>
	</div>
</div>
