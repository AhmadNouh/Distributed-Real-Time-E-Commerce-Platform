# Prebuilt PHP 8.3 FPM image with pdo_pgsql, redis, zip and composer already included.
# Using a prebuilt base keeps the build fast; only the gRPC extension needs
# compiling (it is not shipped with serversideup/php).
FROM serversideup/php:8.3-fpm

WORKDIR /var/www/html

# The framework is served from a bind mount in development, so opcache keeps
# validating timestamps - once a minute (freq=60) instead of on every request.
ENV PHP_OPCACHE_ENABLE=true \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=1 \
    PHP_OPCACHE_REVALIDATE_FREQ=60

# Installs the `grpc` extension. The script limits make to 2 jobs because the
# grpc C++ build is OOM-killed with the default parallelism on small Docker VMs.
COPY docker/php/install-extensions.sh /tmp/install-extensions.sh
RUN sh /tmp/install-extensions.sh
