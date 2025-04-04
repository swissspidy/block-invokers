const { resolve } = require( 'node:path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		editor: resolve( __dirname, 'packages/editor/src/index.tsx' ),
		polyfill: require.resolve( 'invokers-polyfill' ),
	},
	output: {
		filename: '[name].js',
		path: resolve( __dirname, 'build' ),
		globalObject: 'self', // This is the default, but required for @shopify/web-worker.
	},
	resolve: {
		// Ensure "require" has a higher priority when matching export conditions.
		// https://webpack.js.org/configuration/resolve/#resolveconditionnames
		// Needed for @huggingface/transformers.
		conditionNames: [ 'node', 'require', 'import' ],
		extensions: [ '.jsx', '.ts', '.tsx', '...' ],
	},
};
