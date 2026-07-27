FROM php:8.2-apache

# Activer mod_rewrite
RUN a2enmod rewrite

# Activer curl pour Supabase
RUN docker-php-ext-install curl

# Copier les fichiers
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]