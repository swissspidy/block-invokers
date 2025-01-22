import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';

import { ButtonControls } from './buttonControls';

const addInvokerControls = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		if ( props.name === 'core/button' ) {
			return (
				<>
					<BlockEdit { ...props } />
					<ButtonControls { ...props } />
				</>
			);
		}

		return <BlockEdit { ...props } />;
	},
	'withInvokerControls'
);

addFilter(
	'editor.BlockEdit',
	'block-invokers/add-invoker-controls',
	addInvokerControls
);

// TODO: Do this server-side instead.
function forceButtonTagName( blockAttributes, blockType ) {
	if ( 'core/button' === blockType.name ) {
		if ( blockAttributes.commandFor && blockAttributes.command ) {
			blockAttributes.tagName = 'button';
		}
	}
	return blockAttributes;
}

addFilter(
	'blocks.getBlockAttributes',
	'block-invokers/force-button-tagname',
	forceButtonTagName
);
