module.exports = {
	mode: 'jit',
	purge: [
		'./includes/js/src/visual/AddonMaintainerApp.js',
	],
	darkMode: false, // or 'media' or 'class'
	theme: {
		extend: {
			colors: {
				color1: '#000',
				color2: '#FFF',
			},
			gridTemplateColumns: {
				// Complex site-specific column configuration
				'module-body': '100px 1fr',
			}
		   }
	},
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