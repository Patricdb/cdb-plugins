jQuery(document).ready(function($){
    function updateMensajePreview(input){
        var target = $('#' + $(input).data('preview'));
        if(!target.length){return;}
        var field = $(input).attr('name').indexOf('_secundario') !== -1 ? '.cdb-mensaje-secundario' : '.cdb-mensaje-destacado';
        target.find(field).text($(input).val());
    }

    $('.cdb-mensaje-input').on('input', function(){
        updateMensajePreview(this);
    });

    $('select[name^="cdb_empleo_color_"]').on('change', function(){
        var clave = $(this).attr('name').replace('cdb_empleo_color_','');
        var target = $('#preview_' + clave + ' .cdb-aviso');
        if(target.length){
            var tipo = $(this).val();
            target.attr('class','cdb-aviso cdb-aviso--' + tipo + ' cdb-aviso-' + tipo);
        }
    });

    $('.cdb-mostrar-checkbox').on('change', function(){
        var target = $('#' + $(this).data('preview'));
        if(target.length){
            target.toggle(this.checked);
        }
    });

    function updateTipoPreview(row){
        var bg = row.find('input[name$="[bg]"]').val();
        var text = row.find('input[name$="[text]"]').val();
        var borderColor = row.find('input[name$="[border_color]"]').val();
        var borderWidth = row.find('select[name$="[border_width]"]').val();
        var borderRadius = row.find('select[name$="[border_radius]"]').val();
        var preview = row.find('.cdb-aviso-preview');
        preview.css({
            'background-color': bg,
            color: text,
            'border-radius': borderRadius
        });
        if(borderWidth === '0px'){
            preview.css({border:'none','border-left':'4px solid ' + borderColor});
        }else{
            preview.css({'border-left':'none','border': borderWidth + ' solid ' + borderColor});
        }
    }

    $('.cdb-color-picker').wpColorPicker({
        change: function(){
            updateTipoPreview($(this).closest('tr'));
        }
    });
    $('#cdb-tipos-color').on('change','select',function(){
        updateTipoPreview($(this).closest('tr'));
    });
    $('#cdb-tipos-color').on('input','input',function(){
        if($(this).hasClass('wp-color-picker')){return;}
        updateTipoPreview($(this).closest('tr'));
    });

    $('#cdb-tipos-color tbody tr').each(function(){
        updateTipoPreview($(this));
    });

    var table = $('#cdb-tipos-color tbody');
    $('#cdb-add-color-row').on('click', function(e){
        e.preventDefault();
        var index = table.find('tr').length;
        var row = $('<tr />');
        row.append('<td><input type="text" name="tipos_color[' + index + '][slug]" /></td>');
        row.append('<td><input type="text" name="tipos_color[' + index + '][name]" /></td>');
        row.append('<td><input type="text" name="tipos_color[' + index + '][class]" /></td>');
        row.append('<td><input type="text" class="cdb-color-picker" name="tipos_color[' + index + '][bg]" value="#ffffff" /></td>');
        row.append('<td><input type="text" class="cdb-color-picker" name="tipos_color[' + index + '][text]" value="#000000" /></td>');
        row.append('<td><input type="text" class="cdb-color-picker" name="tipos_color[' + index + '][border_color]" value="#ffffff" /></td>');
        row.append('<td><select name="tipos_color[' + index + '][border_width]"><option value="0px">0px</option><option value="1px">1px</option><option value="2px">2px</option><option value="4px">4px</option></select></td>');
        row.append('<td><select name="tipos_color[' + index + '][border_radius]"><option value="0px">0px</option><option value="4px">4px</option><option value="6px">6px</option><option value="8px">8px</option></select></td>');
        row.append('<td><div class="cdb-aviso cdb-aviso-preview"><strong class="cdb-mensaje-destacado">Vista previa</strong><span class="cdb-mensaje-secundario"></span></div></td>');
        row.append('<td><button type="button" class="button cdb-remove-row">&times;</button></td>');
        table.append(row);
        row.find('.cdb-color-picker').wpColorPicker({
            change: function(){
                updateTipoPreview(row);
            }
        });
        updateTipoPreview(row);
    });

    $('#cdb-tipos-color').on('click', '.cdb-remove-row', function(){
        $(this).closest('tr').remove();
    });
});

