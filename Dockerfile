FROM ubuntu:24.04
ARG DEBIAN_FRONTEND=noninteractive
ARG PHP_VERSION

RUN apt update 
# Install dependencies
RUN apt install -y mysql-client
RUN apt-get install -y apache2
RUN apt-get install -y php libapache2-mod-php
RUN apt-get install -y -m   php-cli \
                             php-common \
                             php-mysql 

# Config Apache
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
RUN chown www-data:www-data -R /var/www
RUN chmod -R 755 /var/www
COPY .docker/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers

# run mysql initial script
COPY ./persistence/bd/init.sql /docker-entrypoint-initdb.d/
# Run Apache
CMD ["apachectl", "-D", "FOREGROUND"]

EXPOSE 80

