jQuery(document).ready(function($){
    function hexToRgb(hex){
        hex = hex.replace('#','');
        if(hex.length === 3){
            hex = hex.split('').map(function(h){return h+h;}).join('');
        }
        var num = parseInt(hex,16);
        return {r:(num>>16)&255,g:(num>>8)&255,b:num&255};
    }
    function luminance(r,g,b){
        var a=[r,g,b].map(function(v){
            v/=255;
            return v<=0.03928 ? v/12.92 : Math.pow((v+0.055)/1.055,2.4);
        });
        return a[0]*0.2126 + a[1]*0.7152 + a[2]*0.0722;
    }
    function contrast(c1,c2){
        var rgb1=hexToRgb(c1), rgb2=hexToRgb(c2);
        var L1=luminance(rgb1.r,rgb1.g,rgb1.b)+0.05;
        var L2=luminance(rgb2.r,rgb2.g,rgb2.b)+0.05;
        return L1>L2?L1/L2:L2/L1;
    }
    function setupRow(row){
        function update(){
            var bg = row.find('input[name="tipos[bg][]"]').val() || '#ffffff';
            var text = row.find('input[name="tipos[text][]"]').val() || '#000000';
            var bcolor = row.find('input[name="tipos[border_color][]"]').val() || bg;
            var bwidth = row.find('input[name="tipos[border_width][]"]').val() || '0px';
            var bradius = row.find('input[name="tipos[border_radius][]"]').val() || '0px';
            var preview = row.find('.cdb-aviso-preview');
            preview.css({
                'background-color': bg,
                'color': text,
                'border': bwidth + ' solid ' + bcolor,
                'border-radius': bradius
            });
            if (bwidth === '0px' || bwidth === '0') {
                preview.css('border-left', '4px solid ' + bcolor);
            } else {
                preview.css('border-left', '');
            }
            var warn = row.find('.cdb-contraste-aviso');
            if (contrast(bg, text) < 4.5) {
                warn.show();
            } else {
                warn.hide();
            }
        }
        row.on('input change', 'input', update);
        update();
    }
    if (typeof $.fn.wpColorPicker !== 'undefined') {
        $('.cdb-color-field').wpColorPicker({ change: function(e){ $(e.target).trigger('input'); } });
    }
    $('#cdb-eventos-tipos tbody tr').each(function(){ setupRow($(this)); });
    $('#cdb-eventos-add-tipo').on('click', function(e){
        e.preventDefault();
        var table = $('#cdb-eventos-tipos').find('tbody');
        var row = '<tr>'+
            '<td><input type="text" name="tipos[slug][]" value="" /></td>'+
            '<td><input type="text" name="tipos[name][]" value="" /></td>'+
            '<td><input type="text" name="tipos[class][]" value="" /></td>'+
            '<td><input type="text" class="cdb-color-field" name="tipos[bg][]" value="" /></td>'+
            '<td><input type="text" class="cdb-color-field" name="tipos[text][]" value="" /></td>'+
            '<td><input type="text" class="cdb-color-field" name="tipos[border_color][]" value="" /></td>'+
            '<td><input type="text" name="tipos[border_width][]" value="0px" class="small-text" /></td>'+
            '<td><input type="text" name="tipos[border_radius][]" value="4px" class="small-text" /></td>'+
            '<td class="cdb-preview-cell"><div class="cdb-aviso cdb-aviso-preview"><strong class="cdb-mensaje-destacado">Texto</strong></div><small class="cdb-contraste-aviso" style="display:none;color:#a00;">Contraste bajo</small></td>'+
            '</tr>';
        table.append(row);
        if (typeof $.fn.wpColorPicker !== 'undefined') {
            table.find('.cdb-color-field').wpColorPicker({ change: function(e){ $(e.target).trigger('input'); } });
        }
        setupRow(table.find('tr').last());
    });
});
