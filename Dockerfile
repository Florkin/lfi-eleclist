FROM php:7.4-apache

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

#install all the dependencies
RUN apt-get update && apt-get install -y \
      libicu-dev \
      libzip-dev \
      libpq-dev \
      libmcrypt-dev \
      git \
      zip \
      unzip \
      libxrender1 \
      libfontconfig1 \
    && rm -r /var/lib/apt/lists/* \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install \
      intl \
      pcntl \
      pdo_mysql \
      pdo_pgsql \
      pgsql \
      zip

#install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

#create project folder
ENV APP_HOME /var/www
WORKDIR $APP_HOME

#change uid and gid of apache to docker user uid/gid
RUN usermod -u ${UID} www-data && groupmod -g ${GID} www-data

COPY php/eleclist.conf /etc/apache2/sites-available/eleclist.conf

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    a2enmod rewrite && \
    a2dissite 000-default && \
    a2ensite eleclist && \
    service apache2 restart

RUN cd /usr/local/etc/php/conf.d/ && \
  echo 'memory_limit = -1' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

#copy source files and run composer
COPY . $APP_HOME

RUN chmod -R 775 $APP_HOME
#change ownership
RUN chown -R www-data:www-data $APP_HOME
