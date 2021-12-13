/**
 * Tailwind CSS config file.
 *
 * @since 1.0.0
 *
 * @internal Tailwind doesn't know to look for `.tailwindrc.cjs` files, unfortunately.
 * If you ever need to call tailwind directly be sure to tell it where the config file lives.
 * It's not an issue at this time because we don't call it directly. This file is require()'d elsewhere.
 */
/*
-----------------------------------------------------------------------------------------------------------------------
Example `config.tailwind.theme` in package.json:
-----------------------------------------------------------------------------------------------------------------------
"config" : {
	"tailwind" : {
		"theme" : {
			"fontFamily" : {
				"sans"  : [ "Exo 2", "sans-serif" ],
				"serif" : [ "Georgia", "serif" ]
			}
		}
	}
}
-----------------------------------------------------------------------------------------------------------------------
Example `index.scss` starter file contents:
-----------------------------------------------------------------------------------------------------------------------
@tailwind base;
@tailwind components;
@tailwind utilities;

@import 'https://fonts.googleapis.com/css2?family=Exo+2:wght@100..900&display=swap';
-----------------------------------------------------------------------------------------------------------------------
*/
var deepMerge = require( 'deepmerge' );
var package   = require( './package.json');
var config    = ( package.config || {} ).tailwind || {};

module.exports = deepMerge( {
	purge: {
		enable: true,
		globs: [ './src/{**/*,**/.*,.*,*}.{html,php,jsx,js}' ],
	},
	theme: {
		fontFamily : {
			sans  : [ 'Exo 2', 'sans-serif' ],
			serif : [ 'Georgia', 'serif' ]
		}
	},
}, config, { arrayMerge: ( x, y ) => y } );
