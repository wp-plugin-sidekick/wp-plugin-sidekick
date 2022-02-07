while getopts 'p:n:t:f:' flag; do
	case "${flag}" in
		p) plugindir=${OPTARG} ;;
		n) namespace=${OPTARG} ;;
		t) textdomain=${OPTARG} ;;
		f) fix=${OPTARG} ;;
	esac
done


#Go to wp-content directory.
cd ./../../../../;

# Make sure that packagejson and composer json exist in wp-content.
if [ ! -f package.json ] || [ ! -f composer.json ]; then
	cd -;
    sh hoister.sh;
	cd ./../../../../;
fi

# Make sure that node_modules and vendor directories exist in wp-content.
if [ ! -d node_modules ] || [ ! -d vendor ]; then
	cd -;
    sh install.sh;
	cd ./../../../../;
fi

# Run the lint command from the wp-content directory.
if [ "$fix" == "1" ]; then
	npm run lint:css "$plugindir/**/*.*css"  -- --fix;
else
	npm run lint:css "$plugindir/**/*.*css";
fi