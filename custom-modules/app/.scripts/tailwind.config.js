module.exports = {
	mode: 'jit',
	purge: [
		'./includes/js/src/visual/AddonBuilderApp.js',
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
				'option': '100px 1fr',
			}
		   }
	},
	variants: {
	    extend: {},
	},
	plugins: [
		require('daisyui'),
	],
};