FROM php:8.0-apache

# Install dependencies for pdo_sqlite
RUN apt-get update && apt-get install -y libsqlite3-dev

# Installa estensione pdo_sqlite
RUN docker-php-ext-install pdo pdo_sqlite

# Abilita mod_rewrite (utile per futuri sviluppi)
RUN a2enmod rewrite

# Copia i file PHP nell'htdocs di Apache
COPY src/ /var/www/html/

# Crea la cartella per il database e imposta i permessi
RUN mkdir -p /var/www/data \
    && chown -R www-data:www-data /var/www/data \
    && chmod -R 0777 /var/www/data

# Copia la cartella data (per il database) nella posizione corretta
COPY data/ /var/www/data/

# Imposta i permessi anche per i file PHP
RUN chown -R www-data:www-data /var/www/html

# Espone la porta 80
EXPOSE 80
