FROM php:7.4-cli-alpine
COPY ["updownio-exporter.php", "composer.json", "/usr/src/updownio/"]
WORKDIR /usr/src/updownio

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN composer install

HEALTHCHECK  --interval=30s --timeout=3s \
    CMD curl --fail http://localhost:9124/health || exit 1   

CMD [ "php", "-q", "-S", "0.0.0.0:9124", "updownio-exporter.php" ]