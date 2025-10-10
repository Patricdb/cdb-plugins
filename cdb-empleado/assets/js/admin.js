(function($){
    function getStack(selector){
        var opt = $(selector).find('option:selected');
        return opt.data('stack') || '';
    }
    function updatePreview(){
        var ink = $('#tarjeta_oct_ink').val();
        var bgStart = $('#tarjeta_oct_bg_start').val();
        var bgEnd = $('#tarjeta_oct_bg_end').val();
        var svg = $('#tarjeta_oct_bg_svg').val().trim();
        var bodyStack = getStack('#tarjeta_oct_font_body');
        var headStack = getStack('#tarjeta_oct_font_heading');
        var $preview = $('#cdb-empleado-preview');
        if(!$preview.length){
            return;
        }
        var bg = 'linear-gradient(180deg,'+bgStart+','+bgEnd+')';
        if(svg){
            bg += ',url("data:image/svg+xml;utf8,'+encodeURIComponent(svg)+'")';
        }
        $preview.css({
            'color': ink,
            'background': bg,
            'background-repeat': 'no-repeat',
            'background-size': 'contain',
            'background-position': 'center',
            'font-family': bodyStack
        });
        $preview.find('.cdb-preview-title').css('font-family', headStack);
    }
    $(function(){
        $('.cdb-color-field').wpColorPicker({
            change: updatePreview,
            clear: updatePreview
        });
        $('#tarjeta_oct_font_body, #tarjeta_oct_font_heading').on('change', updatePreview);
        $('#tarjeta_oct_bg_svg').on('input', updatePreview);
        var $svgDisplay = $('#tarjeta_oct_bg_svg_display').hide();
        $('#tarjeta_oct_bg_svg_preview').on('click', function(){
            var svg = $('#tarjeta_oct_bg_svg').val().trim();
            if(!svg){
                $svgDisplay.empty().hide();
                return;
            }
            var doc = new DOMParser().parseFromString(svg, 'image/svg+xml');
            if(doc.documentElement.tagName === 'parsererror'){
                alert('El SVG proporcionado no es válido.');
                return;
            }
            var svgEl = doc.documentElement;
            svgEl.removeAttribute('width');
            svgEl.removeAttribute('height');
            if(!svgEl.hasAttribute('viewBox') && svgEl.hasAttribute('width') && svgEl.hasAttribute('height')){
                var w = svgEl.getAttribute('width'), h = svgEl.getAttribute('height');
                svgEl.setAttribute('viewBox', '0 0 '+w+' '+h);
            }
            svgEl.setAttribute('width', '100%');
            svgEl.setAttribute('height', '100%');
            if(!svgEl.hasAttribute('preserveAspectRatio')){
                svgEl.setAttribute('preserveAspectRatio', 'xMidYMid meet');
            }
            $svgDisplay.html(svgEl.outerHTML);
            $svgDisplay.toggle();
            if($svgDisplay.is(':visible')){
                $svgDisplay.css('display', 'flex');
            }
        });
        updatePreview();
    });
})(jQuery);
