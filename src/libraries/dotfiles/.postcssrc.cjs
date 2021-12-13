/**
 * PostCSS config file.
 *
 * @since 1.0.0
 *
 * @internal PostCSS is aware of this config file's location.
 */
module.exports = function (api) {
	return {
		plugins: {
			'postcss-import': {},
			'tailwindcss': require('./.tailwindrc.cjs'),
			'postcss-preset-env': {
				stage: 1,
				features: {
					'focus-within-pseudo-class': false,
				},
			},
			'autoprefixer': require('autoprefixer'),
			'cssnano': require('./.cssnanorc.cjs'),
		},
	};
};
