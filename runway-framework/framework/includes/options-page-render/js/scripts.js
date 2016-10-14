(function($) {

    $(function() {

        // Toggle developer mode action
        $('#ToggleDevMode').on('click', function (e) {
            e.preventDefault();
            $('.developerMode').fadeToggle();
        });

        if ($.cookie($('#page-slug').val() + '-activeTab')) {
            $('.nav-tab').removeClass('nav-tab-active');
            $('.nav-tab[data-tabrel="' + ($.cookie($('#page-slug').val() + '-activeTab') + '"]')).addClass('nav-tab-active');
            $('.tab-active').removeClass('tab-active');
            $($.cookie($('#page-slug').val() + '-activeTab')).addClass('tab-active');
        }

        $('.nav-tab').on('click', function () {
            $.cookie($('#page-slug').val() + '-activeTab', $(this).attr('data-tabrel'), {expires: 14});
        });

        $('.tab-controlls a').on('click', function () {
            if (!$(this).hasClass('nav-tab-active')) {
                $('.tab-controlls a').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-active').removeClass('tab-active');
                $($(this).data('tabrel')).addClass('tab-active');
            }

            return false;
        });

        $('body').on('click', '.customize-control-content', function(e) {
            e.stopPropagation();
            e.preventDefault();
        });

        $('.submit input').on('click', function(e){
            var current_index = -1;
            var start_index = -1;
            var current_name = '';

            $('.input-check.custom-data-type').each(function () {
                var $this = $(this);
                var name = $(this).attr('name');
                var matched;

                if (/\[(\d*)\]\[\]$/.test(name)) {
                    matched = name.match(/\[(\d*)\]\[\]$/);
                    name = name.replace(/\[\d*\]\[\]$/, '');
                    if (current_name != name) {
                        current_name = name;
                        current_index = -1;
                        start_index = -1;
                    }

                    if (current_index != matched[1]) {
                        current_index = matched[1];
                        start_index++;
                    }

                    $this.attr('name', name + '[' + start_index + '][]');
                }

                if ($this.attr('data-type') === 'checkbox-bool-type' && /\[\]$/.test(name)) {
                    if (!$this.prop('checked')) {
                        $this.val('false');
                    }
                }
            });

            current_index = -1;
            start_index = -1;
            current_name = '';

            $('.input-radio.custom-data-type').each(function () {
                var $this = $(this);
                var name = $this.attr('name');
                var matched;

                if (/\[(\d*)\]$/.test(name)) {
                    matched = name.match(/\[(\d*)\]$/);
                    name = name.replace(/\[\d*\]$/, '');

                    if (current_name != name) {
                        current_name = name;
                        current_index = -1;
                        start_index = -1;
                    }

                    if (current_index != matched[1]) {
                        current_index = matched[1];
                        start_index++;
                    }

                    $this.attr('name', name + '[' + start_index + ']');
                }
            });

            $('body').find('.custom-data-type').each(function (index, el) {
                var $this = $(this);
                var $el = $(el);
                var name = $el.attr('name');
                var checked;
                var data_section;
                var selected;

                switch ($el.data('type')) {
                    case 'checkbox-type':
                        if (/\[(\d*)\]\[\]$/.test(name)) {
                            checked = $("input[name='" + name + "']:checked");

                            if (checked.length === 0) {
                                data_section = '';

                                if (typeof $el.attr('data-section') !== 'undefined') {
                                    data_section = "data-section='" + $el.attr('data-section') + "'";
                                }

                                $this.attr('name', '').after("<input type='hidden' class='" + $el.attr('class') + "' " +
                                    "value='false' name='" + name + "' " + data_section + " data-type='" + $el.attr('data-type') + "' " +
                                    "/>");
                            }
                        }
                        break;

                    case 'checkbox-bool-type':
                        if (/\[\]$/.test(name)) {
                            data_section = '';

                            if (typeof $el.attr('data-section') !== 'undefined') {
                                data_section = "data-section='" + $el.attr('data-section') + "'";
                            }

                            if (!$this.prop('checked')) {
                                $this.attr('name', '').after("<input type='hidden' class='" + $el.attr('class') + "' " +
                                    "value='false' name='" + name + "' " + data_section + " data-type='" + $el.attr('data-type') + "' " +
                                    "/>");
                            }
                            else {
                                $this.attr('name', '').after("<input type='hidden' class='" + $el.attr('class') + "' " +
                                    "value='true' name='" + name + "' " + data_section + " data-type='" + $el.attr('data-type') + "' " +
                                    "/>");
                            }
                        }
                        break;

                    case 'multi-select-type':
                        if (/\[(\d*)\]\[\]$/.test(name)) {
                            selected = $el.children('option:selected');

                            if (selected.length === 0) {
                                $el.children('option').eq(0).prop('selected', 'selected');
                            }
                        }
                        break;

                    case 'radio-buttons-image':
                    case 'radio-buttons':
                        if (/\[(\d*)\]$/.test(name)) {
                            checked = $("input[name='" + name + "']:checked");

                            if (checked.length === 0) {
                                data_section = '';

                                if (typeof $el.attr('data-section') !== 'undefined') {
                                    data_section = "data-section='" + $el.attr('data-section') + "'";
                                }

                                $("input[name='" + name + "']").attr('name', '');
                                $this.attr('name', '').after("<input type='hidden' class='" + $el.attr('class') + "' " +
                                    "value='false' name='" + name + "' " + data_section + " data-type='" + $el.attr('data-type') + "' " +
                                    "/>");
                            }
                        }
                        break;

                }
            });
        });

    });

    // show 'delete' link only if two and more elements exist
    window.check_inputs_amount = function ($container) {

        $container.one('DOMSubtreeModified', change_listener);
        $container.triggerHandler('DOMSubtreeModified');

        function change_listener() {
            setTimeout(function () {
                var $items = $container.find('[class*="delete_"]');

                if ($items.length > 1) {
                    $items.show();
                } else {
                    $items.hide();
                }

                $container.one('DOMSubtreeModified', change_listener);
            }, 0);
        }
    }

})(jQuery);
