(function($, entries) {
    var MONTH_IN_SECONDS = 108e4 * 13,
    _ = function(id) {
        return document.getElementById(id);
    };
    $(function() {
        var updateView = function() {
            $Editor.removeClass('loading');

            var $all = $Editor.children('.picker');
            $all.removeClass('first-picker last-picker');

            var $active = $all.filter('.picker-active');
            $active.first().addClass('first-picker');
            $active.last().addClass('last-picker');
            $Editor.toggleClass('no-active', !$active.length);
            $('#kt_tab_palette .count').text('(' + $active.length + ')');

            var $inactive = $all.filter('.picker-inactive');
            $inactive.first().addClass('first-picker');
            $inactive.last().addClass('last-picker');
            $Editor.toggleClass('no-inactive', !$inactive.length);
            $('#kt_tab_trash .count').text('(' + $inactive.length + ')');
        },
        switchToTab = function(id) {
            $Tabs.removeClass('tab-active');
            $('#' + id).addClass('tab-active');
            $Editor.toggleClass('show-palette', id == 'kt_tab_palette');
            $Editor.toggleClass('show-trash', id == 'kt_tab_trash');
        },
        getPicker = function(obj) {
            return $(obj).closest('.picker');
        },
        sortUp = function() {
            var $this = getPicker(this);
            if($this.prev().length) {
                $this.after($this.prev());
                updateView();
                return false;
            }
        },
        sortDown = function() {
            var $this = getPicker(this);
            if($this.next().length) {
                $this.before($this.next());
                updateView();
                return false;
            }
        },
        focusPicker = function(e) {
            if($(e.target).is('.picker')) {
                $(this).trigger('focus');
            }
        },
        focusParent = function(e) {
            switch(e.which) {
                case kt.key.ESC:
                    $(this.parentNode).trigger('focus');
                    break;

                case kt.key.ENTER:
                    e.preventDefault();
                    break;
            }
        };

        var $Editor = $('#kt_color_editor');
        $(entries).each(function() {
            var entry = kt_Color_Entry($Editor, this);
            entry.on('change:status remove', updateView);
        });

        $('#kt_add').on('click', function() {
            switchToTab('kt_tab_palette');
            var entry = kt_Color_Entry($Editor, {
                status: kt_Color_Entry.ACTIVE
            });
            entry.on('change:status remove', updateView);
            updateView();
        });

        var $Tabs = $('#kt_toolbar .tab');
        $Tabs.on('mousedown', function() {
            switchToTab(this.id);
        });

        $('#kt_autoname').on('change', function() {
            kt_Color.enableAutoname(this.checked);
            $Editor.toggleClass('autoname', this.checked);
            if(window.wpCookies) {
                var show = this.checked ? 1 : 0;
                window.wpCookies.set('kt_color_grid_autoname', show, MONTH_IN_SECONDS);
            }
        });
        kt_Color.enableAutoname($Editor.hasClass('autoname'));

        $Editor.on('click', '.sort-up', sortUp)
        .on('click', '.sort-down', sortDown)
        .on('mousedown', '.picker', focusPicker)
        .on('keydown', 'input', focusParent);

        if(!$(document.body).hasClass('mobile')) {
            $Editor.sortable({
                placeholder: 'picker-placeholder',
                items: '.picker',
                distance: 2,
                revert: 130,
                stop: function(e, ui) {
                    ui.item.css('zIndex', '').trigger('focus');
                    updateView();
                }
            });
        }


        $('#kt_visual, #kt_customizer').on('change', function() {
            if(this.id == 'kt_visual' && !this.checked && _('kt_type_palette').checked) {
                $('#kt_type_default').prop('checked', true).trigger('change');
            }
        });

        $('input[name="kt_type"]').on('change', function() {
            if(this.value == 'palette') {
                _('kt_visual').checked = true;
            }
            $('#kt_customizer').trigger('change');
        });

        $('#kt_clamp, #kt_clamps').on('mousedown', function() {
            _('kt_spread_odd').checked = true;
        });

        $('#kt_palette_metabox').on('change', '[data-form]', function() {
            $('#' + this.id + '_form').toggleClass('hide-if-js', !this.checked);
        });

        var $ExportForms = $('#kt_backup_metabox .export-format-form');
        $('#kt_export_format').on('change', function() {
            $ExportForms.addClass('hide-if-js');
            var type = $('#kt_export_format [value=' + this.value + ']').data('form');
            if(type) {
                $('#kt_export_' + type + '_form').removeClass('hide-if-js');
            }
        });

        if(window.wpCookies) {
            $('#kt_backup_metabox').on('change', 'input,select', function() {
                var value = this.value;
                if(this.type == 'checkbox') {
                    value = this.checked ? 1 : 0;
                }
                window.wpCookies.set(this.id, value, MONTH_IN_SECONDS);
            });
        }

        var randomIndex = Math.floor(Math.random() * window.ntc.names.length);
        var randomPreview = window.ntc.names[randomIndex];
        var randomPreviewName = randomPreview[1].replace(/[^A-Za-z0-9_-]/, '');
        var randomPreviewColor = '#' + randomPreview[0];
        var randomPreviewAlpha = Math.floor(Math.random() * 101);
        $.each(['css', 'css_vars', 'scss'], function() {
            var base = 'kt_export_' + this;
            var color = new kt_Color(randomPreviewColor);
            color.setAlpha(randomPreviewAlpha);
            var template = wp.template(base + '_preview');
            var compat = wp.template(base + '_compat_preview');
            var $pre = $('#' + base + '_preview pre');
            var $format = $('#' + base + '_color_format');
            var $compat = $('#' + base + '_color_compat');
            var render = function() {
                $pre.text(($compat.length && $compat.attr('checked') ? compat : template)({
                    key: randomPreviewName,
                    prefix: _(base + '_prefix').value,
                    suffix: _(base + '_suffix').value,
                    color: color.setType($format.val()).toString(),
                    hex: color.getHEX()
                }));
            };
            $('#' + base + '_form').on('change', 'input,select', render);
            render();
        });

        $('#kt_backup_metabox').on('change', '.export-color-format', function() {
            $('#' + this.id.substring(0, this.id.length - 6) + 'compat_wrap').toggleClass('hide-if-js', $(this).val() == 'hex');
        });

        $('#kt_upload').on('change', function() {
            $('#kt_upload_label').addClass('disabled');
            _('kt_action').value = 'import';
            this.form.submit();
        });

        postboxes.add_postbox_toggles(pagenow);
        updateView();
    });
})(jQuery, window.kt_central_palette_entries || []);