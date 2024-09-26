#!/bin/sh

# Ensure to run PHPUnit from the PHP container, but also allow running it from the host
docker-compose exec php /srv/app/bin/phpunit $@ 2>/dev/null || php /srv/app/bin/phpunit $@
