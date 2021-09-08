const glob = require('glob');

// Get a list of postcss configs
const postCssConfigFiles = glob.sync('./includes/**/.npm-scripts/postcss.config.js', {
	absolute: true,
});

// Load and export postcss configurations
module.exports = postCssConfigFiles.map((file) => {
	const fileContents = require(file);
	return fileContents;
});