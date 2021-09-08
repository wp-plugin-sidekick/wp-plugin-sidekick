# Loop through each plugin-module in the plugin.
shopt -s globstar dotglob
for FILE in includes/**/**/*; do
	# If the file being looped is called build-scripts.sh, run the commands inside it.
	if [[ "$FILE" == *"build-scripts.sh"* ]];then
		sh $FILE;
	fi
done