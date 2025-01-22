<?php
/**
 * Collection of functions.
 *
 * @package BlockInvokers
 */

declare(strict_types = 1);

namespace BlockInvokers;

use WP_HTML_Tag_Processor;

function add_commands_to_block_metadata( $metadata ) {
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
	}

	return $metadata;
}

add_filter( 'block_type_metadata', __NAMESPACE__ . '\add_commands_to_block_metadata' );

function add_command_attributes( $block_content, $block ) {
	if ( isset( $block['attrs']['commandId'] ) && '' !== $block['attrs']['commandId'] ) {

		$processor = new WP_HTML_Tag_Processor( $block_content );
		switch ( $block['blockName'] ) {
			case 'core/video':
				if ( $processor->next_tag( 'video' ) ) {
					$processor->set_attribute( 'id', $block['attrs']['commandId'] );
				}

				break;

			case 'core/details':
				if ( $processor->next_tag( 'details' ) ) {
					$processor->set_attribute( 'id', $block['attrs']['commandId'] );
				}

				break;
		}

		return $processor->get_updated_html();
	}

	if ( 'core/button' === $block['blockName'] ) {
		$processor = new WP_HTML_Tag_Processor( $block_content );

		if ( $processor->next_tag( 'button' ) ) {
			$processor->set_attribute( 'commandfor', $block['attrs']['commandFor'] );
			$processor->set_attribute( 'command', $block['attrs']['command'] );
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
