# Hoist package.json and composer.json to the wp-content directory
cp package.json composer.json ./../../../../

# Go to wp-content directory
cd ./../../../../;

# Run npm install
npm install;

# Run composer install
composer install;