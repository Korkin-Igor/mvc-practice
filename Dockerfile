FROM php:8-apache
ARG USER=user
ARG PASSWORD=password

# Install dependencies
RUN apt-get update && apt-get install -y \
    sudo \
    build-essential \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev \
    libfreetype6 \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    nano \
    unzip \
    git \
    curl

# Install extensions
RUN docker-php-ext-install pdo_mysql zip exif pcntl mysqli gd

#Add user
RUN useradd -m -s /bin/bash -p $(openssl passwd -1 $PASSWORD) $USER
RUN usermod -g $USER $USER
RUN usermod -aG sudo $USER
RUN chown -R $USER.$USER /var/www/html
RUN echo "cd /var/www/html">>/home/$USER/.bashrc

#Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

#Apache configure
RUN a2enmod rewrite

ENTRYPOINT chmod 777 -R /var/www/html && apache2-foreground