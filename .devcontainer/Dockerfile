# Start from the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install system dependencies, Node.js, Composer, and Cypress dependencies
RUN apt-get update && apt-get install -y \
    # System tools and git
    git \
    unzip \
    curl \
    sudo \
    # PHP extensions dependencies
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    # MySQL client AND server
    default-mysql-client \
    default-mysql-server \
    # Cypress dependencies
    xvfb \
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
    && \
    # Install the required PHP extensions for Zip and MySQL
    docker-php-ext-install zip mysqli && \
    # Install Node.js (LTS version)
    curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - && \
    apt-get install -y nodejs && \
    # Install Composer globally
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    # Enable Apache's rewrite module and set the ServerName to prevent warnings
    a2enmod rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    # Clean up apt cache to reduce image size
    apt-get clean && rm -rf /var/lib/apt/lists/*
