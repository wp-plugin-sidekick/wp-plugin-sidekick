# Loop through each plugin-module in the plugin.

for DIR in custom-modules/*/; do
	# Go to the directory of this custom-module.
	cd "$DIR";
	echo "Module: $DIR";

	# Run the "npm run dev" command in its package.json file.
	if [[ -f "package.json" ]]
	then
		npm run dev:css &
	fi

	# Go back to main directory, which includes the plugin modules.   
	cd -;
	
done
