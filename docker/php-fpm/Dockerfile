FROM php:fpm-alpine

COPY wait-for-it.sh /usr/bin/wait-for-it

RUN chmod +x /usr/bin/wait-for-it

# Install Git
RUN apk --update --no-cache add git

# Install PDO Mysql ext. on PHP
RUN docker-php-ext-install pdo_mysql

# Takes composer file from official image
COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

CMD composer install ; wait-for-it db:3306 -- bin/console doctrine:migrations:migrate ; php-fpm

EXPOSE 9000