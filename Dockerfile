FROM php:7.2-apache
COPY eshop/ /var/www/html/


#installs php composer
RUN apt update
RUN echo 'Y' | apt install wget unzip
RUN cd ~
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN HASH=`curl -sS https://composer.github.io/installer.sig`
RUN php -r "if (hash_file('SHA384', '/tmp/composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer

#runs laravel server
CMD php /var/www/html/artisan serve --host=0.0.0.0 --port=8000 