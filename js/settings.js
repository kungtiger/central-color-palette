/*
 * kt_Color
 * Simple Color Class for RGB, HEX and HSL conversion
 */
(function () {
    var SHIFT = /\s*[+-]\d*\.?\d+/,
     round = function (x, p) {
         var e = Math.pow(10, parseInt(p) || 0);
         return Math.round(x * e) / e;
     },
     limit = function (min, x, max) {
         return Math.max(min, Math.min(x, max));
     },
     hex2dec = function (color) {
         color._dec = parseInt(color._hex.substr(1), 16);
     },
     dec2hex = function (color) {
         var hex = color._dec.toString(16);
         color._hex = '#000000'.substr(0, 7 - hex.length) + hex.toUpperCase();
     },
     dec2rgb = function (color) {
         color._rgb = [color._dec >> 16, (color._dec >> 8) & 0xFF, color._dec & 0xFF];
     },
     rgb2dec = function (color) {
         color._dec = color._rgb[0] << 16 | (color._rgb[1] << 8) & 0xFFFF | color._rgb[2];
     },
     rgb2hsl = function (color) {
         var r = color._rgb[0] / 255, g = color._rgb[1] / 255, b = color._rgb[2] / 255,
          min = Math.min(r, Math.min(g, b)),
          max = Math.max(r, Math.max(g, b)),
          e = max + min,
          d = max - min,
          l = e / 2,
          s = l > 0 && l < 1 ? d / (l < .5 ? e : (2 - e)) : 0,
          h = d > 0 ? max == r ? (g - b) / d + (g < b ? 6 : 0) : max == g ? (b - r) / d + 2 : max == b ? (r - g) / d + 4 : 0 : 0;
         color._hsl = [round(h * 60, 2), round(s * 100, 2), round(l * 100, 2)];
     },
     hue2rgb = function (p, q, h) {
         h += h < 0 ? 1 : (h > 1 ? -1 : 0);
         return round((h * 6 < 1 ? p + (q - p) * h * 6 : h * 2 < 1 ? q : h * 3 < 2 ? p + (q - p) * (2 / 3 - h) * 6 : p) * 255);
     },
     hsl2rgb = function (color) {
         var h = color._hsl[0] / 360, s = color._hsl[1] / 100, l = color._hsl[2] / 100;
         if (s == 0) {
             l = round(l * 255);
             color._rgb = [l, l, l];
         } else {
             var q = l < .5 ? l * (1 + s) : l + s - l * s, p = 2 * l - q;
             color._rgb = [hue2rgb(p, q, h + 1 / 3), hue2rgb(p, q, h), hue2rgb(p, q, h - 1 / 3)];
         }
     },
     validate = function (prop, x) {
         if (x == null) {
             return false;
         }
         switch (prop) {
             case 'alpha':
                 return limit(0, parseInt(x), 100);
             case 'rgb':
                 return limit(0, parseInt(x), 255);
             case 'h':
                 return ((parseFloat(x) % 360) + 360) % 360;
             case 'sl':
                 return limit(0, parseFloat(x), 100);
             case 'hex':
                 var hex = x.match(/#([0-9a-f]{6}|[0-9a-f]{3})/i);
                 if (hex) {
                     hex = hex[1].toUpperCase();
                     if (hex.length == 3) {
                         hex = hex.replace(/([0-9A-F])/g, '$1$1');
                     }
                     return '#' + hex;
                 }
         }
         return false;
     },
     rgb = ['red', 'green', 'blue'],
     hsl = ['hue', 'saturation', 'lightness'],
     kt_Color = function (hex, alpha) {
         this._rgb = [0, 0, 0];
         this._hsl = [0, 0, 0];
         this._hex = '#000000';
         this._dec = 0;
         this._alpha = 100;
         this._event = {};
         this.hex(hex, alpha);
     };
    update = function (color, type) {
        switch (type) {
            case 'rgb':
                rgb2dec(color);
                dec2hex(color);
                rgb2hsl(color);
                break;
            case 'hsl':
                hsl2rgb(color);
                rgb2dec(color);
                dec2hex(color);
                break;
            case 'hex':
                hex2dec(color);
                dec2rgb(color);
                rgb2hsl(color);
                break;
        }
        color.trigger('change');
    };
    kt_Color.prototype.rgb = function (rgb, alpha) {
        if (rgb == null) {
            return this._rgb;
        }
        var r = validate('rgb', rgb[0]), g = validate('rgb', rgb[1]), b = validate('rgb', rgb[2]);
        if (r === false || g === false || b === false) {
            return this;
        }
        if (this._rgb[0] != r || this._rgb[1] != g || this._rgb[2] != b) {
            this._rgb = [r, g, b];
            this.alpha(alpha, true);
            update(this, 'rgb');
        } else {
            this.alpha(alpha);
        }
        return this;
    };
    kt_Color.prototype.hsl = function (hsl, alpha) {
        if (hsl == null) {
            return this._hsl;
        }
        var h = validate('h', hsl[0]), s = validate('sl', hsl[1]), l = validate('sl', hsl[2]);
        if (h === false || s === false || l === false) {
            return this;
        }
        if (this._hsl[0] != h || this._hsl[1] != s || this._hsl[2] != l) {
            this._hsl = [h, s, l];
            this.alpha(alpha, true);
            update(this, 'hsl');
        } else {
            this.alpha(alpha);
        }
        return this;
    };
    kt_Color.prototype.hex = function (hex, alpha) {
        if (hex == null) {
            return this._hex;
        }
        var x = validate('hex', hex);
        if (x && this._hex != x) {
            this._hex = x;
            this.alpha(alpha, true);
            update(this, 'hex');
        } else {
            this.alpha(alpha);
        }
        return this;
    };
    kt_Color.prototype.alpha = function (alpha, silent) {
        if (alpha == null) {
            return this._alpha;
        }
        var x = validate('alpha', alpha);
        if (x === false) {
            return this;
        }
        if (this._alpha != x) {
            this._alpha = x;
            !silent && update(this, 'alpha');
        }
        return this;
    };
    kt_Color.prototype.rgba = function () {
        return 'rgba(' + this._rgb.join(',') + ',' + round(this._alpha / 100, 2) + ')';
    };
    for (var i = 0; i < 3; i++) {
        kt_Color.prototype[rgb[i]] = (function (i) {
            return function (x) {
                if (x == null) {
                    return this._rgb[i];
                }
                var value = x;
                if (typeof x == 'string' && x.match(SHIFT)) {
                    value = this._rgb[i] + parseInt(x);
                }
                value = validate('rgb', value);
                if (value !== false && value != this._rgb[i]) {
                    this._rgb[i] = value;
                    update(this, 'rgb');
                }
                return this;
            };
        })(i);
        kt_Color.prototype[hsl[i]] = (function (i) {
            return function (x) {
                if (x == null) {
                    return this._hsl[i];
                }
                var value = x;
                if (typeof x == 'string' && x.match(SHIFT)) {
                    value = this._hsl[i] + parseFloat(x);
                }
                value = validate(i == 0 ? 'h' : 'sl', value);
                if (value !== false && this._hsl[i] != value) {
                    this._hsl[i] = value;
                    update(this, 'hsl');
                }
                return this;
            };
        })(i);
    }
    kt_Color.prototype.on = function (event, fn) {
        if (!this._event[event]) {
            this._event[event] = [];
        }
        this._event[event].push(fn);
        return this;
    };
    kt_Color.prototype.off = function (event, fn) {
        if (this._event[event]) {
            var i = this._event[event].length;
            while (i--) {
                if (this._event[event][i] == fn) {
                    this._Event[event].splice(i, 1);
                    break;
                }
            }
        }
        return this;
    };
    kt_Color.prototype.trigger = function (event) {
        if (this._event[event]) {
            var args = [].slice.call(arguments, 1);
            for (var i = 0, l = this._event[event].length; i < l; i++) {
                this._event[event][i].apply(this, args);
            }
        }
        return this;
    };
    kt_Color.prototype.toString = function () {
        return this._hex;
    };
    window.kt_Color = kt_Color;
})();


/*
 * kt_Color_Picker
 */
(function ($) {
    var html = '<div class="color"/><div class="wheel"/><div class="overlay"/><div class="alpha"><div class="gradient"/></div><div class="h-marker marker"/><div class="sl-marker marker"/><div class="a-marker marker"/>',
     radius = 84, square = 100, shift = 97, dragging = false, off = 13,
     fixAlpha = function () {
         var image = this.currentStyle.backgroundImage;
         if (image != 'none') {
             $(this).css({
                 backgroundImage: 'none',
                 filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod=crop,src='" + image.substring(5, image.length - 2) + "')"
             });
         }
     },
     kt_Color_Picker = function (container) {
         var $container = $(container).html(html),
          $document = $(document),
          $wheel = $container.children('.wheel'),
          $hue = $container.children('.h-marker'),
          $sl = $container.children('.sl-marker'),
          $a = $container.children('.a-marker'),
          $alpha = $container.children('.alpha'),
          $color = $container.children('.color'),
          set = 'sl',
          color = new kt_Color('#000000'),
          background = new kt_Color('#FF0000', 0),
          updateUI = function () {
              var angle = color._hsl[0] * 6.28 / 360;
              $hue.css({
                  left: Math.round(Math.sin(angle) * radius) + shift,
                  top: Math.round(-Math.cos(angle) * radius) + shift
              });
              $sl.css({
                  left: shift - Math.round(square * (this._hsl[1] / 100 - .5)),
                  top: shift - Math.round(square * (this._hsl[2] / 100 - .5))
              });
              $a.css('top', off + radius * (100 - this._alpha) / 50);
              background.hue(color._hsl[0]);
              $color.css('backgroundColor', background._hex);
              $alpha.css('backgroundColor', background._hex);
          },
          coords = function (e) {
              var offset = $wheel.offset();
              return {
                  x: (e.pageX - offset.left) - shift,
                  y: (e.pageY - offset.top) - shift
              };
          },
          mousedown = function (e) {
              if (e.which != 1) {
                  return false;
              }
              if (!dragging) {
                  $document.on(mouse);
                  dragging = true;
              }
              var pos = coords(e);
              set = 'sl';
              if (pos.x > shift + 1) {
                  set = 'alpha';
              } else if (Math.max(Math.abs(pos.x), Math.abs(pos.y)) * 2 > square) {
                  set = 'hue';
              }
              mousemove(e);
              return false;
          },
          mousemove = function (e) {
              var pos = coords(e);
              switch (set) {
                  case 'hue':
                      color.hue(Math.atan2(pos.x, -pos.y) * 360 / 6.28);
                      break;
                  case 'sl':
                      color.hsl([color._hsl[0], -100 * (pos.x / square) + 50, -100 * (pos.y / square) + 50]);
                      break;
                  case 'alpha':
                      color.alpha(-50 * (pos.y / radius) + 50);
                      break;
              }
          },
          mouseup = function () {
              $document.off(mouse);
              dragging = false;
          },
          mouse = {mousemove: mousemove, mouseup: mouseup};
         if (String(navigator.appVersion).match(/MSIE [0-6]\./)) {
             $container.children().each(fixAlpha);
         }
         color.on('change', updateUI);
         $container.on('mousedown', mousedown);
         return color;
     };
    window.kt_Color_Picker = kt_Color_Picker;
})(jQuery);


/*
 * Color Editor
 */
(function ($, ntc) {
    var key = {
        BACKSPACE: 8, DELETE: 46,
        ENTER: 13,
        ESC: 27,
        SPACE: 32,
        PAGE_UP: 33, PAGE_DOWN: 34,
        END: 35, HOME: 36,
        LEFT: 37, UP: 38,
        RIGHT: 39, DOWN: 40,
        PLUS: 107, PLUS_NUM: 187,
        MINUS: 109, MINUS_NUM: 189
    },
     mouse = {LEFT: 1, MIDDLE: 2, RIGHT: 3},
     _ = function (id) {
         return document.getElementById(id);
     },
     sanitizeHex = function (x) {
         var m = String(x).toUpperCase().match(/^#?([0-9A-F]{6}|[0-9A-F]{3})$/);
         if (m) {
             if (m[1].length == 3) {
                 return '#' + m[1].replace(/([0-9A-F])/g, '$1$1');
             }
             return '#' + m[1];
         }
         return false;
     },
     sanitizeAlpha = function (x) {
         var alpha = parseInt(x);
         if (isNaN(alpha)) {
             return 100;
         }
         return Math.max(0, Math.min(alpha, 100));
     };

    $(function () {
        var pickerIsHidden = function () {
            return $Picker.hasClass('hidden');
        },
         hidePicker = function () {
             $Picker.attr('aria-hidden', 'true').addClass('hidden');
             $document.off('mousedown', autoHide);
         },
         autoHide = function (e) {
             if (!$(e.target).closest($current_color).length) {
                 hidePicker();
             }
         },
         togglePicker = function (e) {
             if (e.which == mouse.LEFT) {
                 if (pickerIsHidden()) {
                     $document.on('mousedown', autoHide);
                     $current_color = $(this);
                     $Picker.attr('aria-hidden', 'false').removeClass('hidden').position({
                         of: this,
                         at: 'left bottom',
                         my: 'left top-2px'
                     });
                 } else {
                     hidePicker();
                 }
             }
         },
         updateUI = function ($color) {
             var hex, rgba, $this;
             if (this instanceof kt_Color && $current_color) {
                 $this = $current_color;
                 hex = this._hex;
                 $this.siblings('.hex').val(hex);
                 $this.siblings('.alpha').val(this._alpha);
                 rgba = this.rgba();
                 console.log('kt_Color: ' + rgba);
             } else if ($color) {
                 $this = $color;
                 hex = $this.siblings('.hex').val();
                 render.hex(hex, $this.siblings('.alpha').val());
                 rgba = render.rgba();
                 console.log('$color: ' + rgba);
             } else {
                 return;
             }
             var $sample = $this.children();
             $sample.children('.rgb').css('background-color', hex);
             $sample.children('.rgba').css('background-color', rgba);
             autoName($this);
         },
         updateColor = function () {
             var $hex = $(this);
             var hex = sanitizeHex(this.value),
              $name = $hex.siblings('.name');
             if (hex) {
                 this.value = hex;
                 updateUI($hex.siblings('.color'));
             } else if ($name.hasClass('autoname')) {
                 $name.val('');
             }
         },
         updateAlpha = function () {
             this.value = sanitizeAlpha(this.value);
             updateUI($(this).siblings('.color'));
         },
         toggleAutoname = function () {
             $(this).toggleClass('autoname', this.value == '');
         },
         autoName = function (field) {
             if (_('kt_autoname').checked) {
                 var $picker = $(field).closest('.picker');
                 var $name = $picker.children('.name');
                 if ($name.hasClass('autoname')) {
                     var hex = $picker.children('.hex').val();
                     var name = ntc.name(hex)[1];
                     if (name.substr(0, 14) == 'Invalid Color:') {
                         name = '';
                     }
                     $name.val(name);
                 }
             }
         },
         initSort = function (e) {
             if ($(e.target).is('.picker')) {
                 if (e.type == 'focusin') {
                     $focus = $(this).attr('aria-grabbed', 'true').addClass('grabbed').on('keydown', doSort);
                 } else {
                     $focus.attr('aria-grabbed', 'false').removeClass('grabbed').off('keydown', doSort);
                 }
             }
         },
         currentPicker = function (e, p) {
             return (e && e.type == 'click') ? $(p).closest('.picker') : $focus;
         },
         sortUp = function (e) {
             var $e = currentPicker(e, this);
             if ($e.prev().length) {
                 $e.after($e.prev());
                 return false;
             }
         },
         sortDown = function (e) {
             var $e = currentPicker(e, this);
             if ($e.next().length) {
                 $e.before($e.next());
                 return false;
             }
         },
         focusNext = function () {
             if ($focus.next().length) {
                 $focus = $focus.next().trigger('focus');
                 return false;
             }
         },
         focusPrev = function () {
             if ($focus.prev().length) {
                 $focus = $focus.prev().trigger('focus');
                 return false;
             }
         },
         doSort = function (e) {
             switch (e.which) {
                 case key.PAGE_UP:
                     return sortUp();
                 case key.PAGE_DOWN:
                     return sortDown();
             }
             if (ctrlPressed(e)) {
                 switch (e.which) {
                     case key.LEFT:
                     case key.UP:
                         return sortUp();
                     case key.RIGHT:
                     case key.DOWN:
                         return sortDown();
                 }
             }
             switch (e.which) {
                 case key.ENTER:
                     $focus.children('.color').trigger('focus');
                     return false;
                 case key.LEFT:
                 case key.UP:
                     return focusPrev();
                 case key.RIGHT:
                 case key.DOWN:
                     return focusNext();
                 case key.END:
                     $focus.appendTo($ColorEditor).trigger('focus');
                     return false;
                 case key.HOME:
                     $focus.prependTo($ColorEditor).trigger('focus');
                     return false;
                 case key.BACKSPACE:
                 case key.DELETE:
                     removePicker();
                     break;
                 case key.ESC:
                     $focus.trigger('blur');
                     break;
             }
         },
         removePicker = function () {
             var $neighbour = $focus.next();
             if (!$neighbour.length) {
                 $neighbour = $focus.prev();
             }
             $focus.remove();
             $neighbour.trigger('focus');
         },
         initAdjustHSL = function (e) {
             if (e.type == 'focusin') {
                 $current_color = $(this).on('keydown', adjustHSL);
                 var hex = $current_color.siblings('.hex').val();
                 var alpha = $current_color.siblings('.alpha').val();
                 color.hex(hex, alpha);
             } else {
                 hidePicker();
                 $current_color.off('keydown', adjustHSL);
                 $current_color = null;
             }
         },
         adjustHSL = function (e) {
             var x = ctrlPressed(e) ? 5 : 2.5,
              inc = '+' + x, dec = '-' + x;
             switch (e.which) {
                 case key.MINUS:
                 case key.MINUS_NUM:
                     color.hue(dec);
                     return false;
                 case key.PLUS:
                 case key.PLUS_NUM:
                     color.hue(inc);
                     return false;
                 case key.UP:
                     color.lightness(inc);
                     return false;
                 case key.DOWN:
                     color.lightness(dec);
                     return false;
                 case key.LEFT:
                     color.saturation(inc);
                     return false;
                 case key.RIGHT:
                     color.saturation(dec);
                     return false;
                 case key.SPACE:
                     $current_color.trigger($.Event('click', {which: 1}));
                     return false;
                 case key.ESC:
                     return esc($current_color);
             }
         },
         esc = function ($target) {
             if (pickerIsHidden()) {
                 $target.parent().trigger('focus');
             } else {
                 hidePicker();
             }
             return false;
         },
         autoRevert = function (e) {
             if (e.type == 'focusin') {
                 var $hex = $(this);
                 if (sanitizeHex(this.value)) {
                     $hex.data('lastValue', this.value);
                 }
                 var autoFill = $hex.data('autoFill');
                 if (autoFill) {
                     clearTimeout(autoFill);
                     $hex.removeData('autoFill');
                 }
             } else if (!sanitizeHex(this.value)) {
                 hidePicker();
                 var $hex = $(this);
                 $hex.data('autoFill', setTimeout(function () {
                     $hex.val($hex.data('lastvalue'));
                     autoName(this);
                 }, 5000));
             }
         },
         focusParent = function (e) {
             switch (e.which) {
                 case key.ESC:
                     $(this.parentNode).trigger('focus');
                     break;
                 case key.ENTER:
                     e.preventDefault();
                     break;
             }
         },
         autoNameOnEnter = function (e) {
             if (e.which == key.ENTER) {
                 e.preventDefault();
                 autoName(this);
             }
         },
         ctrlPressed = function (e) {
             return e.ctrlKey || e.shiftKey || e.metaKey;
         };

        var $document = $(document),
         $Picker = $('#kt_picker'),
         $current_color = null, $focus = $(),
         $Checkboxes = $('#kt_visual, #kt_customizer'),
         $Type = $('input[name="kt_type"]'),
         _Palette = _('kt_type_palette'),
         $ColorEditor = $('#kt_color_editor'),
         list_entry_template = $('#tmpl-kt_list_entry').html(),
         render = new kt_Color(),
         color = kt_Color_Picker($Picker);
        color.on('change', updateUI);

        $Checkboxes.on('change', function () {
            if (this.id == 'kt_visual' && !this.checked && _Palette.checked) {
                $('#kt_type_default').prop('checked', true).trigger('change');
            }
        });

        $Type.on('change', function () {
            if (this.value == 'palette') {
                _('kt_visual').checked = true;
            }
            $('#kt_customizer').trigger('change');
        });
        $('#kt_clamp, #kt_clamps').on('mousedown', function () {
            _('kt_spread_odd').checked = true;
        });

        var MONTH_IN_SECONDS = 108e4 * 13;
        var _Export = _('kt_action_export');
        var $ExportParts = $('#kt_export').children('input');
        $('#kt_export').on('change', 'input[type="checkbox"]', function () {
            _Export.disabled = $ExportParts.filter(':checked').length == 0;
            if (window.wpCookies) {
                window.wpCookies.set('kt_export_' + this.value, this.checked ? 1 : 0, MONTH_IN_SECONDS);
            }
        });

        var _Action = _('kt_action');
        $('#kt_import, #kt_upload').on('change', function () {
            $('#' + this.id + '_label').addClass('disabled');
            _Action.value = $(this).data('action');
            this.form.submit();
        });

        $('#kt_add').on('click', function (e) {
            e.preventDefault();
            $(list_entry_template).appendTo($ColorEditor)
             .children('.hex').prop({
                selectionStart: 1,
                selectionEnd: 7
            }).trigger('focus');
        });

        $('#kt_autoname').on('change', function () {
            $ColorEditor.toggleClass('autoname', this.checked);
            if (window.wpCookies) {
                var show = this.checked ? 1 : 0;
                window.wpCookies.set('kt_color_grid_autoname', show, MONTH_IN_SECONDS);
            }
        });
        $('#kt_alpha').on('change', function () {
            $('#kt_color_grid').toggleClass('support-alpha', this.checked);
        });

        $ColorEditor.on('click', '.remove', function (e) {
            $focus = $(this.parentNode);
            removePicker();
            e.preventDefault();
        })
         .on('click', 'button.autoname', function (e) {
             e.preventDefault();
             autoName(this);
         })
         .on('mousedown', '.picker', function (e) {
             if ($(e.target).is('.picker')) {
                 $(this).trigger('focus');
             }
         })
         .on('focus blur', '.picker', initSort)
         .on('click', '.color', togglePicker)
         .on('focus blur', '.color', initAdjustHSL)
         .on('change', '.hex', updateColor)
         .on('change', '.alpha', updateAlpha)
         .on('focus blur', '.hex', autoRevert)
         .on('click', '.sort-up', sortUp)
         .on('click', '.sort-down', sortDown)
         .on('keydown', 'input', focusParent)
         .on('keydown', '.name', autoNameOnEnter)
         .on('change', '.name', toggleAutoname)
         .sortable({
             placeholder: 'picker-placeholder',
             items: '.picker',
             delay: $(document.body).hasClass('mobile') ? 200 : 0,
             distance: 2,
             revert: 130,
             stop: function (e, ui) {
                 ui.item.css('zIndex', '').trigger('focus');
             }
         });

        postboxes.add_postbox_toggles(pagenow);
    });
})(jQuery, window.ntc);
