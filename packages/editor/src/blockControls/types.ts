import type { Block, BlockSupports } from '@wordpress/blocks';

interface BlockSupportsWithCommands extends BlockSupports {
	commands: Record< string, { label: string; description: string } >;
}

export interface BlockWithCommand< T extends Record< string, any > = {} >
	extends Block< T > {
	readonly supports: BlockSupportsWithCommands;
}
