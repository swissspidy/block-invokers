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
