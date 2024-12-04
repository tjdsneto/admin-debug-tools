const defaults = require('@wordpress/scripts/config/webpack.config');

const localWpDomain = process.env.LOCAL_WORDPRESS_DOMAIN;

module.exports = {
	...defaults,
	entry: {
		...defaults.entry(),
	},
	externals: {
		react: 'React',
	},
	devServer: {
		...defaults.devServer,
		allowedHosts: [localWpDomain, 'localhost', '127.0.0.1'],
	},
};
