FROM php:7.4-cli-alpine
COPY ["updownio-exporter.php", "composer.json", "/usr/src/updownio/"]
WORKDIR /usr/src/updownio

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN composer install

CMD [ "php", "-S", "0.0.0.0:9124", "updownio-exporter.php" ]