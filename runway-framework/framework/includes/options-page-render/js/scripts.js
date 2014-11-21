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
                case 'show': {console.log('++show');
                    el.closest('tr').hide();
                } break;

                case 'hide': {console.log('++hide');
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

    $('.custom-data-type').each(function(){
        var alias = $(this).attr('data-conditionalAlias');
        var value = $(this).attr('data-conditionalValue');
        var action = $(this).attr('data-conditionalAction');

        if(typeof alias !== 'undefined') {

            var alias_watch_value;
            var alias_watch = $(".custom-data-type[name='" + alias + "']");

            if( alias_watch.length > 0 ) {
                alias_watch.attr('data-targetalias', $(this).attr('name'));
                alias_watch.attr('data-targetvalue', value);
                alias_watch.attr('data-targetaction', action);

                init_conditional_display($(this), action);
                
                var dataType = alias_watch.attr('data-type');
                switch(dataType) {
                    case 'radio-buttons': {
                        alias_watch_value = $(".custom-data-type[name='" + alias + "']:checked").val();
                    } break;

                    case 'radio-buttons-image': {console.log('sss2');
                        alias_watch_value = $(".custom-data-type[name='" + alias + "']:checked").val();
                    } break;

                    case 'checkbox-bool-type': {console.log('sss3');
                        alias_watch_value = ($(".custom-data-type[name='" + alias + "']").is(":checked"))? 'true' : 'false';
                    } break;

                    case 'checkbox-type': {console.log('sss4');
                        alias_watch_value = ($(".custom-data-type[name='" + alias + "']").is(":checked"))? 'true' : 'false';
                    } break;

                    // case 'datepicker-type': {console.log('sss5');
                    //     alias_watch_value = $('body').find(".custom-data-type[name='" + alias + "']").val();
                    // } break;

                    default: {
                        alias_watch_value = $('body').find(".custom-data-type[name='" + alias + "']").val();
                        console.log(alias_watch_value);
                    } break;
                }  
                conditional_action($(this), action, value, alias_watch_value);
                
                switch(dataType) {

                    case 'datepicker-type': {
                        alias_watch.datepicker("option", "onSelect", function(){
                            var data_alias = $(this).attr('data-targetalias');
                            var data_value = $(this).attr('data-targetvalue');
                            var data_action = $(this).attr('data-targetaction');
                            var alias_new_value;

                            var alias_target = $(".custom-data-type[name='" + data_alias + "']");

                            alias_new_value = $('body').find(".custom-data-type[name='" + alias + "']").val();

                            conditional_action(alias_target, data_action, data_value, alias_new_value);

                        }); } break;

                    default: {
                        alias_watch.on('change', function(){

                            var data_alias = $(this).attr('data-targetalias');
                            var data_value = $(this).attr('data-targetvalue');
                            var data_action = $(this).attr('data-targetaction');
                            var alias_new_value;

                            var alias_target = $(".custom-data-type[name='" + data_alias + "']");

                            switch($(this).attr('data-type')) {
                                case 'radio-buttons': {
                                    alias_new_value = $(".custom-data-type[name='" + alias + "']:checked").val();
                                } break;

                                case 'radio-buttons-image': {
                                    alias_new_value = $(".custom-data-type[name='" + alias + "']:checked").val();
                                } break;

                                case 'checkbox-bool-type': {
                                    alias_new_value = ($(".custom-data-type[name='" + alias + "']").is(":checked"))? 'true' : 'false';
                                } break;

                                default: {
                                    alias_new_value = $('body').find(".custom-data-type[name='" + alias + "']").val();
                                } break;
                            }  

                            conditional_action(alias_target, data_action, data_value, alias_new_value);

                        }); } break;
                }  

            }
        }
    });
    
});