#!/bin/sh

set -e

# Inject config
cp /var/app/docky.json /docky/docky.json
cp /var/app/.docky.env /docky/.env

# Run
set -- /usr/local/bin/php /docky/docky "$@"

exec "$@"
