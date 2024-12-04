module.exports = {
	root: true,
	extends: ['plugin:@wordpress/eslint-plugin/recommended'],
	env: {
		browser: true,
		node: true,
		jest: true,
	},
	globals: {
		jQuery: true,
	},
};
