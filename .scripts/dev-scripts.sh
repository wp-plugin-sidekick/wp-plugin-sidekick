# Loop through each plugin-module in the plugin.

for DIR in add-on-modules/*/; do
	# Go to the directory of this add-on-module.
	cd "$DIR";
	echo "$DIR";

	# Run the "npm run dev" command in its package.json file.
	npm run dev;

	# Go back to main directory, which includes the plugin modules.   
	cd -;
	
done
