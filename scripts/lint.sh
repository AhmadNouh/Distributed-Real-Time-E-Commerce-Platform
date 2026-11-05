#!/bin/sh
# Syntax-checks every PHP file of the project (app, tests, config, routes).
#
#   docker compose exec -T app sh /var/www/html/scripts/lint.sh
#
# Exits with code 1 as soon as one file does not parse.
set -eu

cd /var/www/html

fail=0

for dir in app tests config routes database bootstrap; do
    [ -d "$dir" ] || continue

    find "$dir" -type f -name '*.php' -print | while IFS= read -r file; do
        if ! out=$(php -l "$file" 2>&1); then
            echo "FAIL ${file}"
            echo "${out}"
            exit 1
        fi
    done || fail=1
done

if [ "$fail" -ne 0 ]; then
    echo "Lint failed."
    exit 1
fi

echo "All PHP files pass the syntax check."
