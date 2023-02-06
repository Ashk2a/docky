#!/bin/sh

set -e

# Inject config
cp /app/docky.json /docky/docky.json
cp /app/.docky.env /docky/.env

# Run
set -- /usr/local/bin/php /docky/docky "$@"

exec "$@"
