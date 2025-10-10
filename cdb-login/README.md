# CdB Login

CdB Login es un plugin de WordPress que restringe el acceso de los visitantes no autenticados y personaliza la pantalla de inicio de sesión. También almacena en una tabla propia los accesos de los usuarios para su posterior consulta.

## Instalación
1. Copia la carpeta del plugin en `wp-content/plugins`.
2. Actívalo desde el panel de administración de WordPress. Durante la activación se creará automáticamente la tabla `cdb_login_access_log`.
3. Accede a **Ajustes → CdB Login** para configurar colores, textos y opciones avanzadas.

## Características
- Redirección automática al formulario de login salvo en páginas públicas.
- Personalización del formulario con colores y logo.
- Registro de accesos filtrado por rol de usuario.
- Página de administración para revisar y borrar registros.

## Desinstalación
Al desinstalar el plugin se elimina la tabla de registros y todas las opciones guardadas. Puedes hacerlo desde el listado de plugins en WordPress.

