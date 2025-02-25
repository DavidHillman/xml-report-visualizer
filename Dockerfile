FROM ubuntu:18.04

MAINTAINER David Hillman <hillmands@hobbysumo.com>

# disable interactive functions.
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install -y software-properties-common && apt-get update && add-apt-repository ppa:ondrej/php && apt-get update &&  \
	apt-get install -y apache2 \
	libapache2-mod-fcgid \
	php7.4-fpm \
	php-bz2 \
	php-intl \
	php-gd \
	php-mbstring \
	php-mysql \
	php-zip \
	curl \
	&& rm -rf /var/lib/apt/lists/* \
	&& apt-get clean -y
# Install composer for PHP dependencies
RUN cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Enable apache mods.
RUN a2enconf php7.4-fpm
RUN a2enmod proxy
RUN a2enmod proxy_fcgi setenvif
RUN a2enmod rewrite

# Update the PHP.ini file, enable <? ?> tags and quieten logging.
RUN sed -i "s/short_open_tag = Off/short_open_tag = On/" /etc/php/7.4/fpm/php.ini
RUN sed -i "s/error_reporting = .*$/error_reporting = E_ERROR | E_WARNING | E_PARSE/" /etc/php/7.4/fpm/php.ini

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

EXPOSE 80

# Copy site into place.
ADD www /var/www/site

# Update the default apache site with the config we created.
ADD apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# By default, simply start apache.
CMD /usr/sbin/apache2ctl -D FOREGROUND

# expose container at port 80
EXPOSE 80

