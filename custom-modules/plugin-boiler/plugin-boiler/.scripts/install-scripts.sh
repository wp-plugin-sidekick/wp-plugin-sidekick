# Do not touch this file.

# Loop through each plugin-module in the plugin.
for DIR in custom-modules/*/; do
	# Go to the directory of this addon-module.
	cd "$DIR";

	# Run the "npm run dev" command in it's package.json file.
	echo $DIR;
	npm install;

	# Go back to main directory, which includes the plugin modules.   
	cd -;
	
done
