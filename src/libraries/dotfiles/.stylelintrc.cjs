/**
 * Stylelint config file.
 *
 * @since 1.0.0
 *
 * @internal Stylelint is aware of this config file's location.
 */
module.exports = {
	plugins: [ 'stylelint-scss' ],
	extends: [ "stylelint-config-standard", "stylelint-config-html", "stylelint-no-unsupported-browser-features", "stylelint-config-recess-order", "stylelint-config-prettier" ],
	rules: {
		indentation: 'tab',
		'at-rule-no-unknown': [
			true,
			{
				ignoreAtRules: [ 'tailwind', 'apply', 'variants', 'responsive', 'screen' ],
			},
		],
	},
	overrides: [
		{
			files: ["{**/*,**/.*,.*,*}.css"],
			customSyntax: "postcss-safe-parser"
		},
		{
			files: ["{**/*,**/.*,.*,*}.scss"],
			customSyntax: "postcss-scss"
		},
		{
			files: ["{**/*,**/.*,.*,*}.{html,php}"],
			customSyntax: "postcss-html"
		}
	]
};
