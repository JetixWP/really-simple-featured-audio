<?php
/**
 * Admin View: Bulk Actions
 *
 * @package RSFA
 */

namespace RSFA\Tools\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap rsfa rsfa-tools rsfa-tools-page">
	<div class="plugin-header">
		<div class="plugin-header-wrap">
			<div class="plugin-info">
				<h1 class="menu-title"><?php esc_html_e( 'Really Simple Featured Audio → Tools', 'really-simple-featured-audio' ); ?></h1>
				<?php do_action( 'rsfa_extend_plugin_header' ); ?>
			</div>

			<div class="brand-info">
				<a href="https://jetixwp.com?utm_campaign=settings-header&utm_source=rsfa-plugin" target="_blank"><img class="brand-logo" src="<?php echo esc_url( RSFA_PLUGIN_URL . 'assets/images/jwp-icon-dark.svg' ); ?>" alt="RSFA"></a>
			</div>
		</div>
	</div>
	<div id="rsfa-tools-app"></div>
</div>
