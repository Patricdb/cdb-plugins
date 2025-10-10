/* global Chart */
/* jshint esversion: 6 */

import { registerBlockType } from '@wordpress/blocks';

registerBlockType('cdb/grafica-empleado', {
    title: 'Gráfica Empleado',
    icon: 'chart-area',
    category: 'widgets',
    edit: () => {
        return (
            <div>
                <p>El bloque "Gráfica Empleado" está funcionando correctamente.</p>
            </div>
        );
    },
    save: () => null, // Renderizado en PHP
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("El DOM está cargado.");
    const graficaEmpleadoElement = document.getElementById("grafica-empleado");
    if (!graficaEmpleadoElement) {
        console.error("No se encontró el elemento con id 'grafica-empleado'.");
        return;
    }

    const canvas = document.createElement("canvas");
    graficaEmpleadoElement.appendChild(canvas);
    const ctx = canvas.getContext("2d");

    if (graficaEmpleadoElement && ctx) {
        const data = JSON.parse(graficaEmpleadoElement.dataset.valores);
        const colores = JSON.parse(graficaEmpleadoElement.dataset.roleColors || "{}");
        const borderWidth = parseInt(graficaEmpleadoElement.dataset.borderWidth, 10) || 2;
        const legendFont = parseInt(graficaEmpleadoElement.dataset.legendFont, 10) || 14;
        const ticksStep = parseInt(graficaEmpleadoElement.dataset.ticksStep, 10) || 1;
        const ticksMin = parseInt(graficaEmpleadoElement.dataset.ticksMin, 10) || 0;
        const ticksMax = parseInt(graficaEmpleadoElement.dataset.ticksMax, 10) || 10;

        const chartData = {
            labels: data.labels,
            datasets: []
        };

        if (Array.isArray(data.datasets)) {
            data.datasets.forEach((dataset) => {
                const cfg = colores[dataset.role] || {};
                chartData.datasets.push({
                    label: dataset.label,
                    data: dataset.data,
                    backgroundColor: cfg.background || "gray",
                    borderColor: cfg.border || "gray",
                    borderWidth: borderWidth,
                });
            });
        }

        new Chart(ctx, {
            type: "radar",
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: { font: { size: legendFont } },
                    },
                },
                scales: {
                    r: {
                        ticks: {
                            beginAtZero: true,
                            stepSize: ticksStep,
                            max: ticksMax,
                            min: ticksMin,
                            color: graficaEmpleadoElement.dataset.ticksColor || '#666',
                            backdropColor: graficaEmpleadoElement.dataset.ticksBackdropColor || undefined,
                        },
                    },
                },
            },
        });
        console.log("Gráfica creada correctamente.");
    }
});
