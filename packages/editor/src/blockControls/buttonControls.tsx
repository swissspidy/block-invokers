import { v4 as uuidv4 } from 'uuid';

import { __ } from '@wordpress/i18n';
import { store as blocksStore } from '@wordpress/blocks';
import {
	InspectorControls,
	store as blockEditorStore,
	BlockTitle,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { link } from '@wordpress/icons';
import { useSelect, useDispatch } from '@wordpress/data';

import type { BlockWithCommand } from './types';

// @ts-ignore
export function ButtonControls( { attributes, setAttributes } ) {
	const { updateBlockAttributes, __unstableMarkNextChangeAsNotPersistent } =
		useDispatch( blockEditorStore );
	const blockTypesWithCommands: Record<
		string,
		Record< string, { label: string; description: string } >
	> = useSelect( ( select ) => {
		return Object.fromEntries(
			// eslint-disable-next-line prettier/prettier
			(
				// @ts-ignore
				select( blocksStore ).getBlockTypes() as Array<
					BlockWithCommand< any >
				>
			 )
				.filter(
					( { supports } ) =>
						supports?.commands &&
						Object.keys( supports?.commands ).length > 0
				)
				.map( ( { name, supports } ) => [ name, supports.commands ] )
		);
	}, [] );

	const allClientIds = useSelect( ( select ) => {
		const { getClientIdsWithDescendants } = select( blockEditorStore );
		return getClientIdsWithDescendants();
	}, [] );

	const blocksWithCommands = useSelect(
		( select ) => {
			const { getBlock } = select( blockEditorStore );

			return allClientIds
				.map( ( clientId ) => getBlock( clientId ) )
				.filter( ( block ) => block !== null )
				.filter( ( { name } ) =>
					Object.hasOwn( blockTypesWithCommands, name )
				)
				.map( ( { name, clientId, attributes: blockAttributes } ) => ( {
					clientId,
					commandId: blockAttributes.commandId,
					commands: blockTypesWithCommands[ name ],
				} ) );
		},
		[ allClientIds, blockTypesWithCommands ]
	);

	const controlledBlock = blocksWithCommands.find(
		( { commandId } ) => commandId === attributes.commandFor
	);

	return (
		<InspectorControls>
			<PanelBody
				initialOpen={ true }
				icon={ link }
				title={ __( 'Commands', 'block-invokers' ) }
			>
				<SelectControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label={ __( 'Block', 'block-invokers' ) }
					options={ [
						{
							label: __( 'None', 'block-invokers' ),
							value: '',
						},
						...blocksWithCommands.map( ( { clientId } ) => ( {
							label: (
								<BlockTitle clientId={ clientId } />
							 ) as unknown as string,
							value: clientId,
						} ) ),
					] }
					value={ controlledBlock?.clientId || '' }
					onChange={ ( value ) => {
						if ( '' === value ) {
							setAttributes( {
								commandFor: '',
								command: '',
								tagName: 'a',
							} );
							return;
						}

						// TODO: Add commandId attribute to block, but without causing history update.
						const commandId = blocksWithCommands.find(
							( { clientId } ) => clientId === value
						)?.commandId;
						const commandFor = commandId || uuidv4();
						setAttributes( { commandFor } );

						if ( ! commandId ) {
							// This side effect should not create an undo level.
							void __unstableMarkNextChangeAsNotPersistent();
							void updateBlockAttributes( value, {
								commandId: commandFor,
							} );
						}
					} }
					help={ __(
						'Select the block to control',
						'block-invokers'
					) }
				/>
				{ controlledBlock && (
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Command', 'block-invokers' ) }
						options={ [
							{
								label: __( 'None', 'block-invokers' ),
								value: '',
							},
							...Object.entries( controlledBlock.commands ).map(
								( [ key, value ] ) => ( {
									label: value.label,
									value: key,
								} )
							),
						] }
						value={ attributes.command || '' }
						onChange={ ( value ) =>
							setAttributes( {
								command: value,
								tagName: 'button',
							} )
						}
						help={ __(
							'Select the command to perform on the block.',
							'block-invokers'
						) }
					/>
				) }
			</PanelBody>
		</InspectorControls>
	);
}
