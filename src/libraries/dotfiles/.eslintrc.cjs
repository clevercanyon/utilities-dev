/**
 * ESLint config file.
 *
 * @since 1.0.0
 *
 * @internal ESLint is aware of this config file's location.
 */
 module.exports = {
	env: {
		browser: true,
		jquery: true
	},
	parserOptions: {
		ecmaVersion: 'latest',
		sourceType: 'module',
		ecmaFeatures: {
			jsx: true,
		},
	},
	parser: '@babel/eslint-parser',

	extends: ['eslint:recommended'],
	rules: {
		'space-unary-ops': [
			'warn',
			{
				words: true,
				nonwords: true,
				overrides: {
					'-': false,
					'+': false,
					'--': false,
					'++': false,
				},
			},
		],
		'space-in-parens': [ 'warn', 'always' ],
		'array-bracket-spacing': [ 'warn', 'always' ],
		'object-curly-spacing': [ 'warn', 'always' ],
		'computed-property-spacing': [ 'warn', 'always' ],
	},
};
