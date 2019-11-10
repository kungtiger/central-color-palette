(function($) {
    var entryTemplate, pickerTemplate, typeSelectTemplate, $document,
    radius = 84,
    square = 100,
    shift = 97,
    dragging = false,
    off = 13,
    defaults = {
        color: '#000000',
        alpha: 100,
        name: '',
        index: 0,
        type: kt_Color.HEX,
        status: 1
    };

    var Entry = function(container, options) {
        if(!(this instanceof Entry)) {
            return new Entry(container, options);
        }

        if(!entryTemplate) {
            entryTemplate = wp.template('kt_color_entry');
            pickerTemplate = $('#tmpl-kt_color_picker').html();
            typeSelectTemplate = $('#tmpl-kt_color_type_select').html();
            $document = $(document);
        }
        var init = $.extend({}, defaults, options);
        var self = this;
        this.color = kt_Color(init.color);
        this.color.setAlpha(init.alpha);
        this.color.setName(init.name);
        this.color.enableAutoname(init.name == '');
        this.color._type = init.type;
        this.$picker = $(pickerTemplate).appendTo(document.body);
        this.$type = $(typeSelectTemplate).appendTo(document.body);
        this.$type.children('[data-type=' + init.type + ']').addClass('active');
        this.$el = $(entryTemplate({
            index: init.index,
            status: init.status
        })).appendTo(container);
        var background = kt_Color('#FF0000');
        var set = 'sl';

        this.el = {
            $colorBtn: self.$el.children('.color'),
            $rgbSample: self.$el.find('.sample .rgb'),
            $rgbaSample: self.$el.find('.sample .rgba'),
            $colorInput: self.$el.children('.color-input'),
            $alphaInput: self.$el.children('.alpha-input'),
            $hex: self.$el.children('.color-hex'),
            $type: self.$el.children('.color-type'),
            $typeSelect: self.$el.children('.type-select'),
            $status: self.$el.children('.color-status'),
            $activateBtn: self.$el.find('.buttons .activate'),
            $deactivateBtn: self.$el.find('.buttons .deactivate'),
            $removeBtn: self.$el.find('.buttons .remove'),
            $nameInput: self.$el.children('.name'),
            $autonameBtn: self.$el.children('button.autoname'),
            keyboard: function(e) {
                switch(e.which) {
                    case kt.key.ENTER:
                        self.el.$colorBtn.trigger('focus');
                        return false;
                    case kt.key.ESC:
                        self.$el.trigger('blur');
                        break;
                }
            },
            view: function() {
                self.el.$colorInput.val(self.color.toString());
                self.el.$alphaInput.val(self.color.getAlpha());
                self.el.$rgbSample.css('backgroundColor', self.color.getHEX());
                self.el.$rgbaSample.css('backgroundColor', self.color.getRGBA(true));
                self.el.$nameInput.val(self.color.getName()).toggleClass('autoname', self.color.autonameEnabled());
                self.el.$type.val(self.color._type);
                self.el.$hex.val(self.color.getHEX());
            }
        };

        this.picker = {
            $wheel: self.$picker.children('.wheel'),
            marker: {
                $hue: self.$picker.children('.h-marker'),
                $sl: self.$picker.children('.sl-marker'),
                $alpha: self.$picker.children('.a-marker')
            },
            $alpha: self.$picker.children('.alpha'),
            $color: self.$picker.children('.color'),
            keyboard: function(e) {
                var f = kt.key.ctrl(e) ? 5 : 2.5, more = '+' + f, less = '-' + f;
                switch(e.which) {
                    case kt.key.MINUS:
                    case kt.key.MINUS_NUM:
                        self.color.setHue(less);
                        return false;
                    case kt.key.PLUS:
                    case kt.key.PLUS_NUM:
                        self.color.setHue(more);
                        return false;
                    case kt.key.UP:
                        self.color.setLightness(more);
                        return false;
                    case kt.key.DOWN:
                        self.color.setLightness(less);
                        return false;
                    case kt.key.LEFT:
                        self.color.setSaturation(more);
                        return false;
                    case kt.key.RIGHT:
                        self.color.setSaturation(less);
                        return false;
                    case kt.key.SPACE:
                        self.el.$colorBtn.trigger($.Event('click', {
                            which: kt.mouse.LEFT
                        }));
                        return false;
                    case kt.key.ESC:
                        if(self.picker.isHidden()) {
                            self.$el.trigger('focus');
                        } else {
                            self.picker.hide();
                        }
                        return false;
                }
            },
            view: function() {
                var angle = self.color.getHue() * 6.28 / 360;
                self.picker.marker.$hue.css({
                    left: Math.round(Math.sin(angle) * radius) + shift,
                    top: Math.round(-Math.cos(angle) * radius) + shift
                });
                self.picker.marker.$sl.css({
                    left: shift - Math.round(square * (self.color.getSaturation() / 100 - .5)),
                    top: shift - Math.round(square * (self.color.getLightness() / 100 - .5))
                });
                self.picker.marker.$alpha.css('top', off + radius * (100 - self.color.getAlpha()) / 50);
                background.setHue(self.color.getHue());
                self.picker.$color.css('backgroundColor', background.getHEX());
                self.picker.$alpha.css('backgroundColor', self.color.getHEX());
            },
            coords: function(e) {
                var offset = self.picker.$wheel.offset();
                return {
                    x: (e.pageX - offset.left) - shift,
                    y: (e.pageY - offset.top) - shift
                };
            },
            mousedown: function(e) {
                if(e.which != kt.mouse.LEFT) {
                    return false;
                }
                if(!dragging) {
                    $document.on(self.picker.mouse);
                    dragging = true;
                }
                var pos = self.picker.coords(e);
                set = 'sl';
                if(pos.x > shift + 1) {
                    set = 'alpha';
                } else if(Math.max(Math.abs(pos.x), Math.abs(pos.y)) * 2 > square) {
                    set = 'hue';
                }
                self.picker.mousemove(e);
                return false;
            },
            mousemove: function(e) {
                var pos = self.picker.coords(e);
                switch(set) {
                    case 'hue':
                        self.color.setHue(Math.atan2(pos.x, -pos.y) * 360 / 6.28);
                        break;
                    case 'sl':
                        self.color.setHSL([self.color.getHue(), -100 * (pos.x / square) + 50, -100 * (pos.y / square) + 50]);
                        break;
                    case 'alpha':
                        self.color.setAlpha(-50 * (pos.y / radius) + 50);
                        break;
                }
            },
            mouseup: function() {
                $document.off(self.picker.mouse);
                dragging = false;
            },
            isHidden: function() {
                return self.$picker.hasClass('hidden');
            },
            hide: function() {
                self.$picker.addClass('hidden');
                $document.off('mousedown', self.picker.autohide);
            },
            autohide: function(e) {
                if(!$(e.target).closest(self.el.$colorBtn).length) {
                    self.picker.hide();
                }
            },
            toggle: function(e) {
                if(kt.mouse.LEFT == e.which) {
                    if(self.picker.isHidden()) {
                        $document.on('mousedown', self.picker.autohide);
                        self.$picker.removeClass('hidden').position({
                            of: this,
                            at: 'left bottom',
                            my: 'left top-1px'
                        });
                    } else {
                        self.picker.hide();
                    }
                }
            }
        };
        this.picker.mouse = {
            mousemove: self.picker.mousemove,
            mouseup: self.picker.mouseup
        };
        this.type = {
            isHidden: function() {
                return self.$type.hasClass('hidden');
            },
            hide: function() {
                self.$type.addClass('hidden');
                self.el.$typeSelect.removeClass('active');
                $document.off('mousedown', self.type.autohide);
            },
            autohide: function(e) {
                if(!$(e.target).closest([self.$type[0], self.el.$typeSelect[0]]).length) {
                    self.type.hide();
                }
            },
            toggle: function(e) {
                if(kt.mouse.LEFT == e.which) {
                    if(self.type.isHidden()) {
                        $document.on('mousedown', self.type.autohide);
                        self.el.$typeSelect.addClass('active');
                        self.$type.removeClass('hidden').position({
                            of: this,
                            at: 'right bottom',
                            my: 'right top-1px'
                        });
                    } else {
                        self.type.hide();
                    }
                }
            },
            select: function(e) {
                var $this = $(e.target);
                var type = $this.data('type');
                $this.siblings().removeClass('active');
                $this.addClass('active');
                self.color.setType(type);
                self.type.hide();
            }
        };

        // add or remove keyboard events when an entry gets or loses focus
        this.$el.on('focus blur', function(e) {
            if(e.type == 'focus' || e.type == 'focusin') {
                self.$el.attr('aria-grabbed', 'true').on('keydown', self.el.keyboard);
            } else {
                self.$el.attr('aria-grabbed', 'false').off('keydown', self.el.keyboard);
            }
        });

        // mouse events for the picker
        this.$picker.on('mousedown', this.picker.mousedown);

        // when the color changes update the UI
        this.color.on('change', this.el.view);
        this.color.on('change', this.picker.view);

        // toggle the picker then the color preview button is pressed
        this.el.$colorBtn.on('mousedown', this.picker.toggle);

        // add or remove keyboard events when the color preview putton gets or looses focus
        this.el.$colorBtn.on('focus blur', function(e) {
            if(e.type == 'focus' || e.type == 'focusin') {
                self.el.$colorBtn.on('keydown', self.picker.keyboard);
            } else {
                self.picker.hide();
                self.el.$colorBtn.off('keydown', self.picker.keyboard);
            }
        });

        // when the color input changes parse its value
        this.el.$colorInput.on('change', function() {
            self.color.parse(this.value);
            this.value = self.color.toString();
        });

        // when the alpha input changes update the color object
        this.el.$alphaInput.on('change', function() {
            self.color.setAlpha(this.value);
            this.value = self.color.getAlpha();
        });

        // when the name input changes toggle autonaming and update the color name
        this.el.$nameInput.on('change', function() {
            var name = $.trim(this.value);
            self.color.enableAutoname(name == '');
            self.color.setName(name);
        });

        // toggle the type select then the type button is pressed
        this.el.$typeSelect.on('mousedown', this.type.toggle);
        this.$type.on('mousedown', this.type.select);

        // when the autoname button is pressed autoname the color
        this.el.$autonameBtn.on('click', function() {
            self.color.autoname(true);
        });

        this.el.$activateBtn.on('click', function() {
            self.activate();
        });
        this.el.$deactivateBtn.on('click', function() {
            self.deactivate();
        });
        this.el.$removeBtn.on('click', function() {
            self.remove();
        });

        // initial UI update
        this.picker.view();
        this.el.view();
    };

    Entry.ACTIVE = 1;
    Entry.INACTIVE = 2;

    $.extend(Entry.prototype, {
        activate: function() {
            this.$el.removeClass('picker-inactive').addClass('picker-active');
            this.el.$status.val(Entry.ACTIVE);
            this.trigger('change:status');
            this.trigger('change');
            return this;
        },
        deactivate: function() {
            this.$el.removeClass('picker-active').addClass('picker-inactive');
            this.el.$status.val(Entry.INACTIVE);
            this.trigger('change:status');
            this.trigger('change');
            return this;
        },
        remove: function() {
            this.$el.remove();
            this.trigger('remove');
            return this;
        }
    });
    kt.implement(Entry, 'Event');
    window.kt_Color_Entry = Entry;
})(jQuery);