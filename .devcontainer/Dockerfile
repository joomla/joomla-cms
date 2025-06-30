FROM mcr.microsoft.com/vscode/devcontainers/php:8.1
RUN apt-get update && apt-get install -y \
mariadb-server \
mariadb-client \
git \
unzip \
libzip-dev \
libpng-dev \
libjpeg-dev \
libfreetype6-dev \
libonig-dev \
libxml2-dev \
libicu-dev \
zlib1g-dev \
libgtk2.0-0 \
libgtk-3-0 \
libgbm-dev \
libnotify-dev \
libnss3 \
libxss1 \
libasound2 \
libxtst6 \
xauth \
xvfb \
&& rm -rf /var/lib/apt/lists/*

# Install PHP extensions (corrected list)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    gd \
    intl \
    zip \
    opcache \
    mbstring \
    bcmath \
    && a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 3. Install Node.js (npm) - Latest LTS version
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

WORKDIR /workspaces/joomla-cms
