/* global Chart */
/* jshint esversion: 6 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockType('cdb/grafica-bar', {
    title: 'Gráfica Bar',
    icon: 'chart-area',
    category: 'widgets',
    edit: () => {
        return (
            <div>
                <p>El bloque "Gráfica Bar" está funcionando correctamente. La configuración de colores ha sido eliminada para mayor estabilidad.</p>
            </div>
        );
    },
    save: () => null, // Renderizado en PHP
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("El DOM está cargado.");
    const graficaBarElement = document.getElementById("grafica-bar");
    if (!graficaBarElement) {
        console.error("No se encontró el elemento con id 'grafica-bar'.");
        return;
    }

    const canvas = document.createElement("canvas");
    graficaBarElement.appendChild(canvas);
    const ctx = canvas.getContext("2d");

    if (graficaBarElement && ctx) {
        const data = JSON.parse(graficaBarElement.dataset.valores);
        const borderWidth = parseInt(graficaBarElement.dataset.borderWidth, 10) || 2;
        const legendFont = parseInt(graficaBarElement.dataset.legendFont, 10) || 14;
        const ticksStep = parseInt(graficaBarElement.dataset.ticksStep, 10) || 1;
        const ticksMin = parseInt(graficaBarElement.dataset.ticksMin, 10) || 0;
        const ticksMax = parseInt(graficaBarElement.dataset.ticksMax, 10) || 10;

        const chartData = {
            labels: data.labels,
            datasets: [
                {
                    label: `${ __( 'Puntuación de Gráfica', 'cdb-grafica' ) }: ${data.total.toFixed(1)}`, // Limitar a 1 decimal
                    data: data.promedios,
                    backgroundColor: graficaBarElement.dataset.backgroundColor || "rgba(75, 192, 192, 0.2)",
                    borderColor: graficaBarElement.dataset.borderColor || "rgba(75, 192, 192, 1)",
                    borderWidth: borderWidth,
                },
            ],
        };

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
                            color: graficaBarElement.dataset.ticksColor || '#666',
                            backdropColor: graficaBarElement.dataset.ticksBackdropColor || undefined,
                        },
                    },
                },
            },
        });
        console.log("Gráfica creada correctamente.");
    }
});
