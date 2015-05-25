FROM php:5.6-apache
MAINTAINER Benjamin Althues <benjamin@babab.nl>
COPY docker/php.ini /usr/local/etc/php/
COPY src/ /var/www/html/
