import { resolve } from '../';

describe( 'resolve', () => {
	it( 'resolves file in monorepo package', () => {
		const result = resolve( '@block-invokers/upload-media', 'index.ts' );
		expect( result.found ).toBe( true );
	} );
} );
