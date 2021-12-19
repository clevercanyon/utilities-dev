/**
 * Stylelint config file.
 *
 * @since 1.0.0
 *
 * @internal Stylelint is aware of this config file's location.
 *
 * @internal PLEASE DO NOT EDIT THIS FILE!
 * This file and the contents of it are updated automatically.
 * Instead of editing, please see source repository @ <https://git.io/JD8Zo>.
 */
/* eslint-env node */

module.exports = {
	plugins   : [ 'stylelint-scss' ],
	extends   : [
		'stylelint-config-standard',
		'stylelint-config-html',
		'stylelint-no-unsupported-browser-features',
		'stylelint-config-recess-order',
		'stylelint-config-prettier',
	],
	rules     : {
		indentation          : 'tab',
		'at-rule-no-unknown' : [
			true,
			{
				ignoreAtRules : [
					'tailwind',
					'apply',
					'variants',
					'responsive',
					'screen',
				],
			},
		],
	},
	overrides : [
		{
			files        : [ '{**/*,**/.*,.*,*}.css' ],
			customSyntax : 'postcss-safe-parser',
		},
		{
			files        : [ '{**/*,**/.*,.*,*}.scss' ],
			customSyntax : 'postcss-scss',
		},
		{
			files        : [ '{**/*,**/.*,.*,*}.{xml,htm,html,php}' ],
			customSyntax : 'postcss-html',
		},
	],
};
