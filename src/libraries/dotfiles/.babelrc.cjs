/**
 * Babel config file.
 *
 * @since 1.0.0
 *
 * @internal Babel is aware of this config file's location.
 */
module.exports = function ( api ) {
	api.cache( false );

	return {
		presets: [ '@babel/preset-env', '@babel/preset-react', '@linaria' ],
	};
};
