/**
 * Cssnano config file.
 *
 * @since 1.0.0
 *
 * @internal Cssnano doesn't know to look for `.cssnanorc.cjs` files, unfortunately.
 * If you ever need to call cssnano directly be sure to tell it where the config file lives.
 * It's not an issue at this time because we don't call it directly. This file is require()'d elsewhere.
 */
module.exports = {
	preset: [
		'default',
		{
			discardComments: {
				removeAll: true,
			},
		},
	],
};
