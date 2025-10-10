(()=>{"use strict";
const e=window.wp.element,a=window.wp.blocks,o=window.wp.i18n;
(0,a.registerBlockType)("cdb/grafica-bar",{title:"Gráfica Bar",icon:"chart-area",category:"widgets",edit:()=> (0,e.createElement)("div",null,(0,e.createElement)("p",null,'El bloque "Gráfica Bar" está funcionando correctamente. La configuración de colores ha sido eliminada para mayor estabilidad.')),save:()=>null});
document.addEventListener("DOMContentLoaded",function(){
    console.log("El DOM está cargado.");
    const e=document.getElementById("grafica-bar");
    if(!e){console.error("No se encontró el elemento con id 'grafica-bar'.");return;}
    const a=document.createElement("canvas");
    e.appendChild(a);
    const t=a.getContext("2d");
    if(e&&t){
        const a=JSON.parse(e.dataset.valores),
            r=parseInt(e.dataset.borderWidth,10)||2,
            n=parseInt(e.dataset.legendFont,10)||14,
            i=parseInt(e.dataset.ticksStep,10)||1,
            l=parseInt(e.dataset.ticksMin,10)||0,
            c=parseInt(e.dataset.ticksMax,10)||10,
            s={labels:a.labels,datasets:[{label:`${(0,o.__)("Puntuación de Gráfica","cdb-grafica")}: ${a.total.toFixed(1)}`,data:a.promedios,backgroundColor:e.dataset.backgroundColor||"rgba(75, 192, 192, 0.2)",borderColor:e.dataset.borderColor||"rgba(75, 192, 192, 1)",borderWidth:r}]};
        new Chart(t,{type:"radar",data:s,options:{responsive:!0,plugins:{legend:{display:!0,labels:{font:{size:n}}}},scales:{r:{ticks:{beginAtZero:!0,stepSize:i,max:c,min:l,color:e.dataset.ticksColor||"#666",backdropColor:e.dataset.ticksBackdropColor||void 0}}}}});
        console.log("Gráfica creada correctamente.");
    }
});
})();
