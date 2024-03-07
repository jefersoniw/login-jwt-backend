FROM php:8.3-fpm-alpine3.19

# Install system dependencies
RUN apk add --no-cache \
  bash \
  curl \
  libpng-dev \
  libzip-dev \
  zlib-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install gd \
  && docker-php-ext-install zip \
  && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
  chown -R $user:$user /home/$user

# Install redis
RUN pecl install -o -f redis \
  &&  rm -rf /tmp/pear \
  &&  docker-php-ext-enable redis

# Install node and npm
RUN apk add --no-cache nodejs npm

# Set working directory
WORKDIR /var/www

EXPOSE 9000