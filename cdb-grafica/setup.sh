#!/bin/bash
set -euo pipefail

# Instalar dependencias PHP si composer.json está presente
if [ -f composer.json ]; then
    composer install --no-interaction --prefer-dist
fi

# Instalar dependencias de Node y compilar assets
if [ -f package.json ]; then
    npm ci
    npm run build
fi

# Compilar archivos de traducción (.po -> .mo)
if compgen -G "languages/*.po" > /dev/null; then
    for po in languages/*.po; do
        msgfmt "$po" -o "${po%.po}.mo"
    done
fi
