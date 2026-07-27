FROM php:8.2-apache

# Activer les wrappers HTTP (déjà activés par défaut)
# Pas besoin d'installer curl

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier les fichiers
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]