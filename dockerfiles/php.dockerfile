FROM php:7.4-fpm-alpine

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

RUN mkdir -p /var/www

WORKDIR /var/www

# MacOS staff group's gid is 20, so is the dialout group in alpine linux. We're not using it, let's just remove it.
RUN delgroup dialout

RUN addgroup -g ${GID} --system symfony
RUN adduser -G symfony --system -D -s /bin/sh -u ${UID} symfony

RUN sed -i "s/user = www-data/user = symfony/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = symfony/g" /usr/local/etc/php-fpm.d/www.conf
RUN echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf

RUN docker-php-ext-install pdo pdo_mysql

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]
