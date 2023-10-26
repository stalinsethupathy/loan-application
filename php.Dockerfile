FROM php:7.3-apache

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
	mysqli \
    zip \
    gd \
    intl \
    xmlrpc \
    soap \
    exif \
    opcache
