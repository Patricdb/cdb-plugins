<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Obtiene los criterios de evaluación para bares.
 *
 * @return array Criterios organizados por grupo.
 */
function cdb_get_criterios_bar() {
    return [
        'DIB (Direccion)' => [
            'relacion_superiores' => [
                'label'       => 'Relación con Superiores',
                'descripcion' => 'Relación de los empleados con los supervisores o gerentes.',
                'visible'     => true,
            ],
        ],
        'COE (Condiciones Económicas)' => [
            'salario' => [
                'label'       => 'Salario',
                'descripcion' => 'Adecuación del salario a las funciones desempeñadas.',
                'visible'     => true,
            ],
        ],
        'EDT (Espacio de trabajo)' => [
            'espacio_seguro' => [
                'label'       => 'Espacio Seguro',
                'descripcion' => 'Percepción general de seguridad en el lugar de trabajo.',
                'visible'     => true,
            ],
        ],
        'COL (Condiciones Laborales)' => [
            'turnos_justos' => [
                'label'       => 'Turnos Justos',
                'descripcion' => 'Distribución equitativa de turnos laborales entre los empleados.',
                'visible'     => true,
            ],
        ],
        'EQU (Equipo)' => [
            'motivacion' => [
                'label'       => 'Motivación',
                'descripcion' => 'Capacidad del equipo para mantener la motivación alta.',
                'visible'     => true,
            ],
        ],
        'ALB (Ambiente Laboral)' => [
            'bienvenida' => [
                'label'       => 'Bienvenida',
                'descripcion' => 'Valoración sobre cómo se recibe a los nuevos empleados en el equipo.',
                'visible'     => true,
            ],
        ],
        'DPF (Desarrollo Profesional)' => [
            'formacion' => [
                'label'       => 'Formación',
                'descripcion' => 'Oportunidades de capacitación y formación profesional.',
                'visible'     => true,
            ],
        ],
        'CLI (Clientela)' => [
            'reputacion' => [
                'label'       => 'Reputación',
                'descripcion' => 'Reputación general del lugar frente a los clientes.',
                'visible'     => true,
            ],
        ],
    ];
}
