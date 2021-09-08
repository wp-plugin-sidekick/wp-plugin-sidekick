# Do not touch this file.

# Install any add-on-modules
cat install-add-on-modules.txt | while read line
do
	cd add-on-modules;
	# Clone the git repo into add-on-modules directory
	echo "$line";
	#!/bin/sh
	curl -L -sS "$line" > file.zip && \
	unzip file.zip                                  && \
	rm file.zip
	cd -;
done

# Loop through each plugin-module in the plugin.
for DIR in add-on-modules/*/; do
	# Go to the directory of this addon-module.
	cd "$DIR";

	# Run the "npm run dev" command in it's package.json file.
	echo $DIR;
	npm install;

	# Go back to main directory, which includes the plugin modules.   
	cd -;
	
done
