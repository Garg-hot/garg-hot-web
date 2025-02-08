#!/bin/sh
set -e

# Attendre que les variables d'environnement soient disponibles
if [ "$DATABASE_URL" ]; then
    echo "Waiting for database to be ready..."
    sleep 5
fi

# Installation des dépendances si le dossier vendor n'existe pas
if [ ! -d "vendor" ]; then
    composer install --prefer-dist --no-dev --no-progress --no-interaction
fi

# Permissions sur le dossier var
mkdir -p var
chmod -R 777 var/
chown -R www-data:www-data var/

# Nettoyage et réchauffement du cache
composer dump-autoload --optimize --no-dev --classmap-authoritative
php bin/console cache:clear --env=prod --no-debug
php bin/console assets:install --env=prod --no-debug

# Démarrer PHP-FPM
exec docker-php-entrypoint php-fpm
