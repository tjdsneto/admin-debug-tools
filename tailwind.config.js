const defaultTheme = require('tailwindcss/defaultTheme');

/**
 * Converts rem values to pixel values.
 *
 * This function takes an input (which can be an object, array, string, or function) and a font size (default is 16),
 * and converts all rem values in the input to pixel values. The conversion is based on the provided font size.
 *
 * @see https://github.com/tailwindlabs/tailwindcss/issues/1232#issuecomment-1330042062
 *
 * @since 1.0.0
 *
 * @param {Object|Array|string|Function} input         The input to convert.
 * @param {number}                       [fontSize=16] The base font size for the conversion.
 *
 * @return {Object|Array|string|Function} The input with all rem values converted to pixel values.
 */
function rem2px(input, fontSize = 16) {
	if (input === null) {
		return input;
	}
	switch (typeof input) {
		case 'object':
			if (Array.isArray(input)) {
				return input.map((val) => rem2px(val, fontSize));
			}
			const ret = {};
			for (const key in input) {
				ret[key] = rem2px(input[key], fontSize);
			}
			return ret;
		case 'string':
			return input.replace(/(\d*\.?\d+)rem$/, (_, val) => `${parseFloat(val) * fontSize}px`);
		case 'function':
			return eval(input.toString().replace(/(\d*\.?\d+)rem/g, (_, val) => `${parseFloat(val) * fontSize}px`));
		default:
			return input;
	}
}

/** @type {import('tailwindcss').Config} */
module.exports = {
	content: ['./src/**/*.{html,js}'],
	theme: {
		// Converts rem values to pixel values so the design is consistent across different WP themes.
		...rem2px(defaultTheme),
	},
	plugins: [],
};
