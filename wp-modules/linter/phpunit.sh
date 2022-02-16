while getopts 'p:m:' flag; do
	case "${flag}" in
		p) plugindirname=${OPTARG} ;;
		m) multisite=${OPTARG} ;;
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

# Start wp-env
npx wp-env start;

# Run PHPunit inside wp-env, targeting the plugin in question.
if [ "$multisite" = "1" ]; then
	npx wp-env run phpunit "phpunit -c /var/www/html/wp-content/plugins/wp-plugin-sidekick/wp-modules/phpunit/phpunit.xml.dist /var/www/html/wp-content/plugins/$plugindirname";
else
	npx wp-env run phpunit "WP_MULTISITE=1 phpunit -c /var/www/html/wp-content/plugins/wp-plugin-sidekick/wp-modules/phpunit/phpunit.xml.dist /var/www/html/wp-content/plugins/$plugindirname";
fi