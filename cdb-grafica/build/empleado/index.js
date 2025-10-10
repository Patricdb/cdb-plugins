(()=>{"use strict";
const a=window.wp.blocks,o=window.ReactJSXRuntime;
(0,a.registerBlockType)("cdb/grafica-empleado",{title:"Gráfica Empleado",icon:"chart-area",category:"widgets",edit:()=> (0,o.jsx)("div",{children:(0,o.jsx)("p",{children:'El bloque "Gráfica Empleado" está funcionando correctamente.'})}),save:()=>null});
document.addEventListener("DOMContentLoaded",function(){
    console.log("El DOM está cargado.");
    const e=document.getElementById("grafica-empleado");
    if(!e){console.error("No se encontró el elemento con id 'grafica-empleado'.");return;}
    const a=document.createElement("canvas");
    e.appendChild(a);
    const t=a.getContext("2d");
    if(e&&t){
        const a=JSON.parse(e.dataset.valores),
            r=JSON.parse(e.dataset.roleColors||"{}"),
            n=parseInt(e.dataset.borderWidth,10)||2,
            l=parseInt(e.dataset.legendFont,10)||14,
            i=parseInt(e.dataset.ticksStep,10)||1,
            c=parseInt(e.dataset.ticksMin,10)||0,
            d=parseInt(e.dataset.ticksMax,10)||10,
            s={labels:a.labels,datasets:[]};
        Array.isArray(a.datasets)&&a.datasets.forEach(dset=>{
            const cfg=r[dset.role]||{};
            s.datasets.push({label:dset.label,data:dset.data,backgroundColor:cfg.background||"gray",borderColor:cfg.border||"gray",borderWidth:n});
        });
        new Chart(t,{type:"radar",data:s,options:{responsive:!0,plugins:{legend:{display:!0,labels:{font:{size:l}}}},scales:{r:{ticks:{beginAtZero:!0,stepSize:i,max:d,min:c,color:e.dataset.ticksColor||"#666",backdropColor:e.dataset.ticksBackdropColor||void 0}}}}});
        console.log("Gráfica creada correctamente.");
    }
});
})();
