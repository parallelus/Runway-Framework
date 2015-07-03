jQuery(function() {

    var $ = jQuery;

    function init_conditional_display(el, action) {

            switch(action) {
                case 'show': {
                    el.closest('tr').hide();
                } break;

                case 'hide': {
                    el.closest('tr').show();
                } break;

                default: {} break;
            }

    }

    function conditional_action(el, action, data_value, alias_new_value) {

        var toggle = data_value == alias_new_value;

        if( toggle ) {
            switch(action) {
                case 'show': {
                    el.closest('tr').show();
                } break;

                case 'hide': {
                    el.closest('tr').hide();
                } break;

                default: {} break;
            }
        } else {
            switch(action) {
                case 'show': {
                    el.closest('tr').hide();
                } break;

                case 'hide': {
                    el.closest('tr').show();
                } break;

                default: {} break;
            }                    
        }
    }

    function get_watch_value( alias, el_watch ) {
                var el_watch_value;
            
                switch( el_watch.attr('data-type') ) {
                    case 'radio-buttons': {
                        el_watch_value = $(".custom-data-type[name='" + alias + "']:checked").val();
                    } break;

                    case 'radio-buttons-image': {
                        el_watch_value = $(".custom-data-type[name='" + alias + "']:checked").val();
                    } break;

                    case 'checkbox-bool-type': {
                        el_watch_value = ($(".custom-data-type[name='" + alias + "']").is(":checked"))? 'true' : 'false';
                    } break;

                    case 'checkbox-type': {
                        var el_watch_checked = [];
                        el_watch.each(function(){
                            if( $(this).is(":checked") )
                                el_watch_checked.push( $(this).val() );
                            });
                        el_watch_value = el_watch_checked.join(',');
                    } break;

                    case 'multi-select-type': {
                        var el_watch_selected = el_watch.val();
                        el_watch_value = el_watch_selected.join(',');
                    } break;

                    case 'range-slider': {
                        var start = $('.slider-value.slider-start-' + alias).text();
                        var end = $('.slider-value.slider-end-' + alias).text();
                        el_watch_value = (end == '')? Math.floor(start) : Math.floor(start) + ',' + Math.floor(end);
                    } break;

                    case 'code-editor': {
                        el_watch_value = el_watch.val();
                        el_watch_value = el_watch_value.replace(/(\r\n|\n|\r)/gm,"");    // remove line breaks
                    } break;                    

                    case 'font-select': {
                        var el_watch_font = [];
                        el_watch.each(function(){
                            el_watch_font.push( $(this).val() );                         // family, style, weight, size, color
                        });
                        el_watch_value = el_watch_font.join(',');
                    } break;  

                    default: {
                        el_watch_value = el_watch.val();
                    } break;
                }  

                return el_watch_value;
    }

    $('.custom-data-type').each(function(){
        var alias = $(this).attr('data-conditionalAlias');
        var value = $(this).attr('data-conditionalValue');
        var action = $(this).attr('data-conditionalAction');

        if(typeof alias !== 'undefined') {

            var el_watch = $(".custom-data-type[name^='" + alias + "']");

            if( el_watch.length > 0 ) {
                var targetalias = [],
                    targetvalue = [],
                    targetaction = [];

                if( el_watch.is('[data-targetalias]') && typeof el_watch.attr('data-targetalias') !== 'undefined' ) {
                    targetalias = $.parseJSON(el_watch.attr('data-targetalias'));
                }
                targetalias.push($(this).attr('name'));
                el_watch.attr('data-targetalias', JSON.stringify(targetalias));

                if( el_watch.is('[data-targetvalue]') && typeof el_watch.attr('data-targetvalue') !== 'undefined' )
                    targetvalue = $.parseJSON(el_watch.attr('data-targetvalue'));
                targetvalue.push(value);
                el_watch.attr('data-targetvalue', JSON.stringify(targetvalue));

                if( el_watch.is('[data-targetaction]') && typeof el_watch.attr('data-targetaction') !== 'undefined' )
                    targetaction = $.parseJSON(el_watch.attr('data-targetaction'));
                targetaction.push(action);
                el_watch.attr('data-targetaction', JSON.stringify(targetaction));

                init_conditional_display($(this), action);
                
                var alias_watch_value = get_watch_value( alias, el_watch );
                conditional_action($(this), action, value, alias_watch_value);
                
                switch( el_watch.attr('data-type') ) {

                    case 'datepicker-type': {
                        el_watch.datepicker("option", "onSelect", function(){
                            var data_alias = $.parseJSON(el_watch.attr('data-targetalias'), true);
                            var data_value = $.parseJSON(el_watch.attr('data-targetvalue'), true);
                            var data_action = $.parseJSON(el_watch.attr('data-targetaction'), true);
                            var el_target, el_new_value;

                            for (var i = 0; i < data_alias.length; i++) {
                                if( data_alias[i].length > 0 ) {
                                    el_target = $(".custom-data-type[name^='" + data_alias[i] + "']");
                                    el_new_value = get_watch_value( alias, el_watch );
                                    conditional_action(el_target, data_action[i], data_value[i], el_new_value);
                                }
                            }
                        }); } break;

                    default: {
                        $(document).on("change", el_watch, function(){
                            var data_alias = JSON.parse(el_watch.attr('data-targetalias'), true);
                            var data_value = JSON.parse(el_watch.attr('data-targetvalue'), true);
                            var data_action = JSON.parse(el_watch.attr('data-targetaction'), true);
                            var el_target, el_new_value;

                            for (var i = 0; i < data_alias.length; i++) {
                                if( data_alias[i].length > 0 ) {
                                    el_target = $(".custom-data-type[name^='" + data_alias[i] + "']");
                                    el_new_value = get_watch_value( alias, el_watch );
                                    conditional_action(el_target, data_action[i], data_value[i], el_new_value);
                                }
                            }
                        }); } break;
                }  

            }
        }
    });
    
});