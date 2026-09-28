/**
 * Analytics report for Audio Tools.
 *
 * @package RSFA
 */

import { useCallback, useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { applyFilters } from '../hooks';

/**
 * Format a count for display.
 *
 * @param {number} value Count.
 * @return {string}
 */
const formatCount = ( value ) => Number( value || 0 ).toLocaleString();

/**
 * Short date label, parsed as a local calendar day.
 *
 * @param {string} date Y-m-d.
 * @return {string}
 */
const formatDay = ( date ) => {
	const parsed = new Date( `${ date }T00:00:00` );

	if ( Number.isNaN( parsed.getTime() ) ) {
		return date;
	}

	return parsed.toLocaleDateString( undefined, { month: 'short', day: 'numeric' } );
};

/**
 * Line chart for views and plays.
 *
 * @param {Object}   props        Props.
 * @param {Array}    props.series Daily points.
 * @return {JSX.Element}
 */
const TrendChart = ( { series } ) => {
	const width = 640;
	const height = 180;
	const pad = 16;
	const max = Math.max(
		1,
		...series.map( ( point ) => Math.max( point.views, point.plays ) )
	);

	const xFor = ( index ) => {
		if ( series.length < 2 ) {
			return pad;
		}

		return pad + ( index / ( series.length - 1 ) ) * ( width - pad * 2 );
	};

	const yFor = ( value ) => height - pad - ( value / max ) * ( height - pad * 2 );

	const pathFor = ( key ) => series
		.map( ( point, index ) => `${ index === 0 ? 'M' : 'L' } ${ xFor( index ) } ${ yFor( point[ key ] ) }` )
		.join( ' ' );

	const tickStep = Math.max( 1, Math.ceil( series.length / 6 ) );

	return (
		<figure className="rsfa-analytics-chart">
			<svg viewBox={ `0 0 ${ width } ${ height }` } role="img" aria-label={ __( 'Views and plays over time', 'really-simple-featured-audio' ) }>
				<line x1={ pad } y1={ height - pad } x2={ width - pad } y2={ height - pad } className="rsfa-analytics-axis" />
				<path d={ pathFor( 'views' ) } className="rsfa-analytics-line rsfa-analytics-line-views" />
				<path d={ pathFor( 'plays' ) } className="rsfa-analytics-line rsfa-analytics-line-plays" />
			</svg>
			<div className="rsfa-analytics-ticks">
				{ series.map( ( point, index ) => (
					index % tickStep === 0 ? <span key={ point.date }>{ formatDay( point.date ) }</span> : null
				) ) }
			</div>
			<ul className="rsfa-analytics-legend">
				<li><span className="rsfa-analytics-swatch rsfa-analytics-swatch-views" />{ __( 'Views', 'really-simple-featured-audio' ) }</li>
				<li><span className="rsfa-analytics-swatch rsfa-analytics-swatch-plays" />{ __( 'Plays', 'really-simple-featured-audio' ) }</li>
			</ul>
		</figure>
	);
};

/**
 * Horizontal bars for one breakdown.
 *
 * @param {Object} props       Props.
 * @param {string} props.title Heading.
 * @param {Array}  props.rows  Rows with label and views.
 * @return {JSX.Element}
 */
const Breakdown = ( { title, rows } ) => {
	const max = Math.max( 1, ...rows.map( ( row ) => row.views ) );

	return (
		<section className="rsfa-analytics-breakdown">
			<h3>{ title }</h3>
			{ rows.length === 0 && <p className="rsfa-analytics-muted">{ __( 'Nothing in this period yet.', 'really-simple-featured-audio' ) }</p> }
			<ul>
				{ rows.map( ( row ) => (
					<li key={ row.key }>
						<span className="rsfa-analytics-bar-label">{ row.label }</span>
						<span className="rsfa-analytics-bar-track">
							<span className="rsfa-analytics-bar-fill" style={ { width: `${ ( row.views / max ) * 100 }%` } } />
						</span>
						<span className="rsfa-analytics-bar-value">{ formatCount( row.views ) }</span>
					</li>
				) ) }
			</ul>
		</section>
	);
};

/**
 * Analytics tab.
 *
 * @return {JSX.Element}
 */
const AnalyticsReport = () => {
	const [ report, setReport ] = useState( null );
	const [ error, setError ] = useState( '' );
	const [ range, setRange ] = useState( { from: '', to: '' } );
	const [ compare, setCompare ] = useState( null );
	const [ hookTick, setHookTick ] = useState( 0 );

	useEffect( () => {
		const bump = () => setHookTick( ( value ) => value + 1 );

		window.addEventListener( 'rsfa-tools-hooks-ready', bump );

		return () => window.removeEventListener( 'rsfa-tools-hooks-ready', bump );
	}, [] );

	const load = useCallback( ( nextRange ) => {
		let path = '/rsfa/v1/analytics/report';

		if ( nextRange && nextRange.from && nextRange.to ) {
			path += `?from=${ encodeURIComponent( nextRange.from ) }&to=${ encodeURIComponent( nextRange.to ) }`;
		}

		apiFetch( { path } )
			.then( ( data ) => {
				setReport( data );
				setError( '' );
			} )
			.catch( () => {
				setError( __( 'The report could not be loaded.', 'really-simple-featured-audio' ) );
			} );
	}, [] );

	useEffect( () => {
		load( range );
	}, [ range, load ] );

	if ( error ) {
		return <p className="rsfa-analytics-error">{ error }</p>;
	}

	if ( ! report ) {
		return (
			<div className="rsfa-loading">
				<span className="spinner is-active" />
				<span>{ __( 'Loading report…', 'really-simple-featured-audio' ) }</span>
			</div>
		);
	}

	const summary = report.summary || {};
	const days = Number.isFinite( Number( report.retentionDays ) ) ? Number( report.retentionDays ) : 14;
	const period = 0 === days
		? __( 'All time', 'really-simple-featured-audio' )
		: sprintf(
			/* translators: %d: number of days. */
			__( 'Last %d days', 'really-simple-featured-audio' ),
			days
		);

	const baseCards = [
		{ key: 'audios', label: __( 'Audios tracked', 'really-simple-featured-audio' ), value: formatCount( summary.audios ) },
		{ key: 'views', label: __( 'Views', 'really-simple-featured-audio' ), value: formatCount( summary.views ) },
		{ key: 'plays', label: __( 'Plays', 'really-simple-featured-audio' ), value: formatCount( summary.plays ) },
		{ key: 'playRate', label: __( 'Play rate', 'really-simple-featured-audio' ), value: `${ summary.playRate || 0 }%` },
	];
	const cards = applyFilters( 'rsfa_analytics_cards', baseCards, { report, compare } );
	const rangeControl = applyFilters( 'rsfa_analytics_range_control', null, { report, setRange, setCompare } );
	const columns = applyFilters( 'rsfa_analytics_table_columns', [
		{ key: 'title', label: __( 'Audio', 'really-simple-featured-audio' ) },
				{ key: 'provider', label: __( 'Source', 'really-simple-featured-audio' ) },
		{ key: 'views', label: __( 'Views', 'really-simple-featured-audio' ) },
		{ key: 'plays', label: __( 'Plays', 'really-simple-featured-audio' ) },
		{ key: 'playRate', label: __( 'Play rate', 'really-simple-featured-audio' ) },
	], report );
	const proUrl = window.rsfaTools?.upgradeUrl || '#';
	const showPromo = ! report.isPro;
	const promoColumns = showPromo ? [
		{ key: 'promo-watch', label: __( 'Listen time', 'really-simple-featured-audio' ), promo: true },
		{ key: 'promo-completion', label: __( 'Completion', 'really-simple-featured-audio' ), promo: true },
	] : [];
	const tableColumns = columns.concat( promoColumns );

	void hookTick;

	const proBadge = (
		<a className="rsfa-analytics-pro-tag" href={ proUrl } target="_blank" rel="noopener noreferrer">
			{ __( 'Pro', 'really-simple-featured-audio' ) }
		</a>
	);

	return (
		<div className="rsfa-analytics">
			<header className="rsfa-analytics-header">
				<div className="rsfa-analytics-heading-row">
					<h2>{ __( 'Audio Analytics', 'really-simple-featured-audio' ) }</h2>
					{ rangeControl }
					{ showPromo && (
						<div className="rsfa-analytics-range is-promo">
							{ proBadge }
							<select disabled aria-label={ __( 'Date range', 'really-simple-featured-audio' ) }>
								<option>{ __( 'Range', 'really-simple-featured-audio' ) }</option>
							</select>
							<input type="date" disabled aria-label={ __( 'From', 'really-simple-featured-audio' ) } />
							<input type="date" disabled aria-label={ __( 'To', 'really-simple-featured-audio' ) } />
							<label>
								<input type="checkbox" disabled />
								{ ' ' }
								{ __( 'Compare', 'really-simple-featured-audio' ) }
							</label>
							<button type="button" className="button" disabled>{ __( 'Export CSV', 'really-simple-featured-audio' ) }</button>
						</div>
					) }
				</div>
					<p className="rsfa-analytics-muted">{ __( 'A view is a player actually on screen. A play is someone starting the audio. Hover preview does not count.', 'really-simple-featured-audio' ) }</p>
					{ ! report.isPro && (
						<p className="rsfa-analytics-muted">
							{ __( 'Recording only the last 14 days of history.', 'really-simple-featured-audio' ) }{ ' ' }
							<a href={ report.settingsUrl }>{ __( 'Analytics settings', 'really-simple-featured-audio' ) }</a>
						</p>
					) }
					{ report.isPro && ! rangeControl && (
						<p className="rsfa-analytics-muted">{ period }</p>
					) }
					{ ! report.enabled && (
						<p className="rsfa-analytics-notice">
							{ __( 'Analytics is turned off. New visits are not being counted.', 'really-simple-featured-audio' ) }{ ' ' }
							<a href={ report.settingsUrl }>{ __( 'Turn it on', 'really-simple-featured-audio' ) }</a>
						</p>
					) }
			</header>

			<ul className="rsfa-analytics-cards">
				{ cards.map( ( card ) => (
					<li key={ card.key || card.label }>
						<span className="rsfa-analytics-card-value">{ card.value }</span>
						<span className="rsfa-analytics-card-label">{ card.label }</span>
					</li>
				) ) }
				{ showPromo && (
					<>
						<li className="is-promo">
							<span className="rsfa-analytics-card-value">—</span>
							<span className="rsfa-analytics-card-label">{ __( 'Average listen', 'really-simple-featured-audio' ) } { proBadge }</span>
						</li>
						<li className="is-promo">
							<span className="rsfa-analytics-card-value">—</span>
							<span className="rsfa-analytics-card-label">{ __( 'Completion', 'really-simple-featured-audio' ) } { proBadge }</span>
						</li>
					</>
				) }
			</ul>

			<TrendChart series={ report.series || [] } />

			<div className="rsfa-analytics-splits">
				<Breakdown title={ __( 'Where audios were shown', 'really-simple-featured-audio' ) } rows={ report.surfaces || [] } />
				<Breakdown title={ __( 'Audio source', 'really-simple-featured-audio' ) } rows={ report.providers || [] } />
			</div>

			<section className="rsfa-analytics-table-wrap">
				<h3>{ __( 'Audios', 'really-simple-featured-audio' ) }</h3>
				<table className="rsfa-analytics-table">
					<thead>
						<tr>
							{ tableColumns.map( ( column ) => (
								<th key={ column.key }>
									{ column.label }
									{ column.promo && <> { proBadge }</> }
								</th>
							) ) }
						</tr>
					</thead>
					<tbody>
						{ ( report.audios || [] ).length === 0 && (
							<tr>
								<td colSpan={ tableColumns.length }>{ __( 'No audios yet. Add a featured audio and this list will fill in.', 'really-simple-featured-audio' ) }</td>
							</tr>
						) }
						{ ( report.audios || [] ).map( ( audio ) => (
							<tr key={ audio.id } className={ audio.status === 'inactive' ? 'is-inactive' : '' }>
								{ tableColumns.map( ( column ) => (
									<td key={ column.key } className={ column.promo ? 'is-promo' : undefined }>
										{ column.promo && '—' }
										{ ! column.promo && column.key === 'title' && (
											<>
												{ audio.editUrl ? <a href={ audio.editUrl }>{ audio.title }</a> : audio.title }
												{ audio.status === 'inactive' && <span className="rsfa-analytics-pill">{ __( 'Removed', 'really-simple-featured-audio' ) }</span> }
											</>
										) }
										{ ! column.promo && column.key === 'views' && formatCount( audio.views ) }
										{ ! column.promo && column.key === 'plays' && formatCount( audio.plays ) }
										{ ! column.promo && column.key === 'playRate' && `${ audio.playRate || 0 }%` }
										{ ! column.promo && column.key !== 'title' && column.key !== 'views' && column.key !== 'plays' && column.key !== 'playRate' && (
											column.render ? column.render( audio ) : audio[ column.key ]
										) }
									</td>
								) ) }
							</tr>
						) ) }
					</tbody>
				</table>
			</section>

			{ showPromo && (
				<>
					<section className="rsfa-analytics-funnel is-promo">
						<h3>{ __( 'How far people listened', 'really-simple-featured-audio' ) } { proBadge }</h3>
						<p className="rsfa-analytics-muted">{ __( 'Share of plays that reached each point.', 'really-simple-featured-audio' ) }</p>
						<ol>
							{ [ __( 'Played', 'really-simple-featured-audio' ), __( 'Reached 25%', 'really-simple-featured-audio' ), __( 'Reached 50%', 'really-simple-featured-audio' ), __( 'Reached 75%', 'really-simple-featured-audio' ), __( 'Completed', 'really-simple-featured-audio' ) ].map( ( label ) => (
								<li key={ label }>
									<span className="rsfa-analytics-funnel-label">{ label }</span>
									<span className="rsfa-analytics-funnel-track"><span style={ { width: '0%' } } /></span>
									<span className="rsfa-analytics-funnel-stat"><strong>—</strong></span>
								</li>
							) ) }
						</ol>
					</section>
					<div className="rsfa-analytics-splits rsfa-analytics-promo-splits">
						{ [ __( 'Countries', 'really-simple-featured-audio' ), __( 'Devices', 'really-simple-featured-audio' ), __( 'Referring sites', 'really-simple-featured-audio' ) ].map( ( title ) => (
							<section className="rsfa-analytics-breakdown is-promo" key={ title }>
								<h3>{ title } { proBadge }</h3>
								<p className="rsfa-analytics-muted">{ __( 'Available in Pro.', 'really-simple-featured-audio' ) }</p>
							</section>
						) ) }
					</div>
				</>
			) }

			{ ( report.sections || [] ).map( ( section, index ) => {
				if ( ! section || ! section.title ) {
					return null;
				}

				if ( section.type === 'funnel' ) {
					const steps = Array.isArray( section.rows ) ? section.rows : [];

					return (
						<section className="rsfa-analytics-funnel" key={ `${ section.title }-${ index }` }>
							<h3>{ section.title }</h3>
							{ section.text ? <p className="rsfa-analytics-muted">{ section.text }</p> : null }
							{ steps.length === 0 && <p className="rsfa-analytics-muted">{ __( 'Nothing in this period yet.', 'really-simple-featured-audio' ) }</p> }
							<ol>
								{ steps.map( ( step ) => (
									<li key={ step.label }>
										<span className="rsfa-analytics-funnel-label">{ step.label }</span>
										<span className="rsfa-analytics-funnel-track">
											<span style={ { width: `${ Math.max( 0, Math.min( 100, Number( step.share ) || 0 ) ) }%` } } />
										</span>
										<span className="rsfa-analytics-funnel-stat">
											<strong>{ formatCount( step.count ) }</strong>
											<span>{ `${ Number( step.share ) || 0 }%` }</span>
										</span>
									</li>
								) ) }
							</ol>
						</section>
					);
				}

				return (
					<section className="rsfa-analytics-breakdown" key={ `${ section.title }-${ index }` }>
						<h3>{ section.title }</h3>
						{ section.text ? <p>{ section.text }</p> : null }
						{ Array.isArray( section.rows ) && section.rows.length > 0 && (
							<ul>
								{ section.rows.map( ( row ) => (
									<li key={ row.label }>
										<span className="rsfa-analytics-bar-label">{ row.label }</span>
										<span className="rsfa-analytics-bar-value">{ row.value }</span>
									</li>
								) ) }
							</ul>
						) }
					</section>
				);
			} ) }
		</div>
	);
};

export default AnalyticsReport;
