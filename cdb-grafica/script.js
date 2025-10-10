jQuery(document).ready(function ($) {
    $('.accordion .accordion-toggle').on('click', function () {
        const item = $(this).closest('.accordion-item, .accordion');
        const content = item.find('> .accordion-content');
        content.slideToggle(300);            // sólo el grupo pulsado
        $(this).toggleClass('open');         // estilo para encabezado activo
        item.toggleClass('open');            // (opcional) marcar el grupo
    });

    $('.cdb-grafica-scores .group-toggle').on('click', function(){
        const section = $(this).closest('tbody');
        const isOpen = section.toggleClass('is-open').hasClass('is-open');
        $(this).attr('aria-expanded', isOpen);
    });
});
