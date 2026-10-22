#!/bin/bash
# Genera la documentación de las clases de php/ en docs/api con
# phpDocumentor y sustituye los recursos que la plantilla carga de CDNs
# (Google Fonts, Font Awesome, Prism, Fuse.js…) por las copias de
# docs/recursos, para que la documentación no dependa de terceros.
#
# Uso: scripts/generar-docs.sh [ruta/a/phpDocumentor.phar]
set -euo pipefail

cd "$(dirname "$0")/.."

PHPDOC=${1:-phpDocumentor.phar}

if [ ! -f "$PHPDOC" ]; then
    echo "No se encuentra $PHPDOC. Descárgalo de https://phpdoc.org/phpDocumentor.phar" >&2
    exit 1
fi

rm -rf docs/api
php "$PHPDOC" --no-interaction

cp -r docs/recursos docs/api/recursos

# Las páginas usan <base href>, así que recursos/... funciona en todas
find docs/api -name '*.html' -print0 | xargs -0 sed -i -E \
    -e 's#https://cdn\.jsdelivr\.net/npm/prismjs@[0-9.]+/prism\.min\.js#recursos/prism/prism.js#g' \
    -e 's#https://cdn\.jsdelivr\.net/npm/prismjs@[0-9.]+/themes/prism-okaidia\.css#recursos/prism/themes/prism-okaidia.min.css#g' \
    -e 's#https://cdn\.jsdelivr\.net/npm/prismjs@[0-9.]+/plugins/([a-z-]+)/(prism-[a-z-]+)\.(min\.)?(js|css)#recursos/prism/plugins/\1/\2.min.\4#g' \
    -e 's#https://cdn\.jsdelivr\.net/npm/fuse\.js@[0-9.]+#recursos/fuse/fuse.js#g' \
    -e 's#https://cdn\.jsdelivr\.net/npm/css-vars-ponyfill@[0-9.]+#recursos/css-vars-ponyfill/css-vars-ponyfill.min.js#g' \
    -e 's#https://cdnjs\.cloudflare\.com/ajax/libs/font-awesome/[0-9.]+/css/all\.min\.css#recursos/fontawesome/css/all.min.css#g' \
    -e 's#<link rel="preconnect" href="https://fonts\.gstatic\.com"[^>]*>##g' \
    -e 's#https://fonts\.googleapis\.com/css2\?family=Open\+Sans[^"]*#recursos/fuentes/fuentes.css#g' \
    -e '\#https://fonts\.googleapis\.com/css2\?family=Source\+Code\+Pro#d' \
    -e 's#(href="recursos/[^"]*") integrity="[^"]*" crossorigin="anonymous"#\1#g'

# Comprobamos que no quede ningún recurso remoto
if grep -rEq '(src|href)="https?://(cdn\.|cdnjs\.|fonts\.)' docs/api; then
    echo "Quedan recursos remotos en docs/api:" >&2
    grep -rhoE '(src|href)="https?://(cdn\.|cdnjs\.|fonts\.)[^"]*"' docs/api | sort -u >&2
    exit 1
fi

echo "Documentación generada en docs/api"
