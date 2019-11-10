window.kt = (function() {
    var eachEvent = function(event, fn, context) {
        return kt.each(event.split(/\s+/), fn, context);
    };

    var kt = {
        key: {
            BACKSPACE: 8,
            DELETE: 46,
            ENTER: 13,
            ESC: 27,
            SPACE: 32,
            PAGE_UP: 33,
            PAGE_DOWN: 34,
            END: 35,
            HOME: 36,
            LEFT: 37,
            UP: 38,
            RIGHT: 39,
            DOWN: 40,
            PLUS: 107,
            PLUS_NUM: 187,
            MINUS: 109,
            MINUS_NUM: 189,
            ctrl: function(e){
                return e.ctrlKey || e.shiftKey || e.metaKey;
            }
        },
        mouse: {
            LEFT: 1,
            MIDDLE: 2,
            RIGHT: 3
        },
        each: function(array, fn, context) {
            for(var i = 0; i < array.length; i++) {
                fn.call(context || array, array[i], i);
            }
            return context || array;
        },
        implement: function(obj, feature) {
            if(typeof feature == 'string') {
                feature = kt[feature];
            }
            for(var property in feature) {
                obj.prototype[property] = feature[property];
            }
            return obj;
        },
        Event: {
            _event: {},
            on: function(event, fn) {
                return eachEvent(event, function(event) {
                    if(!this._event[event]) {
                        this._event[event] = [];
                    }
                    this._event[event].push(fn);
                }, this);
            },
            off: function(event, fn) {
                return eachEvent(event, function(event) {
                    if(this._event[event]) {
                        var i = this._event[event].length;
                        while(i--) {
                            if(this._event[event][i] == fn) {
                                this._Event[event].splice(i, 1);
                                break;
                            }
                        }
                    }
                }, this);
            },
            trigger: function(event) {
                var args = [].slice.call(arguments, 1);
                return eachEvent(event, function(event) {
                    if(this._event[event]) {
                        for(var i = 0, l = this._event[event].length; i < l; i++) {
                            this._event[event][i].apply(this, args);
                        }
                    }
                }, this);
            }
        }
    };
    return kt;
})();