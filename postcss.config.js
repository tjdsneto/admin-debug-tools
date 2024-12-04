const tailwindcss = require('tailwindcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');

const isProduction = process.env.NODE_ENV === 'production';

// Scope TailwindCSS and other styles to the app container.
const postcssPrefixSelector = require('postcss-prefix-selector')({
	prefix: '.adbtl-app',
	transform(prefix, selector, prefixedSelector, filePath, rule) {
		if (selector.match(/\.adbtl-app/) || selector.match(/#adbtl-app/)) {
			return selector; // Do not prefix if already prefixed.
		}

		if (selector.match(/^(html|body)/)) {
			return selector.replace(/^([^\s]*)/, `$1 ${prefix}`);
		}

		if (filePath.match(/node_modules/) && !filePath.match(/@wordpress\/components\/build-style\/style.css/)) {
			return selector; // Do not prefix styles imported from node_modules.
		}

		if (filePath.match(/module.scss/)) {
			return `:global(.adbtl-app) ${selector}`; // CSS modules need to be prefixed with :global(.adbtl-app).
		}

		const annotation = rule.prev();
		if (annotation?.type === 'comment' && annotation.text.trim() === 'no-prefix') {
			return selector; // Do not prefix style rules that are preceded by: /* no-prefix */
		}

		return prefixedSelector;
	},
});

// @wordpress/scripts is alredy wired to use postcss.config.js.
// This config is based on the default found in @wordpress/scripts/config/webpack.config
module.exports = {
	ident: 'postcss',
	sourceMap: !isProduction,
	plugins: [
		tailwindcss('./tailwind.config.js'),
		postcssPrefixSelector,
		autoprefixer,

		// Defautl cssnano config from @wordpress/scripts/config/webpack.config
		...(isProduction
			? [
					cssnano({
						preset: [
							'default',
							{
								discardComments: {
									removeAll: true,
								},
							},
						],
					}),
			  ]
			: []),
	],
};
