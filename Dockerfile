FROM php:5-apache

ENV APACHE_RUN_USER=#1000

COPY php.ini /usr/local/etc/php/
COPY . /var/www/html
