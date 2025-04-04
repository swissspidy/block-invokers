<?php
/**
 * Collection of functions.
 *
 * @package BlockInvokers
 */

declare(strict_types = 1);

namespace BlockInvokers;

use WP_HTML_Tag_Processor;

/**
 * Filters block metadata to add command information.
 *
 * @param array $metadata Block metadata.
 * @return array Filtered block metadata.
 */
function add_commands_to_block_metadata( array $metadata ): array {
	switch ( $metadata['name'] ) {
		case 'core/button':
			$metadata['attributes']['command']    = [
				'type' => 'string',
			];
			$metadata['attributes']['commandFor'] = [
				'type' => 'string',
			];
			break;
		case 'core/details':
			$metadata['supports']['commands']    = [
				'toggle' => [
					'label'       => __( 'Toggle', 'block-invokers' ),
					'description' => __( 'If the <details> is open, then close it, otherwise open it.', 'block-invokers' ),
				],
				'open'   => [
					'label'       => __( 'Open', 'block-invokers' ),
					'description' => __( 'If the <details> is not open, then open it.', 'block-invokers' ),
				],
				'close'  => [
					'label'       => __( 'Close', 'block-invokers' ),
					'description' => __( 'If the <details> is open, then close it.', 'block-invokers' ),
				],
			];
			$metadata['attributes']['commandId'] = [
				'type' => 'string',
			];
			break;

		case 'core/video':
			$metadata['supports']['commands']    = [
				'play-pause'   => [
					'label'       => __( 'Play/Pause', 'block-invokers' ),
					'description' => __( 'If the video is not playing, plays the video. Otherwise pauses it.', 'block-invokers' ),
				],
				'play'         => [
					'label'       => __( 'Play', 'block-invokers' ),
					'description' => __( 'If the video is playing, pause the video.', 'block-invokers' ),
				],
				'pause'        => [
					'label'       => __( 'Pause', 'block-invokers' ),
					'description' => __( 'If the video is not playing, play the video.', 'block-invokers' ),
				],
				'toggle-muted' => [
					'label'       => __( 'Toggle Muted', 'block-invokers' ),
					'description' => __( 'If the video is muted, it unmutes the video, otherwise it mutes it.', 'block-invokers' ),
				],
			];
			$metadata['attributes']['commandId'] = [
				'type' => 'string',
			];
			break;

		case 'core/audio':
			$metadata['supports']['commands']    = [
				'play-pause'   => [
					'label'       => __( 'Play/Pause', 'block-invokers' ),
					'description' => __( 'If the audio is not playing, plays the audio. Otherwise pauses it.', 'block-invokers' ),
				],
				'play'         => [
					'label'       => __( 'Play', 'block-invokers' ),
					'description' => __( 'If the audio is playing, pause the audio.', 'block-invokers' ),
				],
				'pause'        => [
					'label'       => __( 'Pause', 'block-invokers' ),
					'description' => __( 'If the audio is not playing, play the audio.', 'block-invokers' ),
				],
				'toggle-muted' => [
					'label'       => __( 'Toggle Muted', 'block-invokers' ),
					'description' => __( 'If the audio is muted, it unmutes the audio, otherwise it mutes it.', 'block-invokers' ),
				],
			];
			$metadata['attributes']['commandId'] = [
				'type' => 'string',
			];
			break;

		case 'core/image':
			$metadata['supports']['commands']    = [
				'--show-lightbox' => [
					'label'       => __( 'Show Lightbox', 'block-invokers' ),
					'description' => __( 'If the lightbox is not visible, show the lightbox', 'block-invokers' ),
				],
				'--hide-lightbox' => [
					'label'       => __( 'Hide Lightbox', 'block-invokers' ),
					'description' => __( 'If the lightbox is visible, hide the lightbox.', 'block-invokers' ),
				],
			];
			$metadata['attributes']['commandId'] = [
				'type' => 'string',
			];
			break;
	}

	return $metadata;
}

add_filter( 'block_type_metadata', __NAMESPACE__ . '\add_commands_to_block_metadata' );

/**
 * Filters rendered block markup to add necessary attributes for invokers.
 *
 * @param string $block_content Rendered block markup.
 * @param array  $block Block metadata.
 * @return string Filtered block markup.
 */
function add_command_attributes( $block_content, $block ) {
	if ( isset( $block['attrs']['commandId'] ) && '' !== $block['attrs']['commandId'] ) {

		$processor = new WP_HTML_Tag_Processor( $block_content );
		switch ( $block['blockName'] ) {
			case 'core/video':
				if ( $processor->next_tag( 'video' ) ) {
					$processor->set_attribute( 'id', $block['attrs']['commandId'] );
				}

				break;

			case 'core/audio':
				if ( $processor->next_tag( 'audio' ) ) {
					$processor->set_attribute( 'id', $block['attrs']['commandId'] );
				}

				break;

			case 'core/details':
				if ( $processor->next_tag( 'details' ) ) {
					$processor->set_attribute( 'id', $block['attrs']['commandId'] );
				}

				break;

			case 'core/image':
				if ( $processor->next_tag( 'img' ) ) {
					$processor->set_attribute( 'id', $block['attrs']['commandId'] );
					$processor->set_attribute( 'data-wp-on--command', 'actions.handleCommand' );
				}

				break;
		}

		return $processor->get_updated_html();
	}

	if ( 'core/button' === $block['blockName'] && isset( $block['attrs']['commandFor'], $block['attrs']['command'] ) && '' !== $block['attrs']['commandFor'] && '' !== $block['attrs']['command'] ) {
		$processor = new WP_HTML_Tag_Processor( $block_content );

		if ( $processor->next_tag( 'button' ) ) {
			$processor->set_attribute( 'commandfor', $block['attrs']['commandFor'] );
			$processor->set_attribute( 'command', $block['attrs']['command'] );

			wp_enqueue_script( 'block-invokers-polyfill' );
		}

		return $processor->get_updated_html();
	}

	return $block_content;
}

add_filter( 'render_block', __NAMESPACE__ . '\add_command_attributes', 10, 2 );

/**
 * Enqueues scripts for the block editor.
 */
function enqueue_block_editor_assets(): void {
	$asset_file = dirname( __DIR__ ) . '/build/editor.asset.php';
	$asset      = is_readable( $asset_file ) ? require $asset_file : [];

	$asset['dependencies'] = $asset['dependencies'] ?? [];
	$asset['version']      = $asset['version'] ?? '';

	wp_enqueue_script(
		'block-invokers-editor',
		plugins_url( 'build/editor.js', __DIR__ ),
		$asset['dependencies'],
		$asset['version'],
		array(
			'strategy' => 'defer',
		)
	);

	wp_set_script_translations( 'block-invokers-editor', 'block-invokers' );

	wp_enqueue_style(
		'block-invokers-editor',
		plugins_url( 'build/style-editor.css', __DIR__ ),
		array( 'wp-components' ),
		$asset['version']
	);

	wp_style_add_data( 'block-invokers-editor', 'rtl', 'replace' );
}

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );

/**
 * Registers assets for the frontend.
 */
function register_frontend_assets() {
	wp_deregister_script_module( '@wordpress/block-library/image/view' );

	wp_register_script_module(
		'@wordpress/block-library/image/view',
		plugins_url( 'script-modules/image-view.js', __DIR__ ),
		array( '@wordpress/interactivity' ),
		filemtime( dirname( __DIR__ ) . '/script-modules/image-view.js' )
	);

	$asset_file = dirname( __DIR__ ) . '/build/polyfill.asset.php';
	$asset      = is_readable( $asset_file ) ? require $asset_file : [];

	$asset['dependencies'] = $asset['dependencies'] ?? [];
	$asset['version']      = $asset['version'] ?? '';

	wp_register_script(
		'block-invokers-polyfill',
		plugins_url( 'build/polyfill.js', __DIR__ ),
		$asset['dependencies'],
		$asset['version'],
		array(
			'strategy' => 'async',
		)
	);
}

add_action( 'init', __NAMESPACE__ . '\register_frontend_assets', PHP_INT_MAX );
