#!/bin/sh

set -e

#if [ "${1#-}" != "$1" ]; then
#	set -- php-fpm "$@"
#fi

composer install -o
if [ "$APP_ENV" = 'dev' ]; then
    composer prepare-database-dev
fi

composer migrations-execute

composer dump-env "$APP_ENV"

cron -f &
tail -f /home/www/var/log/cron.log >> /proc/1/fd/1 &
docker-php-entrypoint php-fpm


CMD="$*"
exec $CMD
