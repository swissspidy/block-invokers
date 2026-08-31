/**
 * Internal dependencies
 */
import { handleCommand } from '../view';

/**
 * Minimal stand-in for the native CommandEvent, which jsdom does not implement.
 */
class CommandEvent extends Event {
	constructor( type, { command = '', source = null, ...init } = {} ) {
		super( type, init );
		this.command = command;
		this.source = source;
	}
}

/**
 * Dispatches a `command` event on an element, like a browser or the polyfill
 * would when a button with `command`/`commandfor` attributes is clicked.
 *
 * @param {HTMLElement} target  Element to dispatch the event on.
 * @param {string}      command Command value.
 */
function dispatchCommand( target, command ) {
	target.dispatchEvent(
		new CommandEvent( 'command', { command, cancelable: true } )
	);
}

describe( 'handleCommand', () => {
	afterEach( () => {
		document.body.innerHTML = '';
	} );

	describe( 'details', () => {
		/** @type {HTMLDetailsElement} */
		let details;

		beforeEach( () => {
			details = document.createElement( 'details' );
			document.body.append( details );
		} );

		it( 'toggles a closed <details> open via --toggle', () => {
			dispatchCommand( details, '--toggle' );
			expect( details.open ).toBe( true );
		} );

		it( 'toggles an open <details> closed via --toggle', () => {
			details.open = true;
			dispatchCommand( details, '--toggle' );
			expect( details.open ).toBe( false );
		} );

		it( 'opens a closed <details> via --open', () => {
			dispatchCommand( details, '--open' );
			expect( details.open ).toBe( true );
		} );

		it( 'keeps an open <details> open via --open', () => {
			details.open = true;
			dispatchCommand( details, '--open' );
			expect( details.open ).toBe( true );
		} );

		it( 'closes an open <details> via --close', () => {
			details.open = true;
			dispatchCommand( details, '--close' );
			expect( details.open ).toBe( false );
		} );

		it( 'ignores unknown commands', () => {
			dispatchCommand( details, '--unknown' );
			expect( details.open ).toBe( false );
		} );
	} );

	describe.each( [ [ 'video' ], [ 'audio' ] ] )( '%s', ( tagName ) => {
		/** @type {HTMLMediaElement} */
		let media;

		/**
		 * Puts the media element into a playing or paused state, with mocked
		 * play/pause methods since jsdom does not implement media playback.
		 *
		 * @param {boolean} paused Whether the element should report as paused.
		 */
		function mockPlayback( paused ) {
			Object.defineProperty( media, 'paused', {
				value: paused,
				configurable: true,
			} );
			media.play = jest.fn( () => Promise.resolve() );
			media.pause = jest.fn();
		}

		beforeEach( () => {
			media = document.createElement( tagName );
			document.body.append( media );
		} );

		it( 'plays a paused element via --play-pause', () => {
			mockPlayback( true );
			dispatchCommand( media, '--play-pause' );
			expect( media.play ).toHaveBeenCalledTimes( 1 );
			expect( media.pause ).not.toHaveBeenCalled();
		} );

		it( 'pauses a playing element via --play-pause', () => {
			mockPlayback( false );
			dispatchCommand( media, '--play-pause' );
			expect( media.pause ).toHaveBeenCalledTimes( 1 );
			expect( media.play ).not.toHaveBeenCalled();
		} );

		it( 'plays a paused element via --play', () => {
			mockPlayback( true );
			dispatchCommand( media, '--play' );
			expect( media.play ).toHaveBeenCalledTimes( 1 );
		} );

		it( 'does nothing for --play when already playing', () => {
			mockPlayback( false );
			dispatchCommand( media, '--play' );
			expect( media.play ).not.toHaveBeenCalled();
		} );

		it( 'pauses a playing element via --pause', () => {
			mockPlayback( false );
			dispatchCommand( media, '--pause' );
			expect( media.pause ).toHaveBeenCalledTimes( 1 );
		} );

		it( 'does nothing for --pause when already paused', () => {
			mockPlayback( true );
			dispatchCommand( media, '--pause' );
			expect( media.pause ).not.toHaveBeenCalled();
		} );

		it( 'toggles the muted state via --toggle-muted', () => {
			mockPlayback( true );
			expect( media.muted ).toBe( false );
			dispatchCommand( media, '--toggle-muted' );
			expect( media.muted ).toBe( true );
			dispatchCommand( media, '--toggle-muted' );
			expect( media.muted ).toBe( false );
		} );

		it( 'swallows play() rejections, e.g. from autoplay policies', async () => {
			mockPlayback( true );
			media.play = jest.fn( () =>
				Promise.reject( new Error( 'NotAllowedError' ) )
			);
			dispatchCommand( media, '--play' );
			// Flushes the rejection; the test fails on an unhandled one.
			await new Promise( ( r ) => setTimeout( r, 0 ) );
			expect( media.play ).toHaveBeenCalledTimes( 1 );
		} );
	} );

	it( 'ignores events on non-HTML targets', () => {
		expect( () =>
			handleCommand( { target: null, command: '--toggle' } )
		).not.toThrow();
	} );

	it( 'ignores commands on unrelated elements', () => {
		const div = document.createElement( 'div' );
		document.body.append( div );
		expect( () => dispatchCommand( div, '--toggle' ) ).not.toThrow();
	} );
} );
