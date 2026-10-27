FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev libicu-dev libzip-dev libxml2-dev \
    libcurl4-openssl-dev libonig-dev libldap2-dev libpq-dev libxslt1-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" mysqli pdo_mysql intl gd zip soap exif opcache \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf 'max_input_vars=5000\nmemory_limit=512M\nupload_max_filesize=100M\npost_max_size=100M\n' > /usr/local/etc/php/conf.d/moodle.ini

COPY . /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/moodle-entrypoint
RUN chmod +x /usr/local/bin/moodle-entrypoint \
    && mkdir -p /var/www/moodledata \
    && chown -R www-data:www-data /var/www/html /var/www/moodledata

ENTRYPOINT ["moodle-entrypoint"]
CMD ["apache2-foreground"]
