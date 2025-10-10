# CDB Gráfica

Plugin de WordPress para registrar valoraciones de bares y empleados mediante formularios y mostrar los resultados con bloques de Gutenberg.

## Instalación

1. Copia la carpeta del plugin en `wp-content/plugins`.
2. Activa **CDB Gráfica** desde el panel de administración de WordPress.

Al activarlo se crearán automáticamente las tablas personalizadas donde se almacenan las puntuaciones.

## Uso

- Utiliza los shortcodes `[grafica_bar_form]` y `[grafica_empleado_form]` para mostrar los formularios de valoración.
- Inserta los bloques "Gráfica Bar" o "Gráfica Empleado" en el editor para visualizar los promedios.

Si se deja un criterio sin valorar, la puntuación guardada es 0 y no se tendrá en cuenta para el promedio final.

## Configurar Colores

Dentro del menú **CdB Gráfica** se encuentra el submenú **Configurar Colores**. Desde esta página puedes modificar los colores utilizados en las gráficas:

- **Bar – Color de fondo** y **Bar – Color de borde** definen el aspecto del dataset de bares.
- **Empleado – Color de fondo** y **Empleado – Color de borde** controlan el dataset de empleados.

Cada selector cuenta con un deslizador *Alpha* que permite ajustar la transparencia del color. Los valores se guardan en formato `rgba`, por lo que puedes elegir tonalidades semitransparentes que se aplicarán directamente en las gráficas.

Al guardar los cambios, los nuevos valores se aplican automáticamente a las gráficas del sitio. El color de borde también se utiliza para los *ticks* de la escala, por lo que puedes ajustar su tonalidad desde esta misma pantalla.

## Desinstalación

Al desinstalar el plugin se eliminan las tablas creadas para almacenar los resultados.

## API Pública

El plugin expone varios helpers para que otros plugins (p.ej. `cdb-form`)
puedan obtener información de las valoraciones de empleados sin replicar la
lógica de las gráficas.

```php
$scores = cdb_grafica_get_scores_by_role( $empleado_id );
$ultima = cdb_grafica_get_last_rating_datetime( $empleado_id );
$total  = cdb_grafica_get_empleado_total( $empleado_id );
$grupos = cdb_grafica_get_empleado_group_avgs( $empleado_id );
```

### `cdb_grafica_get_scores_by_role( int $empleado_id, array $args = [] ): array`

Devuelve un arreglo con los totales por rol (`empleado`, `empleador`, `tutor`)
redondeados a un decimal o `0.0` si no hay datos. Acepta los argumentos:

- `bypass_cache` (`bool`) para ignorar los transients.
- `with_raw` (`bool`) para incluir el detalle intermedio ya calculado.

### `cdb_grafica_get_last_rating_datetime( int $empleado_id ): ?string`

Retorna la fecha/hora (`Y-m-d H:i:s`) de la última valoración registrada o
`null` si no existen datos.

### `cdb_grafica_get_empleado_total( int $empleado_id ): float`

Devuelve el total de puntuación del empleado. El valor puede modificarse con el
filtro `cdb_grafica_empleado_total`. El resultado se almacena en un transient
cuya vida útil puede ajustarse mediante `cdb_grafica_scores_ttl`.

### `cdb_grafica_get_empleado_group_avgs( int $empleado_id ): array`

Retorna los promedios por grupo del empleado. Antes de ser cacheados se pueden
ajustar con el filtro `cdb_grafica_empleado_group_avgs`. El TTL del transient se
controla igualmente con `cdb_grafica_scores_ttl`.

### Transients y filtros

- `cdb_grafica_scores_ttl` y `cdb_grafica_last_rating_ttl` permiten ajustar los
  TTL de los transients (por defecto 600 segundos).
- `cdb_grafica_transient_key` filtra las claves usadas para almacenar los
  resultados (`cdbg_scores_{ID}` y `cdbg_last_{ID}`).
- Otros filtros: `cdb_grafica_scores_args`, `cdb_grafica_last_rating_args`.
- `cdb_grafica_empleado_total` y `cdb_grafica_empleado_group_avgs` permiten
  modificar los resultados de esos helpers antes de ser cacheados.

Tras guardar una valoración se ejecuta el hook `cdb_grafica_after_save`, que
borra los transients anteriores.

### `cdb_grafica_admin_capability`

Permite modificar la capacidad requerida para acceder al menú de administración
del plugin. Por defecto es `manage_options`, pero otros plugins o temas pueden
ajustarla según sus necesidades:

```php
add_filter( 'cdb_grafica_admin_capability', function () {
    return 'edit_posts';
} );
```
