/**
 * Implements the custom invoker commands used by this plugin.
 *
 * Only the popover and dialog commands are part of the HTML spec so far.
 * The `<details>` and media element commands are still an Open UI proposal,
 * see https://open-ui.org/components/future-invokers.explainer/.
 * Until they land, this plugin uses custom (`--`-prefixed) commands and
 * implements their proposed behavior here, based on the `command` events
 * dispatched natively by supporting browsers or by the polyfill otherwise.
 */

/**
 * Handles `command` events for `<details>`, `<video>`, and `<audio>` elements.
 *
 * @param {Event} event Command event with `command` and `source` properties.
 */
export function handleCommand( event ) {
	const target = event.target;

	if ( ! ( target instanceof HTMLElement ) ) {
		return;
	}

	if ( target instanceof HTMLDetailsElement ) {
		switch ( event.command ) {
			case '--toggle':
				target.open = ! target.open;
				break;
			case '--open':
				target.open = true;
				break;
			case '--close':
				target.open = false;
				break;
		}

		return;
	}

	if ( target instanceof HTMLMediaElement ) {
		switch ( event.command ) {
			case '--play-pause':
				if ( target.paused ) {
					// Playback can be denied, e.g. by autoplay policies.
					target.play().catch( () => {} );
				} else {
					target.pause();
				}
				break;
			case '--play':
				if ( target.paused ) {
					target.play().catch( () => {} );
				}
				break;
			case '--pause':
				if ( ! target.paused ) {
					target.pause();
				}
				break;
			case '--toggle-muted':
				target.muted = ! target.muted;
				break;
		}
	}
}

// `command` events do not bubble, but a capturing listener on the document
// still sees them on their way down to the target element.
document.addEventListener( 'command', handleCommand, true );
