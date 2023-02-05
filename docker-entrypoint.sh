#!/bin/sh

# Inject config
cp /app/docky.json /docky/docky.json
cp /app/.docky.env /docky/.env

# Run
/usr/local/bin/php /docky/docky "$@"
