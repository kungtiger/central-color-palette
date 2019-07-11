(function ($) {
    if (!$.a8c || !$.a8c.iris) {
        return;
    }

    $.a8c.iris.prototype._addPalettes = function () {
        var $container = this.picker.children('.iris-palette-container');
        if (!$container.length) {
            $container = $('<div class="iris-palette-container"/>').appendTo(this.picker);
        }

        var palette = $.isArray(this.options.palettes) ? this.options.palettes : this._palettes;
        $.each(palette, function (_, set) {
            if (typeof set != 'object') {
                set = {
                    color: set,
                    name: ''
                };
            }

            $('<a class="iris-palette" tabindex="0"/>')
            .data('color', set.color)
            .css('backgroundColor', set.color)
            .attr('title', set.name)
            .appendTo($container);
        });
    };
})(jQuery);