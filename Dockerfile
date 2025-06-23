FROM php:7.4-apache


RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite


COPY . /var/www/html/


RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html


RUN a2enmod rewrite
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
COPY ddos-demo.conf /etc/apache2/conf-available/ddos-demo.conf
RUN a2enconf ddos-demo


COPY init-db.sh /init-db.sh
RUN chmod +x /init-db.sh


ENTRYPOINT ["/init-db.sh"] 