/**
 * Main Bulk Actions App Component
 *
 * @package RSFA
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ManageFeaturedAudios from './components/ManageFeaturedAudios';
import Sidebar from './components/Sidebar';

const App = () => {
	const tabs = [
		{
			id: 'manage',
			label: __( 'Manage Featured Audios', 'really-simple-featured-audio' ),
		},
	];

	/**
	 * Get the initial tab from URL hash or default to first tab.
	 *
	 * @return {string} The tab ID.
	 */
	const getTabFromHash = useCallback( () => {
		const hash = window.location.hash.replace( '#', '' );
		const validTabIds = tabs.map( ( tab ) => tab.id );
		return validTabIds.includes( hash ) ? hash : tabs[ 0 ].id;
	}, [] );

	const [ activeTab, setActiveTab ] = useState( getTabFromHash );

	/**
	 * Update URL hash when tab changes.
	 *
	 * @param {string} tabId The tab ID to set.
	 */
	const handleTabChange = ( tabId ) => {
		setActiveTab( tabId );
		window.history.replaceState( null, '', `#${ tabId }` );
	};

	// Listen for hash changes (browser back/forward).
	useEffect( () => {
		const handleHashChange = () => {
			setActiveTab( getTabFromHash() );
		};

		window.addEventListener( 'hashchange', handleHashChange );
		return () =>
			window.removeEventListener( 'hashchange', handleHashChange );
	}, [ getTabFromHash ] );

	return (
		<div className="rsfa-tools-app">
			<div className="rsfa-tabs">
				<nav className="rsfa-tabs-nav">
					{ tabs.map( ( tab ) => (
						<button
							key={ tab.id }
							className={ `rsfa-tab-button ${
								activeTab === tab.id ? 'active' : ''
							}` }
							onClick={ () => handleTabChange( tab.id ) }
						>
							{ tab.label }
						</button>
					) ) }
				</nav>
			</div>

			<div className="rsfa-tab-content">
				<div className="rsfa-content-wrapper">
					<div className="rsfa-main-content">
						{ activeTab === 'manage' && <ManageFeaturedAudios /> }
					</div>
					<Sidebar />
				</div>
			</div>
		</div>
	);
};

export default App;
