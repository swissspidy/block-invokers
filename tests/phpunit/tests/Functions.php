<?php
/**
 * Tests for the plugin functions.
 *
 * @package BlockInvokers
 */

declare(strict_types = 1);

namespace BlockInvokers\Tests;

use WP_UnitTestCase;

use function BlockInvokers\add_command_attributes;
use function BlockInvokers\add_commands_to_block_metadata;
use function BlockInvokers\register_frontend_assets;

/**
 * Tests for the plugin's functions.
 */
class Test_Functions extends WP_UnitTestCase {
	/**
	 * Starts each test with a clean scripts state and the plugin's
	 * scripts registered, since enqueueing an unregistered handle is
	 * silently ignored.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		unset( $GLOBALS['wp_scripts'], $GLOBALS['wp_script_modules'] );

		register_frontend_assets();
	}

	/**
	 * Resets script registration state after each test.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		unset( $GLOBALS['wp_scripts'], $GLOBALS['wp_script_modules'] );

		parent::tear_down();
	}

	/**
	 * @covers \BlockInvokers\add_commands_to_block_metadata
	 */
	public function test_add_commands_to_block_metadata_button(): void {
		$metadata = add_commands_to_block_metadata( [ 'name' => 'core/button' ] );

		$this->assertArrayHasKey( 'command', $metadata['attributes'] );
		$this->assertArrayHasKey( 'commandFor', $metadata['attributes'] );
		$this->assertSame( [ 'type' => 'string' ], $metadata['attributes']['command'] );
		$this->assertSame( [ 'type' => 'string' ], $metadata['attributes']['commandFor'] );
	}

	/**
	 * @covers \BlockInvokers\add_commands_to_block_metadata
	 */
	public function test_add_commands_to_block_metadata_details(): void {
		$metadata = add_commands_to_block_metadata( [ 'name' => 'core/details' ] );

		$this->assertArrayHasKey( 'commandId', $metadata['attributes'] );
		$this->assertSame(
			[ '--toggle', '--open', '--close' ],
			array_keys( $metadata['supports']['commands'] )
		);
	}

	/**
	 * Data provider for media block types.
	 *
	 * @return array<string, array{string}>
	 */
	public function data_media_blocks(): array {
		return [
			'video' => [ 'core/video' ],
			'audio' => [ 'core/audio' ],
		];
	}

	/**
	 * @dataProvider data_media_blocks
	 * @covers \BlockInvokers\add_commands_to_block_metadata
	 *
	 * @param string $block_name Block name.
	 */
	public function test_add_commands_to_block_metadata_media( string $block_name ): void {
		$metadata = add_commands_to_block_metadata( [ 'name' => $block_name ] );

		$this->assertArrayHasKey( 'commandId', $metadata['attributes'] );
		$this->assertSame(
			[ '--play-pause', '--play', '--pause', '--toggle-muted' ],
			array_keys( $metadata['supports']['commands'] )
		);
	}

	/**
	 * @covers \BlockInvokers\add_commands_to_block_metadata
	 */
	public function test_add_commands_to_block_metadata_image(): void {
		$metadata = add_commands_to_block_metadata( [ 'name' => 'core/image' ] );

		$this->assertArrayHasKey( 'commandId', $metadata['attributes'] );
		$this->assertSame(
			[ '--show-lightbox', '--hide-lightbox' ],
			array_keys( $metadata['supports']['commands'] )
		);
	}

	/**
	 * All commands are custom (`--`-prefixed) or part of the HTML spec,
	 * anything else is silently ignored by browsers and the polyfill.
	 *
	 * @covers \BlockInvokers\add_commands_to_block_metadata
	 */
	public function test_add_commands_to_block_metadata_only_valid_commands(): void {
		$spec_commands = [
			'toggle-popover',
			'show-popover',
			'hide-popover',
			'show-modal',
			'close',
			'request-close',
		];

		foreach ( [ 'core/details', 'core/video', 'core/audio', 'core/image' ] as $block_name ) {
			$metadata = add_commands_to_block_metadata( [ 'name' => $block_name ] );

			foreach ( array_keys( $metadata['supports']['commands'] ) as $command ) {
				$this->assertTrue(
					str_starts_with( $command, '--' ) || in_array( $command, $spec_commands, true ),
					"Command '$command' for $block_name is neither a spec command nor a custom command."
				);
			}
		}
	}

	/**
	 * @covers \BlockInvokers\add_commands_to_block_metadata
	 */
	public function test_add_commands_to_block_metadata_other_blocks_untouched(): void {
		$metadata = [ 'name' => 'core/paragraph' ];

		$this->assertSame( $metadata, add_commands_to_block_metadata( $metadata ) );
	}

	/**
	 * Data provider for blocks whose target element receives an ID.
	 *
	 * @return array<string, array{string, string, string}>
	 */
	public function data_command_target_blocks(): array {
		return [
			'video'   => [
				'core/video',
				'<figure class="wp-block-video"><video controls src="video.mp4"></video></figure>',
				'video',
			],
			'audio'   => [
				'core/audio',
				'<figure class="wp-block-audio"><audio controls src="audio.mp3"></audio></figure>',
				'audio',
			],
			'details' => [
				'core/details',
				'<details class="wp-block-details"><summary>More</summary>Hidden</details>',
				'details',
			],
		];
	}

	/**
	 * @dataProvider data_command_target_blocks
	 * @covers \BlockInvokers\add_command_attributes
	 *
	 * @param string $block_name Block name.
	 * @param string $content    Block content.
	 * @param string $tag        Expected tag receiving the ID.
	 */
	public function test_add_command_attributes_target_blocks( string $block_name, string $content, string $tag ): void {
		$result = add_command_attributes(
			$content,
			[
				'blockName' => $block_name,
				'attrs'     => [ 'commandId' => 'my-command-id' ],
			]
		);

		$this->assertStringContainsString( "<$tag id=\"my-command-id\"", $result );
		$this->assertTrue( wp_script_is( 'block-invokers-view', 'enqueued' ) );
	}

	/**
	 * @covers \BlockInvokers\add_command_attributes
	 */
	public function test_add_command_attributes_image(): void {
		$result = add_command_attributes(
			'<figure class="wp-block-image"><img src="image.jpg" alt=""/></figure>',
			[
				'blockName' => 'core/image',
				'attrs'     => [ 'commandId' => 'my-command-id' ],
			]
		);

		$this->assertStringContainsString( 'id="my-command-id"', $result );
		$this->assertStringContainsString( 'data-wp-on--command="actions.handleCommand"', $result );
	}

	/**
	 * @covers \BlockInvokers\add_command_attributes
	 */
	public function test_add_command_attributes_button(): void {
		$result = add_command_attributes(
			'<div class="wp-block-button"><button type="button" class="wp-block-button__link">Toggle</button></div>',
			[
				'blockName' => 'core/button',
				'attrs'     => [
					'command'    => '--toggle',
					'commandFor' => 'my-command-id',
				],
			]
		);

		$this->assertStringContainsString( 'commandfor="my-command-id"', $result );
		$this->assertStringContainsString( 'command="--toggle"', $result );
		$this->assertTrue( wp_script_is( 'block-invokers-polyfill', 'enqueued' ) );
	}

	/**
	 * @covers \BlockInvokers\add_command_attributes
	 */
	public function test_add_command_attributes_button_without_command(): void {
		$content = '<div class="wp-block-button"><a class="wp-block-button__link">Hello</a></div>';

		$result = add_command_attributes(
			$content,
			[
				'blockName' => 'core/button',
				'attrs'     => [],
			]
		);

		$this->assertSame( $content, $result );
		$this->assertFalse( wp_script_is( 'block-invokers-polyfill', 'enqueued' ) );
	}

	/**
	 * @covers \BlockInvokers\add_command_attributes
	 */
	public function test_add_command_attributes_empty_command_id(): void {
		$content = '<details class="wp-block-details"><summary>More</summary>Hidden</details>';

		$result = add_command_attributes(
			$content,
			[
				'blockName' => 'core/details',
				'attrs'     => [ 'commandId' => '' ],
			]
		);

		$this->assertSame( $content, $result );
		$this->assertFalse( wp_script_is( 'block-invokers-view', 'enqueued' ) );
	}

	/**
	 * @covers \BlockInvokers\add_command_attributes
	 */
	public function test_add_command_attributes_other_blocks_untouched(): void {
		$content = '<p>Hello</p>';

		$result = add_command_attributes(
			$content,
			[
				'blockName' => 'core/paragraph',
				'attrs'     => [],
			]
		);

		$this->assertSame( $content, $result );
	}

	/**
	 * @covers \BlockInvokers\register_frontend_assets
	 */
	public function test_register_frontend_assets(): void {
		register_frontend_assets();

		$this->assertTrue( wp_script_is( 'block-invokers-polyfill', 'registered' ) );
		$this->assertTrue( wp_script_is( 'block-invokers-view', 'registered' ) );
	}

	/**
	 * The whole flow: a rendered post with wired-up button and details blocks
	 * gets the invoker attributes in its markup.
	 *
	 * @covers \BlockInvokers\add_command_attributes
	 */
	public function test_rendered_post_contains_command_attributes(): void {
		$post_content = <<<'HTML'
<!-- wp:details {"commandId":"details-123"} -->
<details class="wp-block-details"><summary>More info</summary><!-- wp:paragraph --><p>Hidden text</p><!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"tagName":"button","command":"--toggle","commandFor":"details-123"} -->
<div class="wp-block-button"><button type="button" class="wp-block-button__link wp-element-button">Toggle</button></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
HTML;

		$rendered = do_blocks( $post_content );

		$this->assertStringContainsString( '<details id="details-123"', $rendered );
		$this->assertStringContainsString( 'commandfor="details-123"', $rendered );
		$this->assertStringContainsString( 'command="--toggle"', $rendered );
	}
}
