/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Invoker commands', () => {
	test.beforeEach( async ( { admin } ) => {
		await admin.createNewPost();
	} );

	test( 'wires up a button to toggle a details block', async ( {
		editor,
		page,
	} ) => {
		await editor.insertBlock( {
			name: 'core/details',
			attributes: { summary: 'More info' },
			innerBlocks: [
				{
					name: 'core/paragraph',
					attributes: { content: 'Hidden text' },
				},
			],
		} );

		await editor.insertBlock( {
			name: 'core/buttons',
			innerBlocks: [
				{
					name: 'core/button',
					attributes: { text: 'Toggle' },
				},
			],
		} );

		// Wire the button up to the details block via the Commands panel.
		await editor.canvas
			.getByRole( 'document', { name: 'Block: Button' } )
			.click();
		await editor.openDocumentSettingsSidebar();

		const commandsPanel = page.getByRole( 'button', {
			name: 'Commands',
			expanded: true,
		} );
		await expect( commandsPanel ).toBeVisible();

		// The details block is the only controllable block in the post, so it
		// is the first option after "None". Selecting by index rather than by
		// label since the label is the block title, which can vary.
		await page
			.getByRole( 'combobox', { name: 'Block' } )
			.selectOption( { index: 1 } );
		await page
			.getByRole( 'combobox', { name: 'Command' } )
			.selectOption( '--toggle' );

		// The button now holds the command attributes.
		const buttonBlock = ( await editor.getBlocks() ).find(
			( { name }: { name: string } ) => name === 'core/buttons'
		)?.innerBlocks?.[ 0 ];
		expect( buttonBlock?.attributes.command ).toBe( '--toggle' );
		expect( buttonBlock?.attributes.commandFor ).toEqual(
			expect.any( String )
		);

		const postId = await editor.publishPost();
		await page.goto( `/?p=${ postId }` );

		// The rendered markup wires the button to the details element.
		const details = page.locator( 'details.wp-block-details' );
		const button = page.getByRole( 'button', { name: 'Toggle' } );
		await expect( button ).toHaveAttribute( 'command', '--toggle' );
		await expect( button ).toHaveAttribute(
			'commandfor',
			( await details.getAttribute( 'id' ) ) as string
		);

		// Clicking the button toggles the details block.
		await expect( details ).toHaveJSProperty( 'open', false );
		await button.click();
		await expect( details ).toHaveJSProperty( 'open', true );
		await expect(
			page.getByText( 'Hidden text', { exact: true } )
		).toBeVisible();
		await button.click();
		await expect( details ).toHaveJSProperty( 'open', false );
	} );

	test( 'shows no Command select until a block is chosen', async ( {
		editor,
		page,
	} ) => {
		await editor.insertBlock( {
			name: 'core/buttons',
			innerBlocks: [
				{
					name: 'core/button',
					attributes: { text: 'Click me' },
				},
			],
		} );

		await editor.canvas
			.getByRole( 'document', { name: 'Block: Button' } )
			.click();
		await editor.openDocumentSettingsSidebar();

		await expect(
			page.getByRole( 'combobox', { name: 'Block' } )
		).toBeVisible();
		await expect(
			page.getByRole( 'combobox', { name: 'Command' } )
		).toBeHidden();
	} );
} );
