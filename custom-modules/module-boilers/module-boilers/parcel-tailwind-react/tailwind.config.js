module.exports = {
	mode: 'jit',
	purge: [
		'./includes/js/src/index.js',
	],
	darkMode: false, // or 'media' or 'class'
	theme: {},
	variants: {
	    extend: {},
	},
	plugins: [
		require('daisyui'),
	],
	daisyui: {
		styled: true,
		themes: [
			'dark',
		],
		base: true,
		utils: true,
		logs: true,
		rtl: false,
	},
};