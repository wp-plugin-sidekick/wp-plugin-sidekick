while getopts 'p:n:t:f:' flag; do
	case "${flag}" in
		p) plugindirname=${OPTARG} ;;
		n) namespace=${OPTARG} ;;
		t) textdomain=${OPTARG} ;;
		f) fix=${OPTARG} ;;
	esac
done

# Get the absolute path to the plugin we want to check.
plugindir="$(dirname $(dirname $(dirname $(dirname $(realpath $0) ) ) ) )/$plugindirname"

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
if [ "$fix" = "1" ]; then
	npm run lint:js "$plugindir"  -- --fix;
else
	npm run lint:js "$plugindir";
fi