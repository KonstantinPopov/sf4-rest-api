FROM php:7.2-fpm

WORKDIR /home/www

RUN apt-get update
RUN apt-get install -y mc libxml2-dev zlib1g-dev
RUN docker-php-ext-install soap pdo_mysql opcache zip

RUN pecl install xdebug-2.6.1 #FUCKING xdebug  2.7.2 is sucks
RUN apt-get install -y git

RUN apt-get update && apt-get -y install cron supervisor

COPY ./crontab/scheduled.crontab /var/spool/cron/crontabs/root
COPY ./supervisord/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN chmod 0600 /var/spool/cron/crontabs/root
RUN chown root:crontab /var/spool/cron/crontabs/root
RUN touch /var/www/log/cron.log

COPY ./php/xdebux.ini /usr/local/etc/php/conf.d/xdebug.ini
RUN curl -sS https://getcomposer.org/installer | php -- --version=1.7.2 --install-dir=/usr/local/bin --filename=composer
RUN usermod -u 1000 www-data

ENTRYPOINT ["/home/www/docker/entrypoint.sh"]

CMD ["php-fpm"]
#CMD ["/usr/bin/supervisord"]
