<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Obtiene los criterios de evaluación para empleados.
 *
 * @return array Criterios organizados por grupo.
 */
function cdb_get_criterios_empleado() {
    return [
        'DIE (Dirección)' => [
            'direccion' => [
                'label'       => 'Dirección',
                'descripcion' => 'Guiar al equipo hacia los objetivos comunes.',
                'visible'     => true,
            ],
        ],
        'SAL (Sala)' => [
            'camarero' => [
                'label'       => 'Camarero',
                'descripcion' => 'Atender y servir a los clientes en sala.',
                'visible'     => true,
            ],
        ],
        'TES (Técnica Sala)' => [
            'venta' => [
                'label'       => 'Venta',
                'descripcion' => 'Capacidades comerciales de venta.',
                'visible'     => true,
            ],
            'ritmo_sala' => [
                'label'       => 'Ritmo',
                'descripcion' => 'Gestión y ritmo del servicio.',
                'visible'     => true,
              ],
        ],
        'ATC (Atención al Cliente)' => [
            'satisfaccion' => [
                'label'       => 'Satisfacción',
                'descripcion' => 'Garantizar una experiencia positiva para el cliente.',
                'visible'     => true,
            ],
        ],
        'TEQ (Trabajo en Equipo)' => [
            'cooperacion' => [
                'label'       => 'Cooperación',
                'descripcion' => 'Colaborar para lograr objetivos comunes.',
                'visible'     => true,
            ],
        ],
        'ORL (Orden y Limpieza)' => [
            'orden' => [
                'label'       => 'Orden',
                'descripcion' => 'Organizar el espacio y tareas de forma eficiente.',
                'visible'     => true,
            ],
        ],
        'TEC (Técnica de Cocina)' => [
            'cocina_local' => [
                'label'       => 'Cocina Local',
                'descripcion' => 'Dominar técnicas culinarias locales.',
                'visible'     => true,
            ],
        ],
        'COC (Cocina)' => [
            'cocinero' => [
                'label'       => 'Cocinero',
                'descripcion' => 'Encargarse de la preparación de platos principales.',
                'visible'     => true,
            ],
        ],
    ];
}
