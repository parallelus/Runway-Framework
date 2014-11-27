jQuery(function() {

    var $ = jQuery;

    // Toggle developer mode action
    $('#ToggleDevMode').click(function(e){
        e.preventDefault();
        $('.developerMode').fadeToggle();
    });
    $(document).ready(function(){
        if($.cookie($('#page-slug').val()+'-activeTab')){
            $('.nav-tab').removeClass('nav-tab-active');
            $('.nav-tab[data-tabrel="'+($.cookie($('#page-slug').val()+'-activeTab')+'"]')).addClass('nav-tab-active');
            $('.tab-active').removeClass('tab-active');
            $($.cookie($('#page-slug').val()+'-activeTab')).addClass('tab-active');
        }
        $('.nav-tab').click(function(){
            $.cookie($('#page-slug').val()+'-activeTab', $(this).attr('data-tabrel'), { expires: 14});
        }) ;
    });

	$('.tab-controlls a').click(function() {

		if(!$(this).hasClass('nav-tab-active')) {
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
    
    $('.submit input').click(function(e){
        var current_index = -1;
        var start_index = -1;
		var current_name = '';
        
        $('.input-check.custom-data-type').each(function(){
            var name = $(this).attr('name');
            if(/\[(\d*)\]\[\]$/.test(name)) {
                var matched = name.match(/\[(\d*)\]\[\]$/);
                name = name.replace(/\[\d*\]\[\]$/, "");
				if(current_name != name) {
					current_name = name;
					current_index = -1;
					start_index = -1;
				}
		
                if(current_index != matched[1])
                {
                    current_index = matched[1];
                    start_index++;
                }
                
                $(this).attr('name', name+'['+start_index+'][]');
            }
            
            if($(this).attr('data-type') === 'checkbox-bool-type' && /\[\]$/.test(name)) {
                if(!$(this).prop("checked"))
                    $(this).val('false');
            }
        });
        
        var current_index = -1;
        var start_index = -1;
		var current_name = '';
        $('.input-radio.custom-data-type').each(function(){
            var name = $(this).attr('name');
            if(/\[(\d*)\]$/.test(name)) {
                var matched = name.match(/\[(\d*)\]$/);
                name = name.replace(/\[\d*\]$/, "");
		
				if(current_name != name) {
					current_name = name;
					current_index = -1;
					start_index = -1;
				}
		
                if(current_index != matched[1])
                {
                    current_index = matched[1];
                    start_index++;
                }
                
                $(this).attr('name', name+'['+start_index+']');
            }
        });
        
        $('body').find('.custom-data-type').each(function(index, el){
            var name = $(el).attr('name');
            switch($(el).data('type')){
                case "checkbox-type":
                    if(/\[(\d*)\]\[\]$/.test(name)) {
                        var checked = $("input[name='"+name+"']:checked");
                        if(checked.length === 0) {
                            var data_section = '';
                            if(typeof $(el).attr('data-section') !== "undefined")
                                data_section = "data-section='"+$(el).attr('data-section')+"'";
                            
                            $(this).attr('name', '').after("<input type='hidden' class='"+$(el).attr('class')+"' "+
                                    "value='false' name='"+name+"' "+data_section+" data-type='"+$(el).attr('data-type')+"' "+
                                    "/>");
                        }
                    }
                break;
                case "checkbox-bool-type": 
                    if(/\[\]$/.test(name)) {
                        var data_section = '';
                        if(typeof $(el).attr('data-section') !== "undefined")
                            data_section = "data-section='"+$(el).attr('data-section')+"'";
                            
                        if(!$(this).prop('checked')) {
                            $(this).attr('name', '').after("<input type='hidden' class='"+$(el).attr('class')+"' "+
                                    "value='false' name='"+name+"' "+data_section+" data-type='"+$(el).attr('data-type')+"' "+
                                    "/>");
                        }
                        else {
                            $(this).attr('name', '').after("<input type='hidden' class='"+$(el).attr('class')+"' "+
                                    "value='true' name='"+name+"' "+data_section+" data-type='"+$(el).attr('data-type')+"' "+
                                    "/>");
                        }
                    }
                break;
                case "multi-select-type":
                    if(/\[(\d*)\]\[\]$/.test(name)) {
                        var selected = $(el).children("option:selected");
                        if(selected.length === 0)
                            $(el).children('option').eq(0).prop('selected', 'selected');
                    }
                break;
                case "radio-buttons-image":
                case "radio-buttons":    
                    if(/\[(\d*)\]$/.test(name)) {
                        var checked = $("input[name='"+name+"']:checked");
                        if(checked.length === 0) {
                            
                            var data_section = '';
                            if(typeof $(el).attr('data-section') !== "undefined")
                                data_section = "data-section='"+$(el).attr('data-section')+"'";
                            
                            $("input[name='"+name+"']").attr('name', '');
                            $(this).attr('name', '').after("<input type='hidden' class='"+$(el).attr('class')+"' "+
                                    "value='false' name='"+name+"' "+data_section+" data-type='"+$(el).attr('data-type')+"' "+
                                    "/>");
                        }
                    }
                break;
            }
        });
    });

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

                if( $(this).is('[data-targetalias]') && typeof el_watch.attr('data-targetalias') !== 'undefined' ) {
                    targetalias = $.parseJSON(el_watch.attr('data-targetalias'));
                }
                targetalias.push($(this).attr('name'));
                el_watch.attr('data-targetalias', JSON.stringify(targetalias));

                if( $(this).is('[data-targetvalue]') && typeof el_watch.attr('data-targetvalue') !== 'undefined' )
                    targetvalue = $.parseJSON(el_watch.attr('data-targetvalue'));
                targetvalue.push(value);
                el_watch.attr('data-targetvalue', JSON.stringify(targetvalue));

                if( $(this).is('[data-targetaction]') && typeof el_watch.attr('data-targetaction') !== 'undefined' )
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