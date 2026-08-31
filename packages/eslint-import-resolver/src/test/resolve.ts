import { resolve } from '../';

describe( 'resolve', () => {
	it( 'resolves file in monorepo package', () => {
		const result = resolve( '@block-invokers/editor', 'index.tsx' );
		expect( result.found ).toBe( true );
	} );
} );
