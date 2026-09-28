/**
 * Sidebar Component
 *
 * @package RSFA
 */

import { __ } from '@wordpress/i18n';

const Sidebar = () => {
	const isPro = window.rsfaTools?.isPro || false;
	const upgradeUrl = window.rsfaTools?.upgradeUrl || 'https://jetixwp.com/plugins/really-simple-featured-audio/#pricing';
	const compareUrl = upgradeUrl.replace( /#.*$/, '' ) + '#compare';

	return (
		<div className="rsfa-sidebar">
			<div className="rsfa-sidebar-panel">
				<h3 className="rsfa-sidebar-title">
					{ __( '🙋‍♂️ Important Note', 'really-simple-featured-audio' ) }
				</h3>
				<p>{ __( "If Featured Audios are not working with your theme, try selecting a supported", 'really-simple-featured-audio' ) } <a href={ `${ window.rsfaTools?.settingsUrl || '#' }` }>{ __( "Theme Compatibility Engine", "really-simple-featured-audio" ) }</a> { __( "in Settings.", "really-simple-featured-audio" ) }</p>

				<p>{ __( "If your theme is not listed and the issue persists, submit a request on our GitHub repository. Please note that PRO subscribers receive priority support over GitHub requests, which supports the continuous development of the plugin.", "really-simple-featured-audio" ) }</p>

				<div className="rsfa-sidebar-actions">
					<a className="button button-primary" href={ `${ window.rsfaTools?.settingsUrl || '#' }` }>{ __( 'Go to Settings', 'really-simple-featured-audio' ) }</a>
					<a className="button button-secondary" href="https://github.com/JetixWP/really-simple-featured-audio/issues" target="_blank" rel="noopener noreferrer">{ __( 'File a Request', 'really-simple-featured-audio' ) }</a>
				</div>
			</div>

			{ ! isPro && (
				<div className="rsfa-sidebar-panel rsfa-upgrade-banner">
					<h3 className="rsfa-upgrade-title">
						{ __( '🚀 Ready to go beyond?', 'really-simple-featured-audio' ) }
					</h3>
					<p className="rsfa-upgrade-description">
						{ __( 'RSFA PRO adds deeper WooCommerce control, full audio analytics, player styling, wider theme support, and direct help from the developer.', 'really-simple-featured-audio' ) }
					</p>
					<ul className="rsfa-upgrade-features">
						<li>
							<strong>{ __( 'Full audio analytics', 'really-simple-featured-audio' ) }</strong>
							<span>{ __( 'History beyond 14 days, listen time, completion, CSV export', 'really-simple-featured-audio' ) }</span>
						</li>
						<li>
							<strong>{ __( 'WooCommerce controls', 'really-simple-featured-audio' ) }</strong>
							<span>{ __( 'Gallery order and default gallery thumbnail', 'really-simple-featured-audio' ) }</span>
						</li>
						<li>
							<strong>{ __( 'Play on hover extras', 'really-simple-featured-audio' ) }</strong>
							<span>{ __( 'Screen sizes, delay, accessibility and custom selectors', 'really-simple-featured-audio' ) }</span>
						</li>
						<li>
							<strong>{ __( 'Player appearance', 'really-simple-featured-audio' ) }</strong>
							<span>{ __( 'Accent color and light, dark or automatic theme', 'really-simple-featured-audio' ) }</span>
						</li>
						<li>
							<strong>{ __( 'Premium and custom themes', 'really-simple-featured-audio' ) }</strong>
							<span>{ __( 'More supported, compatibility on request', 'really-simple-featured-audio' ) }</span>
						</li>
						<li>
							<strong>{ __( 'Priority support', 'really-simple-featured-audio' ) }</strong>
							<span>{ __( 'Direct help from the developer', 'really-simple-featured-audio' ) }</span>
						</li>
					</ul>
					<a
						href={ upgradeUrl }
						className="button button-primary rsfa-upgrade-button"
						target="_blank"
						rel="noopener noreferrer"
					>
						{ __( 'See PRO plans', 'really-simple-featured-audio' ) }
					</a>
					<p className="rsfa-upgrade-trust">
						{ __( 'One-time payment · lifetime updates · 14-day money-back guarantee', 'really-simple-featured-audio' ) }
					</p>
					<p className="rsfa-upgrade-compare">
						<a href={ compareUrl } target="_blank" rel="noopener noreferrer">{ __( 'Compare free vs PRO →', 'really-simple-featured-audio' ) }</a>
					</p>
				</div>
			) }

			<div className="rsfa-sidebar-panel">
				<h3 className="rsfa-sidebar-title">
					{ __( 'Quick Tips', 'really-simple-featured-audio' ) }
				</h3>
				<ul className="rsfa-sidebar-tips">
					<li>{ __( 'Click on a thumbnail to set or change the featured image.', 'really-simple-featured-audio' ) }</li>
					<li>{ __( 'Use the audio type dropdown to switch between self-hosted audio and audio links.', 'really-simple-featured-audio' ) }</li>
					<li>{ __( 'Set a cover image to show artwork in the player.', 'really-simple-featured-audio' ) }</li>
					<li>{ __( 'Search for posts by title using the search field above.', 'really-simple-featured-audio' ) }</li>
				</ul>
			</div>

			<div className="rsfa-sidebar-panel">
				<h3 className="rsfa-sidebar-title">
					{ __( 'Keyboard Shortcuts', 'really-simple-featured-audio' ) }
				</h3>
				<ul className="rsfa-sidebar-shortcuts">
					<li>
						<kbd>Enter</kbd>
						<span>{ __( 'Submit search', 'really-simple-featured-audio' ) }</span>
					</li>
					<li>
						<kbd>Esc</kbd>
						<span>{ __( 'Close media modal', 'really-simple-featured-audio' ) }</span>
					</li>
				</ul>
			</div>
		</div>
	);
};

export default Sidebar;
