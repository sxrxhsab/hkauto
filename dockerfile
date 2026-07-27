FROM php:8.2-apache

# Mettre à jour et installer curl
RUN apt-get update && apt-get install -y curl libcurl4-openssl-dev

# Configurer et installer l'extension curl
RUN docker-php-ext-install curl

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier les fichiers
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]