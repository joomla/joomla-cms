# Usa l'immagine ufficiale PHP con Apache
FROM joomlaprojects/docker-images:cypress8.4

# Installa Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configura Xdebug (host configurabile tramite variabile d'ambiente)
ENV XDEBUG_CLIENT_HOST=host.docker.internal
RUN echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.client_host=\${XDEBUG_CLIENT_HOST}" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini

# Espone la porta Xdebug
EXPOSE 9003

# Abilita mod_rewrite di Apache
RUN a2enmod rewrite

# Imposta la working directory
WORKDIR /var/www/html

# Copia il codice nel container
COPY . /var/www/html

# Imposta i permessi corretti
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Espone la porta 80
EXPOSE 80

# Avvia Apache
CMD ["apache2-foreground"]
