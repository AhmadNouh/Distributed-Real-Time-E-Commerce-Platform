#!/bin/sh
# Installs the PHP extensions needed to talk gRPC.
#
# Why the nproc shim: install-php-extensions runs `make -j$(nproc)`, and the
# grpc extension is a heavy C++ build. On a Docker VM with ~4 GB of RAM
# `make -j8` gets OOM-killed (cc1plus: Killed). Limiting the parallelism keeps
# the build inside the available memory.
set -eu

mkdir -p /tmp/limited-jobs
printf '#!/bin/sh\necho 2\n' > /tmp/limited-jobs/nproc
chmod +x /tmp/limited-jobs/nproc

export PATH="/tmp/limited-jobs:${PATH}"

install-php-extensions grpc

php -m | grep -i '^grpc$'
