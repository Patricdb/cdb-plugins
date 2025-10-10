# CdB Empleo

Plugin para gestionar ofertas de empleo en WordPress.

## Instalación
1. Copia la carpeta `cdb-empleo` en `wp-content/plugins/`.
2. Activa el plugin desde el panel de administración de WordPress.

## Shortcodes
- `[cdb_form_oferta]`: muestra el formulario para registrar ofertas (usuarios con rol **Empleador**).
- `[cdb_listado_ofertas posts_per_page="10"]`: lista las ofertas publicadas.
- `[cdb_empleo_suscritos]`: muestra las ofertas en las que el usuario actual está inscrito.

## Roles y capacidades
Al activarse se crea el rol **Empleador** con permisos para crear y gestionar ofertas.

## Traducciones
El textdomain del plugin es `cdb-empleo`. Coloca los archivos `.mo` en la carpeta `languages` dentro del plugin.

