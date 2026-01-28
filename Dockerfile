# file: Dockerfile
FROM ubuntu:24.04

LABEL maintainer="Martin Kokes"

RUN apt-get update \
    && apt-get install -y \
    apt-utils \
    build-essential \
    curl \
    git \
    zip \
    unzip \
    wget \
    mariadb-client
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get install -y software-properties-common

RUN apt-add-repository -y ppa:ondrej/php
RUN apt-get update \
    && apt-get install -y \
    nginx \
    php8.3 php8.3-fpm php8.3-mysql php8.3-curl php8.3-gd php8.3-intl \
    php8.3-mbstring php8.3-soap php8.3-xml php8.3-zip php8.3-bcmath \
    php8.3-imagick php8.3-xdebug php8.3-opcache

# install composer https://getcomposer.org/download/
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');"

RUN apt install -y mariadb-server

COPY sites-available /etc/nginx/sites-available

COPY webroot /usr/share/nginx/webroot

EXPOSE 80

STOPSIGNAL SIGQUIT

COPY import.sql /tmp/import.sql
ADD init_db.sh /tmp/init_db.sh
RUN ["chmod", "+x", "/tmp/init_db.sh"]
RUN /tmp/init_db.sh

ENTRYPOINT ["sh", "-c", "service mariadb start && service php8.3-fpm start && nginx -g 'daemon off;'"]
# docker build -t nazevimage ./
# docker run -it --name nazevkontejneru -p 8080:80 nazevimage