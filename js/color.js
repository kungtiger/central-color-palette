/*
 * kt_Color
 * Simple Color Class for RGB, HEX and HSL conversion
 * Based on https://github.com/moagrius/Color
 */
(function($) {
    var rgb = ['Red', 'Green', 'Blue'],
    hsl = ['Hue', 'Saturation', 'Lightness'],
    SHIFT = /\s*[+-]\d*\.?\d+/,
    IS_HEX = /#([0-9a-f]{3}|[0-9a-f]{6})$/i,
    IS_HSL = /hsla?\((\d{1,3}?),\s*(\d{1,3}%),\s*(\d{1,3}%)(,\s*[01]?\.?\d*)?\)/i,
    IS_RGB = /rgba?\((\d{1,3}%?),\s*(\d{1,3}%?),\s*(\d{1,3}%?)(,\s*[01]?\.?\d*)?\)/i,
    HSL = /hsla?\((\d{1,3}),\s*(\d{1,3})%,\s*(\d{1,3})%(,\s*([01]?\.?\d*))?\)/i,
    RGB = /rgba?\((\d{1,3}%?),\s*(\d{1,3}%?),\s*(\d{1,3}%?)(,\s*([01]?\.?\d*))?\)/i,
    absround = function(x) {
        return (.5 + x) << 0;
    },
    round = function(x, p) {
        var e = Math.pow(10, parseInt(p) || 0);
        return Math.round(x * e) / e;
    },
    limit = function(min, x, max) {
        return Math.max(min, Math.min(x, max));
    },
    each = function(array, fn) {
        var _array = [];
        for(var i = 0, l = array.length; i < l; i++) {
            _array[i] = fn(array[i]);
        }
        return _array;
    },
    validate = function(prop, x) {
        if(x == null) {
            return false;
        }
        switch(prop) {
            case 'decimal':
                return limit(0, parseInt(x), 16777215);
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
                if(hex) {
                    hex = hex[1].toUpperCase();
                    if(hex.length == 3) {
                        hex = hex.replace(/([0-9A-F])/g, '$1$1');
                    }
                    return '#' + hex;
                }
        }
        return false;
    };

    var autonameEnabled = true;
    var Color = function(input) {
        if(!(this instanceof Color)) {
            return new Color(input);
        }

        this._rgb = [0, 0, 0];
        this._hsl = [0, 0, 0];
        this._hex = '#000000';
        this._dec = 0;
        this._alpha = 100;
        this._active = true;
        this._name = '';
        this._autoname = true;
        this._type = Color.HEX;
        this.parse(input);
    };

    PERCENT = /^\d+(\.\d+)*%$/,
    int2percent = function(x) {
        return x * 100 / 255;
    };
    percent2int = function(x) {
        return PERCENT.test(x) ? absround(parseInt(x) * 2.55) : parseInt(x);
    };

    Color.HEX = 1;
    Color.RGB = 2;
    Color.PRGB = 3;
    Color.RGBA = 4;
    Color.PRGBA = 5;
    Color.HSL = 6;
    Color.HSLA = 7;

    Color.Convert = {
        hex2dec: function(color) {
            color._dec = parseInt(color._hex.substr(1), 16);
            return this;
        },
        dec2hex: function(color) {
            var hex = color._dec.toString(16);
            color._hex = '#000000'.substr(0, 7 - hex.length) + hex.toUpperCase();
            return this;
        },
        dec2rgb: function(color) {
            color._rgb = [color._dec >> 16, (color._dec >> 8) & 0xFF, color._dec & 0xFF];
            return this;
        },
        rgb2dec: function(color) {
            color._dec = color._rgb[0] << 16 | (color._rgb[1] << 8) & 0xFFFF | color._rgb[2];
            return this;
        },
        rgb2hsl: function(color) {
            var r = color._rgb[0] / 255, g = color._rgb[1] / 255, b = color._rgb[2] / 255,
            min = Math.min(r, Math.min(g, b)),
            max = Math.max(r, Math.max(g, b)),
            e = max + min,
            d = max - min,
            l = e / 2,
            s = l > 0 && l < 1 ? d / (l < .5 ? e : (2 - e)) : 0,
            h = d > 0 ? max == r ? (g - b) / d + (g < b ? 6 : 0) : max == g ? (b - r) / d + 2 : max == b ? (r - g) / d + 4 : 0 : 0;
            color._hsl = [round(h * 60, 2), round(s * 100, 2), round(l * 100, 2)];
            return this;
        },
        hue2rgb: function(p, q, h) {
            h += h < 0 ? 1 : (h > 1 ? -1 : 0);
            return round((h * 6 < 1 ? p + (q - p) * h * 6 : h * 2 < 1 ? q : h * 3 < 2 ? p + (q - p) * (2 / 3 - h) * 6 : p) * 255);
        },
        hsl2rgb: function(color) {
            var h = color._hsl[0] / 360,
            s = color._hsl[1] / 100,
            l = color._hsl[2] / 100;
            if(s == 0) {
                l = round(l * 255);
                color._rgb = [l, l, l];
            } else {
                var q = l < .5 ? l * (1 + s) : l + s - l * s,
                p = 2 * l - q;
                color._rgb = [this.hue2rgb(p, q, h + 1 / 3), this.hue2rgb(p, q, h), this.hue2rgb(p, q, h - 1 / 3)];
            }
            return this;
        }
    };

    $.extend(Color, {
        autoname: function(color, force) {
            if(force || (autonameEnabled && color.autonameEnabled())) {
                var name = ntc.name(color._hex);
                if(name === false) {
                    return '';
                }
                return name;
            }
            return false;
        },
        autonameEnabled: function() {
            return autonameEnabled;
        },
        enableAutoname: function(state) {
            autonameEnabled = !!state;
        }
    });
    Color.Update = function(color, property) {
        Color.Update[property](color);
        Color.Update.autoname(color);
        color.trigger('change:' + property);
        color.trigger('change');
    };
    $.extend(Color.Update, {
        autoname: function(color) {
            var name = Color.autoname(color);
            if(name === false) {
                return;
            }
            color._name = name;
        },
        decimal: function(color) {
            Color.Convert.dec2hex(color).dec2rgb(color).rgb2hsl(color);
        },
        rgb: function(color) {
            Color.Convert.rgb2dec(color).dec2hex(color).rgb2hsl(color);
        },
        hsl: function(color) {
            Color.Convert.hsl2rgb(color).rgb2dec(color).dec2hex(color);
        },
        hex: function(color) {
            Color.Convert.hex2dec(color).dec2rgb(color).rgb2hsl(color);
        }
    });

    $.extend(Color.prototype, {
        parse: function(x) {
            if(typeof x == 'undefined') {
                return this;
            }

            if(x instanceof Color) {
                this.copy(x);
            } else if(IS_HEX.test(x)) {
                this._type = Color.HEX;
                this.setHEX(x);
            } else if(IS_RGB.test(x)) {
                var parts = x.match(RGB);
                var alpha = parseFloat(parts[5]);
                if(isNaN(alpha)) {
                    alpha = 1;
                }
                this._type = (PERCENT.test(parts[1]) ? Color.PRGB : Color.RGB) + (parts[5] ? 2 : 0);
                this.setRGB([
                    percent2int(parts[1]),
                    percent2int(parts[2]),
                    percent2int(parts[3])
                ], alpha * 100);

            } else if(IS_HSL.test(x)) {
                var parts = x.match(HSL);
                var alpha = parseFloat(parts[5]);
                if(isNaN(alpha)) {
                    alpha = 1;
                }
                this._type = parts[5] ? Color.HSLA : Color.HSL;
                this.setHSL([
                    parseInt(parts[1]),
                    parseInt(parts[2]),
                    parseInt(parts[3])
                ], alpha * 100);
            }

            return this;
        },
        copy: function(x) {
            if(x instanceof Color) {
                this._dec = x._dec;
                this._alpha = x._alpha;
                Color.Update(this, 'decimal');
            }
            return this;
        },
        clone: function() {
            return new Color(this._dec).setAlpha(this._alpha);
        },
        getName: function() {
            return this._name;
        },
        setName: function(newName) {
            if(this._name != newName) {
                this._name = newName;
                this.trigger('change:name');
                this.trigger('change');
            }
            return this;
        },
        autoname: function(force) {
            var newName = Color.autoname(this, force);
            if(newName === false) {
                return this;
            }
            return this.setName(newName);
        },
        enableAutoname: function(state) {
            this._autoname = !!state;
            return this;
        },
        autonameEnabled: function() {
            return this._autoname;
        },
        setType: function(newType) {
            if(typeof newType == 'string') {
                switch(newType) {
                    case 'rgb':
                        newType = Color.RGB;
                        break;
                    case 'rgba':
                        newType = Color.RGBA;
                        break;
                    case 'hsl':
                        newType = Color.HSL;
                        break;
                    case 'hsla':
                        newType = Color.HSLA;
                        break;
                    case 'hex':
                        newType = Color.HEX;
                        break;
                    default:
                        return this;
                }
            }

            var x = absround(newType);
            if(x >= Color.HEX || x <= Color.HSLA) {
                this._type = x;
                this.trigger('change');
            }
            return this;
        },
        getType: function() {
            return this._type;
        },
        setRGB: function(rgb, alpha) {
            var r = validate("rgb", rgb[0]),
            g = validate("rgb", rgb[1]),
            b = validate("rgb", rgb[2]);
            if(r === false || g === false || b === false) {
                return this;
            }
            if(this._rgb[0] != r || this._rgb[1] != g || this._rgb[2] != b) {
                this._rgb = [r, g, b];
                this.setAlpha(alpha, true);
                Color.Update(this, 'rgb');
            } else {
                this.setAlpha(alpha);
            }
            return this;
        },
        setHSL: function(hsl, alpha) {
            var h = validate("h", hsl[0]),
            s = validate("sl", hsl[1]),
            l = validate("sl", hsl[2]);
            if(h === false || s === false || l === false) {
                return this;
            }
            if(this._hsl[0] != h || this._hsl[1] != s || this._hsl[2] != l) {
                this._hsl = [h, s, l];
                this.setAlpha(alpha, true);
                Color.Update(this, 'hsl');
            } else {
                this.setAlpha(alpha);
            }
            return this;
        },
        getHEX: function() {
            return this._hex;
        },
        setHEX: function(hex, alpha) {
            var x = validate('hex', hex);
            if(x && this._hex != x) {
                this._hex = x;
                this.setAlpha(alpha, true);
                Color.Update(this, 'hex');
            } else {
                this.setAlpha(alpha);
            }
            return this;
        },
        getAlpha: function() {
            return this._alpha;
        },
        setAlpha: function(newValue, _silent) {
            var x = validate('alpha', newValue);
            if(x === false) {
                return this;
            }
            if(this._alpha != x) {
                this._alpha = x;
                !_silent && this.trigger('change');
            }
            return this;
        },
        getRGB: function(asString, percent) {
            if(!asString) {
                return this._rgb;
            }
            if(percent) {
                var c = each(each(this._rgb, int2percent), absround);
                return 'rgb(' + c.join('%,') + '%)';
            }
            return 'rgb(' + this._rgb.join(',') + ')';
        },
        getRGBA: function(asString, percent) {
            if(!asString) {
                return this._rgb.slice().push(this._alpha);
            }
            if(percent) {
                var c = [absround(this._rgb[0] * 100 / 255), absround(this._rgb[1] * 100 / 255), absround(this._rgb[2] * 100 / 255), round(this._alpha / 100, 2)];
                return 'rgba(' + c.join('%,') + ')';
            }
            return 'rgba(' + this._rgb.join(',') + ',' + round(this._alpha / 100, 2) + ')';
        },
        getHSL: function(asString, alpha) {
            if(!asString) {
                return this._hsl;
            }
            var c = each(this._hsl, absround);
            if(alpha) {
                return 'hsla(' + c[0] + ',' + c[1] + '%,' + c[2] + '%,' + round(this._alpha / 100, 2) + ')';
            }
            return 'hsl(' + c[0] + ',' + c[1] + '%,' + c[2] + '%)';
        },
        toString: function() {
            switch(this._type) {
                case Color.RGB:
                    return this.getRGB(true);
                case Color.PRGB:
                    return this.getRGB(true, true);
                case Color.RGBA:
                    return this.getRGBA(true);
                case Color.PRGBA:
                    return this.getRGBA(true, true);
                case Color.HSL:
                    return this.getHSL(true);
                case Color.HSLA:
                    return this.getHSL(true, true);
            }
            return this._hex;
        }
    });
    for(var i = 0; i < 3; i++) {
        Color.prototype['get' + rgb[i]] = (function(i) {
            return function() {
                return this._rgb[i];
            };
        })(i);
        Color.prototype['set' + rgb[i]] = (function(i) {
            return function(x) {
                var value = x;
                if(typeof x == 'string' && x.match(SHIFT)) {
                    value = this._rgb[i] + parseInt(x);
                }
                value = validate('rgb', value);
                if(value !== false && value != this._rgb[i]) {
                    this._rgb[i] = value;
                    Color.Update(this, 'rgb');
                }
                return this;
            };
        })(i);
        Color.prototype['get' + hsl[i]] = (function(i) {
            return function() {
                return this._hsl[i];
            };
        })(i);
        Color.prototype['set' + hsl[i]] = (function(i) {
            return function(x) {
                var value = x;
                if(typeof x == 'string' && x.match(SHIFT)) {
                    value = this._hsl[i] + parseFloat(x);
                }
                value = validate(i == 0 ? 'h' : 'sl', value);
                if(value !== false && this._hsl[i] != value) {
                    this._hsl[i] = value;
                    Color.Update(this, 'hsl');
                }
                return this;
            };
        })(i);
    }
    kt.implement(Color, 'Event');
    window.kt_Color = Color;
})(jQuery);