# CdB Empleado

Plugin de WordPress que registra el tipo de contenido **Empleado**, su rol asociado y las herramientas para gestionar **disponibilidad, equipos y calificaciones** dentro de la plataforma CdB. En la vista singular del empleado se **auto-inyecta** una tarjeta de perfil con **gráfica** y **listado de equipos**.

## Tabla de contenidos
- [Descripción](#descripción)
- [Requisitos](#requisitos)
- [Dependencias y supuestos](#dependencias-y-supuestos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso](#uso)
- [Shortcodes](#shortcodes)
- [Hooks y filtros](#hooks-y-filtros)
- [Modelo de datos](#modelo-de-datos)
- [Assets (CSS/JS)](#assets-cssjs)
- [Seguridad e i18n](#seguridad-e-i18n)
- [Compatibilidad y buenas prácticas](#compatibilidad-y-buenas-prácticas)
- [Solución de problemas](#solución-de-problemas)
- [Roadmap](#roadmap)
- [Licencia y créditos](#licencia-y-créditos)
- [Changelog](#changelog)

## Descripción
- CPT `empleado` con `show_in_rest` habilitado (título, editor, imagen destacada, autor, campos personalizados).
- Rol `empleado` con capacidades mínimas de lectura.
- Metacampo **Disponible** y metabox **Equipo y Año** (filtrado dinámico por año).
- Auto-inyección en `single` de empleado: **tarjeta**, **gráfica de calificación** y **listado de equipos**.

## Requisitos
- **Mínimos:** WordPress 4.7+, PHP 7.1+  
- **Recomendado:** WordPress 6.6+, PHP 8.1+

## Dependencias y supuestos
- CPT `equipo` (meta `_cdb_equipo_year`)
- CPT `cdb_posiciones` (meta `_cdb_posiciones_score`)
- Tabla personalizada **`${prefix}cdb_experiencia`** (`empleado_id`, `equipo_id`, `posicion_id`)
- Plugin **cdb-grafica** para cálculos/representación de la gráfica

## Instalación
### Desde ZIP
1. Comprime como `cdb-empleado.zip`.
2. *Plugins → Añadir nuevo → Subir plugin*.
3. Activa **CdB Empleado**.

### Desde repositorio
1. Clona en `wp-content/plugins/cdb-empleado`.
2. Activa en el panel de administración.

## Configuración
### Metacampo “Disponible”
- Metabox **Información del Empleado**  
- Meta key: `disponible` (`1`/`0`)

### Metabox “Equipo y Año”
- Filtrado dinámico de equipos por año (`assets/js/equipo-year.js`)  
- Meta keys: `_cdb_empleado_year`, `_cdb_empleado_equipo`

### Auto-inyección en `single` de empleado
Inserta **tarjeta + gráfica + bloque de calificación + listado de equipos**.
Para desactivar:
```php
add_filter('cdb_empleado_inyectar_grafica', '__return_false');
add_filter('cdb_empleado_inyectar_calificacion', '__return_false');
```

### Rendimiento
- Submenú **Rendimiento**: define el TTL de ranking (`rank_ttl` en segundos, predeterminado `600`).

## Uso
### Shortcode principal
```html
[equipos_del_empleado]
[equipos_del_empleado empleado_id="123"]
```
- Si no se indica `empleado_id`, usa el ID del post actual.
- La salida lista equipos y posiciones según `${prefix}cdb_experiencia`.

### Hooks habituales
```php
add_filter('cdb_grafica_empleado_html', function($html, $empleado_id, $args){
    return '<div class="mi-grafica">...</div>';
}, 10, 3);
```

## Shortcodes
| Shortcode | Atributos | Descripción | Ejemplo |
|-----------|-----------|-------------|---------|
| `equipos_del_empleado` | `empleado_id` (int, opcional; por defecto el post ID) | Lista equipos y posiciones del empleado consultando `${prefix}cdb_experiencia`. | `[equipos_del_empleado empleado_id="15"]` |

## Hooks y filtros
| Hook/filtro | Propósito | Parámetros | Retorno | Ejemplo |
|-------------|-----------|------------|---------|---------|
| `cdb_empleado_inyectar_grafica` | Habilita/inhibe la gráfica en `single` de empleado. | `bool $enabled`, `int $empleado_id` | bool | `add_filter('cdb_empleado_inyectar_grafica','__return_false');` |
| `cdb_empleado_inyectar_calificacion` | Controla el bloque de calificación. | `bool $enabled`, `int $empleado_id` | bool | `add_filter('cdb_empleado_inyectar_calificacion','__return_false');` |
| `cdb_grafica_empleado_html` | Sustituye el HTML de la gráfica. | `string $html`, `int $empleado_id`, `array $args` | string | `add_filter('cdb_grafica_empleado_html','mi_callback',10,3);` |
| `cdb_grafica_empleado_form_html` | Personaliza el formulario de calificación. | `string $html`, `int $empleado_id`, `array $args` | string | `add_filter('cdb_grafica_empleado_form_html','mi_form',10,3);` |
| `cdb_grafica_empleado_scores_table_html` | Ajusta la tabla de puntuaciones. | `string $html`, `int $empleado_id`, `array $args` | string | `add_filter('cdb_grafica_empleado_scores_table_html','mi_tabla',10,3);` |
| `cdb_grafica_empleado_total` | Modifica el puntaje total mostrado. | `float $total`, `int $empleado_id` | float | `add_filter('cdb_grafica_empleado_total','mi_total',10,2);` |
| `cdb_empleado_use_new_card` | Decide uso de tarjeta alternativa. | `bool $use_new`, `int $empleado_id` | bool | `add_filter('cdb_empleado_use_new_card','__return_true');` |
| `cdb_grafica_empleado_notice` | Personaliza aviso en la sección de calificación. | `string $msg`, `int $empleado_id` | string | `add_filter('cdb_grafica_empleado_notice','mi_aviso',10,2);` |
| `cdb_empleado_card_data` | Filtra datos mostrados en la tarjeta. | `array $data`, `int $empleado_id` | array | `add_filter('cdb_empleado_card_data','mi_data',10,2);` |
| `cdb_empleado_rank_ttl` | Ajusta duración del caché de rankings. | `int $seconds` | int | `add_filter('cdb_empleado_rank_ttl', fn()=>300);` |
| `cdb_empleado_rank_current` | Modifica ranking calculado para un empleado. | `mixed $rank`, `int $empleado_id` | mixed | `add_filter('cdb_empleado_rank_current','mi_rank',10,2);` |

## Modelo de datos
- **CPTs:** `empleado` (propio), `equipo`, `cdb_posiciones` (supuestos)  
- **Rol:** `empleado` (capacidad `read`)  
- **Meta keys:** `disponible`, `_cdb_empleado_year`, `_cdb_empleado_equipo`  
- **Tabla:** `${prefix}cdb_experiencia` (relación empleado–equipo–posición)

## Assets (CSS/JS)
| Handle | Tipo | Dónde se carga | Notas |
|--------|------|----------------|-------|
| `cdb-empleado-metabox` | JS | Admin al **editar/crear** CPT empleado | `cdb-empleado.php:168-179` |
| `cdb-perfil-empleado` | CSS | Solo en `is_singular('empleado')` | `cdb-empleado.php:199-204` |
| `cdb-empleado-card-oct` | CSS | Tarjeta alternativa si `cdb_empleado_use_new_card` → `true` | `cdb-empleado.php:327-330` |

## Seguridad e i18n
- **Nonces:**  
  - `cdb_empleado_nonce` → `inc/metacampos-empleado.php:31` (verificación en `:48`)  
  - `cdb_empleado_equipo_nonce` → `cdb-empleado.php:227` (verificación en `:262-263`)  
- **Capacidades:** `edit_post` al guardar metaboxes  
- **Textdomain:** `cdb-empleado`

## Compatibilidad y buenas prácticas
- Encola assets **solo donde se usan**.  
- Usa `${prefix}` (`$wpdb->prefix`) al consultar tablas personalizadas.  
- Evita cache en la página singular de empleado con `nocache_headers`.

## Solución de problemas
- **No se muestran equipos:** comprueba registros en `${prefix}cdb_experiencia` y existencia de CPTs requeridos.  
- **No aparece la gráfica:** verifica que **cdb-grafica** esté activo y que no se haya deshabilitado `cdb_empleado_inyectar_grafica`.

## Roadmap
- Endpoints REST específicos para consultas de experiencia.  
- Soporte para tarjetas/insignias adicionales.

## Licencia y créditos
- Licencia: GPL-2.0  
- Autor: Proyecto CdB — https://proyectocdb.es

## Changelog
### 1.0.2
- Se corrigió el paso de datos de equipos a JavaScript para que se envíen como un array nativo en lugar de una cadena JSON.

### 1.0.1
- Se añadió verificación de nonce y comprobación de capacidades al guardar la metabox "Equipo y Año" para mejorar la seguridad.

### 1.0.0
- Versión inicial.
