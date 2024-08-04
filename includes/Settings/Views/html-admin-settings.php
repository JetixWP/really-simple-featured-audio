<?php
/**
 * Admin View: Settings
 *
 * @package RSFA
 */

namespace RSFA\Settings\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab_exists        = isset( $tabs[ $current_tab ] ) || has_action( 'rsfa_sections_' . $current_tab ) || has_action( 'rsfa_settings_' . $current_tab ) || has_action( 'rsfa_settings_tabs_' . $current_tab );
$current_tab_label = isset( $tabs[ $current_tab ] ) ? $tabs[ $current_tab ] : '';

if ( ! $tab_exists ) {
	wp_safe_redirect( admin_url( 'options-general.php?page=rsfa-settings' ) );
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
				<a href="https://jetixwp.com?utm_campaign=settings-header&utm_source=rsfa-plugin" target="_blank"><img class="brand-logo" src="<?php echo esc_url( RSFA_PLUGIN_URL . 'assets/images/icon-dark.svg' ); ?>" alt="RSFA"></a>
			</div>
		</div>
	</div>
	<div class="rsfa-wrapper">
		<form method="<?php echo esc_attr( apply_filters( 'rsfa_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">

			<div class="nav-content">
				<nav class="nav-tab-wrapper rsfa-nav-tab-wrapper">
					<?php

					foreach ( $tabs as $slug => $label ) {
						echo '<a href="' . esc_html( admin_url( 'options-general.php?page=rsfa-settings&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( $current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
					}

					do_action( 'rsfa_settings_tabs' );

					?>
				</nav>
			</div>
			<div class="tab-content">
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

				<div class="sidebar">
					<?php do_action( 'rsfa_extend_settings_sidebar' ); ?>
				</div>
			</div>
		</form>
	</div>
</div>
