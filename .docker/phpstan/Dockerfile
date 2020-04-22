FROM php:cli-alpine
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN COMPOSER_HOME=/tmp composer global require --prefer-dist --no-scripts --no-suggest --ignore-platform-reqs \
    phpstan/phpstan \
    phpstan/phpstan-deprecation-rules \
    phpstan/phpstan-strict-rules
