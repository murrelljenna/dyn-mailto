#!/bin/bash
# Builds a .zip build of the plugin. This build simply excludes unecessary dev tools. 
# The build, compressed and uncompressed, can be found in /dist/

PLUGIN_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
TARGET_DIR="${PLUGIN_DIR}/dist"
VENDOR_DIR="${PLUGIN_DIR}/vendor"

rsync -ar "${PLUGIN_DIR}" "${TARGET_DIR}" \
    --exclude="vendor/squizlabs" \
    --exclude="vendor/wp-coding-standards" \
    --exclude=".git" \
    --exclude=".gitignore" \
    --exclude="dist" \
    --exclude="composer.phar" \
    --exclude="composer.json" \
    --exclude="composer.lock" \
    --exclude="*.swp" \
    --exclude="dist" \
    --exclude="build.sh"

zip -r "${TARGET_DIR}/dyn-mailto.zip" "${TARGET_DIR}/dyn-mailto"
