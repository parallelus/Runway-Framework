(function ($) {

    $(function () {

        var isWPCustomize = ( wp.customize ) ? true : false;

        function initConditionalDisplay(el, action) {

            switch (action) {
                case 'show':
                    isWPCustomize ? el.closest('.customize-control').hide() : el.closest('tr').hide();
                    break;

                case 'hide':
                    isWPCustomize ? el.closest('.customize-control').show() : el.closest('tr').show();
                    break;

                default:
                    break;
            }

        }

        function runConditionalAction(el, action, dataValue, aliasNewValue) {

            var toggle = dataValue == aliasNewValue;

            if (toggle) {
                switch (action) {
                    case 'show':
                        isWPCustomize ? el.closest('.customize-control').show() : el.closest('tr').show();
                        break;

                    case 'hide':
                        isWPCustomize ? el.closest('.customize-control').hide() : el.closest('tr').hide();
                        break;

                    default:
                        break;
                }
            } else {
                switch (action) {
                    case 'show':
                        isWPCustomize ? el.closest('.customize-control').hide() : el.closest('tr').hide();
                        break;

                    case 'hide':
                        isWPCustomize ? el.closest('.customize-control').show() : el.closest('tr').show();
                        break;

                    default:
                        break;
                }
            }
        }

        function getWatchValue(alias, elWatch) {
            var elWatchValue;
            var elWatchSelected;
            var start;
            var end;
            var elWatchFont;
            var elWatchChecked = [];

            switch (elWatch.attr('data-type')) {
                case 'radio-buttons':
                case 'radio-buttons-image':
                    elWatchValue = $(".custom-data-type[name='" + alias + "']:checked").val();
                    break;

                case 'checkbox-bool-type':
                    elWatchValue = $(".custom-data-type[name='" + alias + "']").is(':checked') ? 'true' : 'false';
                    break;

                case 'checkbox-type':
                    elWatch.each(function () {
                        if ($(this).is(':checked')) {
                            elWatchChecked.push($(this).val());
                        }
                    });
                    elWatchValue = elWatchChecked.join(',');

                    break;

                case 'multi-select-type':
                    elWatchSelected = elWatch.val();

                    elWatchValue = elWatchSelected.join(',');
                    break;

                case 'range-slider':
                    start = $('.slider-value.slider-start-' + alias).text();
                    end = $('.slider-value.slider-end-' + alias).text();

                    elWatchValue = (end == '') ? Math.floor(start) : Math.floor(start) + ',' + Math.floor(end);
                    break;

                case 'code-editor':
                    elWatchValue = elWatch.val();
                    elWatchValue = elWatchValue.replace(/(\r\n|\n|\r)/gm, '');    // remove line breaks
                    break;

                case 'font-select':
                    elWatchFont = [];

                    elWatch.each(function () {
                        elWatchFont.push($(this).val());  // family, style, weight, size, color
                    });
                    elWatchValue = elWatchFont.join(',');
                    break;

                default:
                    elWatchValue = elWatch.val();
                    break;
            }

            return elWatchValue;
        }

        function getValueChangeHandler(elWatch, alias) {
            return function () {
                var dataAlias = $.parseJSON(elWatch.attr('data-targetalias'), true);
                var dataValue = $.parseJSON(elWatch.attr('data-targetvalue'), true);
                var dataAction = $.parseJSON(elWatch.attr('data-targetaction'), true);
                var elTarget, elNewValue;

                for (var i = 0; i < dataAlias.length; i++) {
                    if (dataAlias[i].length > 0) {
                        elTarget = $(".custom-data-type[name='" + dataAlias[i] + "']");
                        elNewValue = getWatchValue(alias, elWatch);
                        runConditionalAction(elTarget, dataAction[i], dataValue[i], elNewValue);
                    }
                }
            };
        }

        $.fn.setConditions = function () {

            return this.each(function () {

                var $this = $(this);
                var alias = $this.attr('data-conditionalAlias');
                var value = $this.attr('data-conditionalValue');
                var action = $this.attr('data-conditionalAction');
                var targetalias = [];
                var targetvalue = [];
                var targetaction = [];
                var elWatch;
                var aliasWatchValue;

                if (typeof alias !== 'undefined') {

                    elWatch = $(".custom-data-type[name^='" + alias + "']");

                    if (elWatch.length > 0) {
                        if (elWatch.is('[data-targetalias]') && typeof elWatch.attr('data-targetalias') !== 'undefined') {
                            targetalias = $.parseJSON(elWatch.attr('data-targetalias'));
                        }
                        targetalias.push($this.attr('name'));
                        elWatch.attr('data-targetalias', JSON.stringify(targetalias));

                        if (elWatch.is('[data-targetvalue]') && typeof elWatch.attr('data-targetvalue') !== 'undefined') {
                            targetvalue = $.parseJSON(elWatch.attr('data-targetvalue'));
                        }
                        targetvalue.push(value);
                        elWatch.attr('data-targetvalue', JSON.stringify(targetvalue));

                        if (elWatch.is('[data-targetaction]') && typeof elWatch.attr('data-targetaction') !== 'undefined') {
                            targetaction = $.parseJSON(elWatch.attr('data-targetaction'));
                        }
                        targetaction.push(action);
                        elWatch.attr('data-targetaction', JSON.stringify(targetaction));

                        initConditionalDisplay($this, action);

                        aliasWatchValue = getWatchValue(alias, elWatch);
                        runConditionalAction($this, action, value, aliasWatchValue);

                        switch (elWatch.attr('data-type')) {

                            case 'datepicker-type':
                                elWatch.datepicker('option', 'onSelect', getValueChangeHandler(elWatch, alias));
                                break;

                            default:
                                // It is enough to attach only one handler
                                $(document)
                                    .off('change.rw_conditions', ".custom-data-type[name^='" + alias + "']")
                                    .on('change.rw_conditions', ".custom-data-type[name^='" + alias + "']", getValueChangeHandler(elWatch, alias));
                                break;
                        }

                    }
                }

            });

        };

        window.setTimeout(function () {
            $('.custom-data-type').setConditions();
        }, 0);

    });

})(jQuery);
