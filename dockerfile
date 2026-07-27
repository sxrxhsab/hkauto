FROM php:8.2-apache

# Installer curl et ses dépendances
RUN apt-get update && apt-get install -y \
    curl \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier les fichiers
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]