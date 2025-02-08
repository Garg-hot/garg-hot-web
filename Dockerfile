# Utiliser une version spécifique de PHP-FPM
FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP nécessaires pour Symfony
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Installation de Composer de manière sécurisée
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Configuration PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de composer en premier pour optimiser le cache
COPY composer.json composer.lock ./

# Installation des dépendances
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

# Copier le reste des fichiers de l'application
COPY . .

# Installation des assets et nettoyage du cache
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative
RUN php bin/console cache:clear --env=prod --no-debug
RUN php bin/console assets:install

# Configuration des permissions
RUN chown -R www-data:www-data var
RUN chmod -R 777 var

# Exposer le port
EXPOSE 9000

# Démarrer PHP-FPM
CMD ["php-fpm"]
