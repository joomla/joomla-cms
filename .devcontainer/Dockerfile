# Usa l'immagine ufficiale PHP con Apache
FROM php:8.3-apache

# Installa le dipendenze necessarie e Node.js
RUN apt-get update && \
    apt-get install -y \
        libgtk2.0-0 \
        libgtk-3-0 \
        libgbm-dev \
        libnotify-dev \
        libgconf-2-4 \
        libnss3 \
        libxss1 \
        libasound2 \
        libxtst6 \
        xauth \
        xvfb \
        git \
        unzip \
        libpng-dev \
        libjpeg-dev \
        software-properties-common \
        curl \
        libfreetype6-dev && \
    curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g npm@latest && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd mysqli pdo pdo_mysql && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

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
