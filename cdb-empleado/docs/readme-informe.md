# Informe de auditoría – CdB Empleado

## 1) Mapa del plugin
```
cdb-empleado/
├── cdb-empleado.php
├── assets/
│   ├── css/
│   │   ├── empleado-card-oct.css
│   │   └── perfil-empleado.css
│   └── js/
│       └── equipo-year.js
├── inc/
│   ├── funciones-extra.php
│   ├── metacampos-empleado.php
│   ├── permisos.php
│   └── roles-capacidades.php
├── includes/
│   └── template-tags.php
├── templates/
│   └── empleado-card-oct.php
└── README.md
```

## 2) Hechos verificados
- **CPTs**
  - `empleado` (`show_in_rest` verdadero, soporta título, editor, imagen destacada, autor y campos personalizados).
- **Roles y capacidades**
  - Rol `empleado` con capacidad `read`.
- **Metaboxes / Metacampo**
  - “Información del Empleado” → meta key `disponible`.
  - “Equipo y Año” → meta keys `_cdb_empleado_equipo`, `_cdb_empleado_year` (filtro dinámico por año).
- **Shortcodes**
  - `[equipos_del_empleado]` (`empleado_id` opcional; por defecto usa el ID del post actual).
- **Hooks / filtros**
  - `cdb_empleado_inyectar_grafica`, `cdb_empleado_inyectar_calificacion`.
  - `cdb_grafica_empleado_html`, `cdb_grafica_empleado_form_html`, `cdb_grafica_empleado_scores_table_html`, `cdb_grafica_empleado_total`.
  - `cdb_empleado_use_new_card`, `cdb_grafica_empleado_notice`, `cdb_empleado_card_data`, `cdb_empleado_rank_ttl`, `cdb_empleado_rank_current`.
- **Endpoints AJAX/REST**
  - No se registran endpoints propios; el CPT `empleado` expone sus datos vía REST de WordPress.
- **Tablas personalizadas**
  - `${$wpdb->prefix}cdb_experiencia` (campos usados: `empleado_id`, `equipo_id`, `posicion_id`).

## 3) Dependencias y supuestos
- Existencia previa de:
  - CPT `equipo` con meta `_cdb_equipo_year`.
  - CPT `cdb_posiciones` con meta `_cdb_posiciones_score`.
  - Tabla `${$wpdb->prefix}cdb_experiencia` para relacionar empleado–equipo–posición.
- Integración con el plugin **cdb-grafica** (provee funciones/filters de la gráfica).
- Auto‑inyección en `single` de empleado: tarjeta + gráfica + bloque de calificación + listado de equipos.
- Assets registrados:
  - JS admin `cdb-empleado-metabox`.
  - CSS frontal `cdb-perfil-empleado`.
  - CSS opcional `cdb-empleado-card-oct` (activado por `cdb_empleado_use_new_card`).

## 4) Brechas del README actual
- Falta de secciones: requisitos, dependencias, configuración, uso detallado, tablas de shortcodes y hooks, modelo de datos, assets, seguridad/i18n, compatibilidad, troubleshooting, roadmap, licencia.
- No incluye tabla de contenidos ni ejemplos prácticos de hooks/shortcodes.
- Nomenclatura parcial (p.ej., metacampo “Disponible” sin referencia al meta key).
- No documenta la auto‑inyección ni cómo desactivarla.
- No se mencionan los handles de assets ni el textdomain.

## 5) Checklist para README.md
- [ ] Añadir descripción breve y propósito.
- [ ] Especificar requisitos mínimos (WP/PHP).
- [ ] Detallar dependencias: CPT `equipo`, `cdb_posiciones`, tabla `${prefix}cdb_experiencia`, plugin `cdb-grafica`.
- [ ] Incluir instrucciones de instalación desde ZIP y repositorio.
- [ ] Documentar configuración: metacampo “Disponible”, metabox “Equipo y Año”, auto‑inyección y desactivación vía filtros.
- [ ] Proporcionar ejemplos de uso del shortcode `[equipos_del_empleado]`.
- [ ] Crear tabla de shortcodes.
- [ ] Crear tabla de hooks/filtros con parámetros, retorno y ejemplos.
- [ ] Describir modelo de datos (CPTs, roles, meta keys, tabla personalizada).
- [ ] Enumerar assets con handles y condiciones de carga.
- [ ] Explicar medidas de seguridad (nonces, capabilities) y i18n (textdomain).
- [ ] Añadir secciones de compatibilidad, troubleshooting, roadmap, licencia y créditos.
- [ ] Mantener el Changelog existente sin modificaciones.
