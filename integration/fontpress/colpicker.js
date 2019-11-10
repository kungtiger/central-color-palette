(function ($, palette) {
    var per_row = 11, color_size = 23;

    // Ceference to old init
    var _colpick = $.fn.colpick;
    $.fn.colpick = function () {
        // Call old init
        _colpick.apply(this, arguments);

        // Make sure we got a palette
        if (!$.isArray(palette) || !palette.length) {
            return this;
        }
        
        return this.each(function () {
            // Let's get our colpick wrapper
            var $this = $(this), id = $this.data('colpickId');
            if (!id) {
                return;
            }
            var $colpick = $('#' + id);

            // Add palette wrapper
            var $palette = $('<div class="colpick_palette"></div>').appendTo($colpick);
            $.each(palette, function () {
                var color = $('<div class="colpick_palette_color"/>');
                color.data('color', this.color);
                color.css('backgroundColor', this.color);
                color.attr('title', this.name);
                $palette.append(color);
            });

            // Add padding to colpick wrapper
            $colpick.css('paddingBottom', Math.ceil(palette.length / per_row) * color_size);

            // Delegate mousedown events for palette colors
            $palette.on('mousedown', '.colpick_palette_color', function () {
                // Use Colpick's API to set color
                $this.colpickSetColor($(this).data('color'));

                // Simulate a change event
                var instance = $colpick.data('colpick'),
                        hex = $.colpick.hsbToHex(instance.color),
                        rgb = $.colpick.hsbToRgb(instance.color);
                // 5th argument needs to be false or input fields won't update
                var args = [instance, hex, rgb, instance.el, false];
                instance.onChange.apply($colpick.parent(), args);
            });
        });
    };
})(jQuery, kt_fontpress_palette || null);
