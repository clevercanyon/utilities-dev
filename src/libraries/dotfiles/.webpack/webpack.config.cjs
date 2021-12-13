/**
 * Webpack config file.
 *
 * @since 1.0.0
 */
/*
-----------------------------------------------------------------------------------------------------------------------
Example `config.webpack.assetDirs` in package.json:
-----------------------------------------------------------------------------------------------------------------------
"config" : {
	"webpack" : {
		"assetDirs" : [ "./src/assets" ]
	}
}
-----------------------------------------------------------------------------------------------------------------------
Example directory structure expected for webpack:
-----------------------------------------------------------------------------------------------------------------------
./src/assets
	- styles/index.scss
	- scripts/index.js
	- webpack/ (output directory)
-----------------------------------------------------------------------------------------------------------------------
*/
var path       = require( 'path' );
var miniCss    = require( 'mini-css-extract-plugin' );
var baseConfig = {
	cache: false,
	mode: 'production',
	target: 'browserslist',
	plugins: [ new miniCss( { filename: '[name].min.css' } ) ],
	module: {
		rules: [
			{
				test: /\.(?:txt|md)$/i,
				use: [ 'raw-loader' ],
			},
			{
				test: /\.(?:html)$/i,
				use: [ 'html-loader' ],
			},
			{
				test: /\.(?:gif|jpe?g|png|svg|eot|ttf|woff[0-9]*)$/i,
				use: [ 'file-loader' ],
			},
			{
				test: /\.(?:css|scss)$/i,
				use: [ miniCss.loader, 'css-loader', 'postcss-loader', 'sass-loader' ],
			},
			{
				test: /\.(?:js|jsx)$/i,
				exclude: [ /\/(?:node_modules\/(?:core-js|webpack\/buildin))\//i ],
				use: [ 'babel-loader', '@linaria/webpack-loader' ],
			},
		],
	},
};
module.exports = function ( env, argv ) {
	var configurations = [];
	var package        = require( '../package.json');
	var config         = ( package.config || {} ).webpack || {};

	( config.assetDirs || [] ).forEach( function( assetsDir ) {
		assetsDir = path.resolve( __dirname, '../' + assetsDir );
		configurations.push( {
			...baseConfig,
			entry: {
				index: [
					assetsDir + '/styles/index.scss',
					assetsDir + '/scripts/index.js'
				],
			},
			output: {
				path: assetsDir + '/webpack',
				filename: '[name].min.js',
			},
		} );
	} );
	return configurations;
};
