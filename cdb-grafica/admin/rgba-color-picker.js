(function($){
    function parseRGBA(str){
        var m = str.match(/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)(?:\s*,\s*(\d+(?:\.\d+)?))?\)/i);
        if(m){
            return {r:parseInt(m[1],10), g:parseInt(m[2],10), b:parseInt(m[3],10), a:m[4] !== undefined ? parseFloat(m[4]) : 1};
        }
        return null;
    }

    function hexToRgb(hex){
        hex = hex.replace('#','');
        if(hex.length === 3){
            hex = hex.replace(/(.)/g, '$1$1');
        }
        var num = parseInt(hex, 16);
        return {r:(num>>16)&255, g:(num>>8)&255, b:num&255};
    }

    function toRGBA(obj){
        return 'rgba(' + obj.r + ',' + obj.g + ',' + obj.b + ',' + obj.a + ')';
    }

    $.fn.rgbaColorPicker = function(){
        return this.each(function(){
            var $input = $(this);
            var rgba = parseRGBA($input.val()) || {r:0,g:0,b:0,a:1};

            $input.val('rgb(' + rgba.r + ',' + rgba.g + ',' + rgba.b + ')');
            $input.wpColorPicker({change:update, clear:update});

            var $container = $input.closest('.wp-picker-container');
            var $holder = $container.find('.wp-picker-holder');
            var $alphaWrap = $('<div class="rgba-alpha"><input type="range" min="0" max="1" step="0.01" value="'+rgba.a+'" /></div>');
            $alphaWrap.insertAfter($holder);

            $alphaWrap.on('input', 'input', function(){
                rgba.a = parseFloat(this.value);
                update();
            });

            function update(){
                var color = $input.wpColorPicker('color');
                if(color.charAt(0) === '#'){
                    var rgb = hexToRgb(color);
                    rgba.r = rgb.r; rgba.g = rgb.g; rgba.b = rgb.b;
                } else {
                    var c = parseRGBA(color);
                    if(c){ rgba.r = c.r; rgba.g = c.g; rgba.b = c.b; }
                }
                var rgbaString = toRGBA(rgba);
                $input.val(rgbaString);
                $container.find('.wp-color-result').css('background-color', rgbaString);
            }

            update();
        });
    };

    $(function(){
        $('.cdb-color-field').rgbaColorPicker();
    });

})(jQuery);
