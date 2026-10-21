FROM php:8.3.36-apache

RUN a2enmod rewrite \
	&& cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
	&& echo "session.upload_progress.enabled=0" > "$PHP_INI_DIR/conf.d/zz-security.ini"
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html
RUN chown www-data:www-data /var/www/html/extras/cache
