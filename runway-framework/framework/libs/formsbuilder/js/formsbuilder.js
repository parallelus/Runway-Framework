(function($){
    // Toggle developer mode action
    $('#ToggleDevMode').click(function(e){
        e.preventDefault();
        $('.developerMode').fadeToggle();
    });

    function new_item_added_event(element){
        $('#new-item-added').fadeIn(1000);
        $('#new-item-added').fadeOut(800);

        $('html, body').animate({
             scrollTop: element.offset().top
         }, 1000);
        element.removeClass('scrollTo');
    }    

    function new_item_duplicated_event(element){
        $('#new-item-duplicated').fadeIn(1000);
        $('#new-item-duplicated').fadeOut(800);

        $('html, body').animate({
             scrollTop: element.offset().top
         }, 1000);
        element.removeClass('scrollTo');
    } 

    $(function(){
        $('#add-tab').click(function(){
            var replaceMarker = $('<div/>', {
                id:     'new-element',
                class:  'accept-tab new-element'
            });
            $('.elements-list.page-layer.accept-tab.tabIn').append(replaceMarker);
            uiObject.onAddTab();
            var element = $('.tabbox:last').addClass('scrollTo');     
            new_item_added_event(element);
        });

        $('#add-container').click(function(){
            var replaceMarker = $('<div/>', {
                id:     'new-element',
                class:  'accept-container new-element scrollTo'
            });
            $('.inside.accept-container:last').append(replaceMarker);
            uiObject.onAddContainer();       
            var element = $('.containerbox:last').addClass('scrollTo');
            if (!uiObject.isPageEmpty())
                new_item_added_event(element);
        });

        $('#add-field').click(function(){
            var replaceMarker = $('<div/>', {
                id:     'new-element',
                class:  'accept-field new-element'
            });
            $('.inside.accept-field:last').append(replaceMarker);
            uiObject.onAddField();
            var element = $('.fieldbox:last').addClass('scrollTo');
            if (!uiObject.isPageEmpty())
                new_item_added_event(element);
        });

        $('body').on('click', '.page-element-controls .duplicate[data-type="container"]', function(e) {
            e.stopPropagation();
            e.preventDefault();                    

            var container_duplicate = $(this).parent().parent().parent().clone(true);
            
            var replaceMarker = $('<div/>', {
                id:     'new-element',
                class:  'accept-container new-element scrollTo'
            });
            $('.inside.accept-container:last').append(replaceMarker);
            uiObject.onCloneContainer(container_duplicate);       
            var element = $('.containerbox:last').addClass('scrollTo');
            if (!uiObject.isPageEmpty())
                new_item_duplicated_event(element);
        });

        $('body').on('click', '.page-element-controls .duplicate[data-type="field"]', function(e) {
            e.stopPropagation();
            e.preventDefault();                    

            var field_origin = $(this).parent().parent().parent();
            var container_origin = field_origin.parent().parent();
            var field_duplicate = field_origin.clone(true);

            var replaceMarker = $('<div/>', {
                id:     'new-element',
                class:  'accept-field new-element'
            });

            container_origin.find('.inside.accept-field:last').append(replaceMarker);
            uiObject.onCloneField(field_duplicate);
            var element = container_origin.find('.fieldbox:last').addClass('scrollTo');

            if (!uiObject.isPageEmpty())
                new_item_duplicated_event(element);        
        });

        $('body').on('click', '.customize-control-content', function(e) {
            e.stopPropagation();
            e.preventDefault();
        });
    });
})(jQuery);

function isEmpty(ob){
   for(var i in ob){ return false; }
   return true;
}

// save_custom_options('form');
function save_custom_options(alias, custom_alias, section){
    var $ = jQuery;
    var values = {};
    var types = {};
    if(!custom_alias){
        custom_alias = null;
    }

    //variables for save repeating checkbox list & radio groups
    var currentIndexInGroup = 0;
    var nextIndexInGroup = 0;
    
    $('body').find('.custom-data-type').each(function(index, el){
        
        
        //check if input is repeating - is array
        var isInputArray = false;
        var isDoubleArray = false;
        var element_name = $(el).attr('name');
        if(/\[\d*\]$/.test(element_name))
        {
            isInputArray = true;
            element_name = element_name.replace(/\[\d*\]$/, "");
        }
        
        types[element_name] = $(el).data('type');

        if($(el).data('section') == section){
            
            switch($(el).data('type')){
                case "checkbox-bool-type":{
                        if(typeof values[element_name] === "undefined" && isInputArray === true) {
                            values[element_name] = [];
                            values[element_name].push($(el).prop("checked") ? $(el).prop("checked") : 'false');
                        }
                        else if(typeof values[element_name] !== "undefined" && isInputArray === true) {
                            values[element_name].push($(el).prop("checked") ? $(el).prop("checked") : 'false');
                        }
                        else {
                            if($(el).prop("checked")){
                                values[element_name] = $(el).prop("checked");            
                            }
                            else{
                                values[element_name] = 'false';
                            }
                        }
                } break;
                
                case "checkbox-type":{
                            
                        if(/\[(\d*)\]/.test(element_name)) {
                            var matched = element_name.match(/\[(\d*)\]/);
                            element_name = element_name.replace(/\[\d*\]$/, "");
                            isDoubleArray = true;
                            
                            if(matched[1] !== "undefined")
                            {
                                if(currentIndexInGroup !== parseInt(matched[1], 10))
                                {
                                    nextIndexInGroup ++;
                                    currentIndexInGroup = parseInt(matched[1], 10);
                                }
                                
                                if(nextIndexInGroup > 0 && typeof values[element_name] === "undefined") {
                                    nextIndexInGroup = 0;
                                }
                            }
                        }
                        else {
                            isDoubleArray = false;
                            currentIndexInGroup = 0;
                            nextIndexInGroup = 0;
                        }
                        
                        if(typeof values[element_name] === "undefined")
                            values[element_name] = [];
                        
                        if(isDoubleArray === true) {
                            if(typeof values[element_name][nextIndexInGroup] === "undefined")
                                values[element_name][nextIndexInGroup] = [];
                            
                            if($(el).prop("checked"))
                                values[element_name][nextIndexInGroup].push($(el).val());
                            if( $("input[name='"+$(el).attr('name')+"']:checked").length === 0)
                            {
                                values[element_name][nextIndexInGroup] = [];
                                values[element_name][nextIndexInGroup].push('');
                            }
                        }
                        else {
                            if($(el).prop("checked"))
                                values[element_name].push($(el).val());
                        }
                } break;
                            
                case "multi-select-type":{
                        
                        if(/\[(\d*)\]/.test(element_name)) {
                            var matched = element_name.match(/\[(\d*)\]/);
                            element_name = element_name.replace(/\[\d*\]$/, "");
                            isDoubleArray = true;
                        }
                        else {
                            isDoubleArray = false;
                        }
                        
                        if(typeof values[element_name] === "undefined")
                            values[element_name] = [];
                        
                        var iterator = values[element_name].length;
                        if(typeof values[element_name][iterator] === "undefined" && isDoubleArray === true)
                            values[element_name][iterator] = [];
                        
                        $(el).children("option:selected").each(function(){
                            if(isDoubleArray === true)
                                values[element_name][iterator].push($(this).val());
                            else
                                values[element_name].push($(this).val());
                        });
                        			
                        if(isDoubleArray === true && values[element_name][iterator].length == 0)
                            values[element_name][iterator].push('no');
                        
                } break;
               
                case "radio-buttons-image":{
                        
                    if(typeof values[element_name] === "undefined" && isInputArray === true) {
                        values[element_name] = [];
                        nextIndexInGroup = 0;
                        currentIndexInGroup = 0;
                        
                        var matched = $(el).attr('name').match(/\[(\d*)\]/);  
                        if(matched[1] !== "undefined")
                            currentIndexInGroup = matched[1];
                        
                        values[element_name][nextIndexInGroup] = '';
                        if($(el).prop('checked')){
                            values[element_name][nextIndexInGroup] = $(el).val();
                        }
                    }
                    else if(typeof values[element_name] !== "undefined" && isInputArray === true) {
                        var matched = $(el).attr('name').match(/\[(\d*)\]/);
                        if(matched[1] !== "undefined")
                        {
                            if(currentIndexInGroup != matched[1])
                            {
                                nextIndexInGroup ++;
                                currentIndexInGroup = matched[1];
                            }
                        }
                        
                        if(typeof values[element_name][nextIndexInGroup] === 'undefined')
                            values[element_name][nextIndexInGroup] = '';
                        
                        if($(el).prop('checked')){
                            values[element_name][nextIndexInGroup] = $(el).val();
                        }
                    }   
                    else {
                        if($(el).prop('checked')){
                            values[element_name] = $(el).val();
                        }
                        
                        if($("input[name='"+$(el).attr('name')+"']:checked").length === 0) {
                            values[element_name] = '';
                        }
                            
                    }
                } break;
                
                case "radio-buttons":{
                    if(typeof values[element_name] === "undefined" && isInputArray === true) {
                        values[element_name] = [];
                        nextIndexInGroup = 0;
                        currentIndexInGroup = 0;
                        
                        var matched = $(el).attr('name').match(/\[(\d*)\]/);  
                        if(matched[1] !== "undefined")
                            currentIndexInGroup = matched[1];
                        
                        values[element_name][nextIndexInGroup] = '';
                        if($(el).prop('checked')){
                            values[element_name][nextIndexInGroup] = $(el).val();
                        }
                    }
                    else if(typeof values[element_name] !== "undefined" && isInputArray === true) {
                        var matched = $(el).attr('name').match(/\[(\d*)\]/);
                        if(matched[1] !== "undefined")
                        {
                            if(currentIndexInGroup != matched[1])
                            {
                                nextIndexInGroup ++;
                                currentIndexInGroup = matched[1];
                            }
                        }
                        
                        if(typeof values[element_name][nextIndexInGroup] === 'undefined')
                            values[element_name][nextIndexInGroup] = '';
                        
                        if($(el).prop('checked')){
                            values[element_name][nextIndexInGroup] = $(el).val();
                        }
                    }   
                    else {
                        if($(el).prop('checked')){
                            values[element_name] = $(el).val();
                        }
                        
                        if($("input[name='"+$(el).attr('name')+"']:checked").length === 0) {
                            values[element_name] = '';
                        }
                    }
                } break;      
            
                case "texteditor-type": {
                        var element_id = $(el).attr('id');
                        var content;
                        var editor = tinyMCE.get(element_id);
                        if (editor) {
                            // Ok, the active tab is Visual
                            content = editor.getContent();
                        } else {
                            // The active tab is HTML, so just query the textarea
                            content = $('#'+element_id).val();
                        }
                        values[element_name] = content;
                } break;
		
		case "code-editor": {
			if($(el).hasClass('ace_editor')) {
				values[element_name] = $(el).val();	
			}
		} break;
		
                // case "text-editor":{
                // TODO: text editor data saving
                // } break;
                default:{
                        
                    if(typeof values[element_name] === "undefined" && isInputArray === true) {
                        values[element_name] = [];
                        values[element_name].push($(el).val());
                    }
                    else if(typeof values[element_name] !== "undefined" && isInputArray === true) {
                        values[element_name].push($(el).val());
                    }
                    else {
                        values[element_name] = $(el).val();
                    }
                }
                break;
            }        
        }
    });  
    
    if(!isEmpty(values) && !isEmpty(types)){
        if($('#layout_alias').val())
            layout_alias = $('#layout_alias').val();
        else
            layout_alias = '';
        $.ajax({
            async: false,
            url: ajaxurl,
            method: "POST",
            data:{
                action: 'save_custom_options',
                form_key: alias,
                custom_alias: custom_alias,
                vals: values,
                types: types,
                layout_alias: layout_alias
            }
        }).done(function(response){
                console.log(response);
            return true;
        }); 
    }
    else{
        return false;
    }
}

function getFormData(form_id) {

    // init $ by jQuery
    var $ = jQuery;
    // setup form
    var $form = $(form_id);
    var data = {};

    // get all named form elements
    var names = [];

    $form.find('[name]').each(function(key, element) {
        names.push($(element).prop('name').replace('[]', ''));
    });

    // get all avalable form values
    var values = $form.serializeArray();
    var valuesConverted = {};

    // convert them into associative array
    for(key in values) {

        if(valuesConverted[values[key].name] == undefined) valuesConverted[values[key].name] = [];

        if(values[key].value.length) valuesConverted[values[key].name].push(values[key].value);
        else valuesConverted[values[key].name].push('');
    }

    // build data array based on form names and values
    for(key in names) {
        d_key = names[key].replace("[]", "");
        if(valuesConverted[names[key]] === undefined) {
            data[d_key] = false;
        } else {
            if(valuesConverted[names[key]].length == 1) {
                data[d_key] = valuesConverted[names[key]][0];
            } else {
                data[d_key] = valuesConverted[names[key]];
            }
        }
    }

    // return serialized form data 
    return data;

}

// Creates a "random" string of specified length for field ID's
function make_ID(length) {

    var result = '', chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    length = typeof length !== 'undefined' ? length : 12; // used specified length, or default 12
    if (length)
    for (var i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result;
}

var builder = null;

var pageObject = {};
var uiObject = {};

var settings_fields_names_definition = {
    index: translations_js.field_id,
    title: translations_js.field_title,
    titleCaption: translations_js.field_title_caption,
    alias: translations_js.field_alias,
    type: translations_js.field_type,
    fieldCaption: translations_js.field_caption,
    values: translations_js.field_values,
    required: translations_js.required,
    validation: translations_js.validation,
    validationMessage: translations_js.validation_message,
    requiredMessage: translations_js.field_required_message,
    cssClass: translations_js.field_css_class,
    changeMonth: translations_js.change_month_enabled,
    changeYear: translations_js.change_year_enabled,
    format: translations_js.date_format,
    image_size: translations_js.radio_image_size,
    repeating: translations_js.repeating
};

var system_vars_definition = ['template', 'index'];

(function($) {

    pageObject = (function() {

        var page_settings = {};
        var page_elements_list = {};

        return {

            addNew: function(type) {

                var options = {
                    index: make_ID(), //(new Date()).getTime(),
                    template: type
                };

                switch(type) {
                    case 'tab': {
                            options.title = translations_js.new_tab;

                    } break;

                    case 'field': {

                            options.title = translations_js.new_field;
                            options.type = 'input-text';
                            options.alias = 'field-' + options.index; /*options.requiredMessage = '%field_name% is required.';*/

                    } break;

                    case 'container': {

                            options.type = 'invisible';
                            options.title = translations_js.new_container;
                            options.display_on_customization_page = false;

                    } break;

                    default: {} break;
                }

                page_elements_list[options.index] = options;

                return options;

            },

            cloneItem: function(options_origin) {

                var options = {
                    index: make_ID(),
                    template: options_origin.template
                };

                switch(options_origin.template) {
                    case 'tab': {

                    } break;

                    case 'field': {
                            var options_new = {};

                            for(var key in options_origin) {
                                switch(key) {
                                    case 'index': {} break;

                                    case 'title': {
                                            options.title = options_origin.title + ' - ' + translations_js.duplicate;
                                    } break;

                                    case 'alias': {
                                            options.alias = options_origin.alias + ' - ' + translations_js.duplicate;
                                    } break;

                                    default: {
                                        options[key] = options_origin[key];
                                    } break;
                                }                                    
                            }

                    } break;

                    case 'container': {

                            options.type = options_origin.type;
                            options.title = options_origin.title + ' - ' + translations_js.duplicate;
                            options.display_on_customization_page = options_origin.display_on_customization_page;

                    } break;

                    default: {} break;
                }

                page_elements_list[options.index] = options;

                return options;

            },

            getElement: function(index) {
                return page_elements_list[index];
            },

            removeElement: function(index) {

            },

            updateOption: function(options) {

                for(var key in options) {
                    page_elements_list[options.index][key] = options[key];
                }

                return page_elements_list[options.index];

            },

            getStructure: function() {

                var sortOrder = uiObject.getSortOrder();
                var valid = [];

                // TODO: temp clearing broken elements
                for(var keyTab in sortOrder) {
                    valid.push(keyTab);
                    for(var keyContainer in sortOrder[keyTab]) {
                        valid.push(keyContainer);
                        for(var keyField in sortOrder[keyTab][keyContainer]) {
                            valid.push(keyField);
                        }
                    }
                }

                var valid_elms = {
                    none: 'none'
                };

                for(var key in valid) {
                    if(page_elements_list[valid[key]] !== undefined) valid_elms[valid[key]] = page_elements_list[valid[key]];
                }

                return {
                    settings: page_settings,
                    sortOrder: sortOrder,
                    elements: valid_elms
                };
            },

            savePageSettings: function(settings) {
                for(var key in settings) {
                    page_settings[settings[key]['name']] = settings[key]['value'];
                }

            },

            getPageSettings: function() {
                return page_settings;
            },

            restore: function(pageJSON) {

                var page = $.parseJSON(pageJSON);

                page_elements_list = page.elements;

                page_settings = page.settings;

                for(var tabKey in page.sortOrder) {

                    if(tabKey != 'none') {
                        var tab = uiObject.template.build(page_elements_list[tabKey]);
                        $('.page-layer').append(tab);
                        tab = tab.find(".inside");

                        for(var containerKey in page.sortOrder[tabKey]) {

                            if(containerKey != 'none') {
                                var container = uiObject.template.build(page_elements_list[containerKey]);
                                tab.append(container);
                                container = container.find(".inside");

                                for(var fieldKey in page.sortOrder[tabKey][containerKey]) {

                                    if(fieldKey != 'none') {
                                        var field = uiObject.template.build(page_elements_list[fieldKey]);
                                        container.append(field);
                                    }

                                }
                            }
                        }
                    }
                }

                uiObject.init();

                for(var key in page_elements_list) {
                    if(page_elements_list[key].template == 'field') {
                        uiObject.settingsPreview(page_elements_list[key]);
                    }
                }

                return page.elements.length !== undefined;

            }

        };

    })();

    uiObject = (function() {

        var godObj = {

            template: {

                build: function(options) {

                    return $("#" + options.template).tmpl(options);
                }
            },

            settingsPreview: function(settings) {

                var exclude_list = ['index', 'template', 'submission_type'];

                if(settings.template == 'container') {
                    var header_text = settings.title + " (" + settings.type + ") ";
                   
                    var target = $($('[data-index="' + settings.index + '"] .hndle')[0]);

                    target.html(header_text);
                }

                if(settings.template == 'field') {

                    var target = $('[data-index="' + settings.index + '"] .inside');

                    target.html('');

                    for(var key in settings) {
                        if($.inArray(key, exclude_list) == -1) {

                            var title = key;

                            if($.inArray(key, system_vars_definition) == 0) {
                                title = settings_fields_names_definition[key];
                            }

                            if(title !== undefined) {
                                //if(settings[key] == null || settings[key] == '') settings[key] = 'no';
                                if(settings[key] !== undefined && settings[key] != '' && settings[key] != null) {
                                    target.append($('#field-settings-preview').tmpl({
                                        label: title + ": ",
                                        value: settings[key]
                                    }));
                                }

                            }
                        }
                    }

                    target.find('.settings-preview-raw').wrapAll('<div class="field-settings-preview" />');
                    target.append('<div class="developerMode"><code class="data-function">get_options_data(\''+ $('#slug-static').children('.edit-slug').text() +'\', \''+ settings['alias'] +'\')</code></div>');

                    $('.data-function').mousedown(function() {                               // enable select text for developer
                        if( $('.data-function').parent().attr('class') == 'developerMode')
                            $('.ui-sortable').sortable("option", "cancel", ".data-function");
                    });
                }

            },

            getSortOrder: function() {
                var sortOrder = {};

                $("[data-template='tab']").each(function() {

                    var tabIndex = $(this).data('index');

                    sortOrder[tabIndex] = {
                        none: 'none'
                    };

                    $(this).find(".page-container").each(function() {

                        var containerIndex = $(this).data('index');

                        sortOrder[tabIndex][containerIndex] = {
                            none: 'none'
                        };

                        $(this).find(".page-field").each(function() {

                            var fieldIndex = $(this).data('index');

                            sortOrder[tabIndex][containerIndex][fieldIndex] = fieldIndex;
                        });
                    })

                });

                return sortOrder;

            },

            updatePageElement: function(options) {

                var newElement = $("#" + options.template).tmpl(options);
                newElement.find(".inside").html($(".page-layer [data-index='" + options.index + "'] .inside").html());
                $(".page-layer [data-index='" + options.index + "']").replaceWith(newElement);

                uiObject.init();

            },

            onAddContainer: function() {

                var replaceMarker = $(".accept-field").find(".new-element");

                if(!replaceMarker.length) {
                    replaceMarker = $(".accept-tab .accept-container").find(".new-element");
                }

                if(!replaceMarker.length) {
                    replaceMarker = $(".page-layer").find(".new-element");
                }

                var type = replaceMarker.data('type');
                
                var options = pageObject.addNew("container");

                options.title = translations_js.new_container+" | " + options.type.replace("-type", " field");

                var repl = godObj.template.build(options);

                $(replaceMarker).replaceWith(repl);

                $('.page-layer').find(".new-element").remove();

                godObj.init();

                if(!empty(repl)) repl.find(".inside").append($("<div>").addClass("new-element"));
                uiObject.onAddField();

                return repl;

            },

            onAddField: function() {
                var replaceMarker = $(".accept-field").find(".new-element");

                var type = $(replaceMarker).data('type');

                var options = pageObject.addNew("field");

                options.title = translations_js.new_field+" | " + options.type.replace("-type", " field");

                var repl = godObj.template.build(options);

                $(replaceMarker).replaceWith(repl);
                uiObject.settingsPreview(options);

                $(".page-layer").find(".new-element").remove();

                godObj.init();

                return repl;

            },

            onAddTab: function() {

                var replaceMarker = $(".accept-tab").find(".new-element, .first");

                var add_inside = replaceMarker.hasClass("first");


                var type = $(this).data('type');

                var repl = godObj.template.build(pageObject.addNew("tab"));
                $(replaceMarker).replaceWith(repl);

                godObj.init();

                if(!empty(repl)) repl.find(".inside").append($("<div>").addClass("new-element"));
                uiObject.onAddContainer();


                return repl;

            },

            onCloneContainer: function(container_duplicate) {

                var replaceMarker = $(".accept-field").find(".new-element");

                if(!replaceMarker.length) {
                    replaceMarker = $(".accept-tab .accept-container").find(".new-element");
                }

                if(!replaceMarker.length) {
                    replaceMarker = $(".page-layer").find(".new-element");
                }

                var type = replaceMarker.data('type');
                
                var index_origin_container = container_duplicate.attr('data-index');
                var options_origin_container = pageObject.getElement(index_origin_container);
                var options_new_container = pageObject.cloneItem(options_origin_container);

                var repl = godObj.template.build(options_new_container);

                $(replaceMarker).replaceWith(repl);

                $('.page-layer').find(".new-element").remove();

                godObj.init();

                var field_new, index_new, options_new;
                var field_clone, index_clone, options_clone;

                container_duplicate.find('.page-field').each(function(index, el){
                    
                    if(!empty(repl)) repl.find(".inside.accept-field").append($("<div>").addClass("new-element"));
                    uiObject.onCloneField($(this));
                });

                return repl;

            },

            onCloneField: function(field_duplicate) {
                var replaceMarker = $(".accept-field").find(".new-element");

                var type = $(replaceMarker).data('type');

                index_origin_field = field_duplicate.attr('data-index');
                options_origin_field = pageObject.getElement(index_origin_field);
                var options = pageObject.cloneItem(options_origin_field);
                var repl = godObj.template.build(options);

                $(replaceMarker).replaceWith(repl);
                uiObject.settingsPreview(options);

                $(".page-layer").find(".new-element").remove();

                godObj.init();

                return repl;

            },

            init: function(level) {

                // $('.accept-tab').sortable('destroy');
                $('.accept-tab').sortable({
                    connectWith: '.accept-tab'
                });

                // $('.accept-container').sortable('destroy');
                $('.accept-container').sortable({
                    connectWith: '.accept-container'
                });

                // $('.accept-field').sortable('destroy');
                $('.accept-field').sortable({
                    connectWith: '.accept-field'
                });

                // $('.ntab').draggable('destroy');
                $('.ntab').draggable({

                    helper: 'clone',
                    connectToSortable: '.accept-tab',
                    stop: godObj.onAddTab

                });

                // $('.ncontainer').draggable('destroy');
                $('.ncontainer').draggable({

                    helper: 'clone',
                    connectToSortable: '.accept-container',
                    stop: godObj.onAddContainer

                });

                // $('.nfield').draggable('destroy');
                $('.nfield').draggable({

                    helper: 'clone',
                    connectToSortable: '.accept-field',
                    stop: godObj.onAddField

                });



            },

            isPageEmpty: function() {
                
                return ($('.tabbox').length > 0)? false : true;
            },

            setDefaultForEmptyValue: function(options) {

                var answer, default_value = (options.type == 'radio-buttons-image')? 'key=>LINK TO IMAGE': 'key=>value';
                var datatypes_with_default = ['checkbox-type', 'multi-select-type', 'radio-buttons-image', 'radio-buttons', 'select-type'];

                if($.inArray(options.type, datatypes_with_default) > -1) {
                    if($.trim(options.values).length === 0) {
                        answer = confirm(translations_js.default_values+' ' + default_value + ' '+translations_js.will_be_added);
                        if(answer)
                            options.values = default_value;
                        else
                            return false;
                    }
                }
                return options;
            },

            loadFieldSettingsMiddleSection: function(options) {
                
                var names = [];

                $($("#" + builder.availableTypes[options.type].settingsFormTemplateID).html()).find('[name]').each(function(key, element) {
                    names.push($(element).prop('name').replace('[]', ''));
                });

                for(var key in names) {
                    if(options[names[key]] === undefined) {
                        options[names[key]] = '';
                    }
                }

                $('.field-settings-form-middle-section').html($("#" + builder.availableTypes[options.type].settingsFormTemplateID).tmpl(options));

                $('.settings-dialog').find('[name="type"]').change(function() {

                    $('.settings-dialog .accept-changes').focus();

                    var options = uiObject.grabCurrentOptions($('.settings-dialog'));

                    // clear
                    options.validation = null;
                    options.required = null;
                    options.values = '';

                    uiObject.fillSettingsDialog(options);
                });

            },

            grabCurrentOptions: function(context) {

                var index = context.find('[name="index"]').val();

                var current_options = pageObject.getElement(index);

                var options = $('.settings-head').serializeArray();

                $('.settings-head input[type="checkbox"]:not(:checked)').each(function() {
                    current_options[$(this).prop('name')] = 'false';
                });

                for(key in options) {
                    current_options[options[key]['name']] = options[key]['value'];
                }

                return current_options;

            },

            fillSettingsDialog: function(options) {

                if(options.title.search(translations_js.default+" .* field") == 0) {
                    options.title = translations_js.default+" " + options.type.replace("-type", " field");
                }

                // load settings form basis
                $(".settings-dialog-inside").html($('#' + options.template + '-settings').tmpl(options));

                if(options.template == 'field') {
                    uiObject.loadFieldSettingsMiddleSection(options);
                }
            },

            initOther: function() {

                // other controls
                $('body').on('click', '.postbox .handlediv', function(event) {
                    if($.cookie('options-page-'+pageObject.getStructure().settings.alias)){
                        var cookie_arr = $.cookie('options-page-'+pageObject.getStructure().settings.alias).split(',');
                    }
                    else{
                        var cookie_arr = Array();
                    }
		    
		    if($(this).parent().parent().data('index') != undefined) {
			var index = $(this).parent().parent().data('index').toString();

			if($.inArray(index, cookie_arr) == -1){
			    cookie_arr.push(index)
			    $.cookie('options-page-'+pageObject.getStructure().settings.alias, cookie_arr, { expires: 14});
			    $(this).parent().find('.inside').hide();
			}
			else{
			    cookie_arr.splice( $.inArray(index, cookie_arr), 1 );
			    $.cookie('options-page-'+pageObject.getStructure().settings.alias, cookie_arr, { expires: 14});
			    $(this).parent().find('.inside').show();
			}
			hide_from_cookie();
		    }
		    else {
			var p = $(this).parent('.postbox'), id = p.attr('id');
			p.toggleClass('closed');
			event.preventDefault();
		    }
                });

                function hide_from_cookie(){
                    if($.cookie('options-page-'+pageObject.getStructure().settings.alias)){
                        var cookie_arr = $.cookie('options-page-'+pageObject.getStructure().settings.alias).split(',');
                        $('.page-element').each(function(i){
                            var find = $(this).data('index').toString();
                            for(var key in cookie_arr){
                                if(find == cookie_arr[key]){
                                    $(this).find('.postbox').find('.inside').hide();
                                }
                            }
                        });
                    }
                }

                $(document).ready(function(){
                    hide_from_cookie();
                });

                var removeItem = {}; // last removed item
                $('body').on('click', '.page-element-controls .remove', function() {

                    //var $this = $(this).parent().parent().parent();
                    var $this = $(this).closest('.page-element');

                    var type = $this.attr('data-template');
                    $this.hide('slow', function() {
                        removeItem = {
                            'typeItem': type,
                            'parentItem': $($this).parents('.meta-box-sortables')[0],
                            'item': $this
                        }
                        $this.remove();
                    });

                    $('div#message').html('<p>'+translations_js.item_deleted+'. <a href="#" id="undo-delete">'+translations_js.undo+'</a></p>');
                    $('div#message').each( function(i){
                        if( !i ) this.remove();
                    })
                    $('div#message').fadeIn(1000);

                });

                $('body').on('click', '#undo-delete', function() {
                    $(removeItem.parentItem).find('div.clear').remove();
                    $(removeItem.item[0]).css('opacity', '');
                    if(removeItem.typeItem == 'field') {                        
                        $(removeItem.parentItem).find('div.inside.accept-field').append(removeItem.item[0]);
                        $(removeItem.item[0]).fadeIn(1000);
                        $(removeItem.parentItem).find('div.inside.accept-field').append('<div class="clear"></div>');
                    } else if(removeItem.typeItem == 'container') {                        
                        $(removeItem.parentItem).find('div.inside.accept-container').append(removeItem.item[0]);
                        $(removeItem.item[0]).fadeIn(1000);
                        $(removeItem.parentItem).find('div.inside.accept-container').append('<div class="clear"></div>');
                    } else if(removeItem.typeItem == 'tab') {                        
                        $('div.page-layer.accept-tab').find('div.clear').remove();
                        $('div.page-layer.accept-tab').append('<div class="clear"></div>');
                        $('div.page-layer.accept-tab').append(removeItem.item[0]);
                        $(removeItem.item[0]).fadeIn(1000);
                        $('div.page-layer.accept-tab').append('<div class="clear"></div>');                        
                    }
                    $('div#message').html('<p>'+translations_js.item_restored+'</p>');
                });

                $('.settings-dialog').dialog({
                    autoOpen: false,                    
                    title: '<div id="icon-edit-pages" class="icon32 icon32-posts-page"></div><h2>'+translations_js.edit_field+'</h2>',
                    modal: true,
                    width: 600,
                    draggable: false,
                    resizable: false,
                    open: function () {
                        for(var key in builder.availableTypes) {
                            if(typeof builder.availableTypes[key].onSettingsDialogOpen === 'function') {
                                builder.availableTypes[key].onSettingsDialogOpen();
                            }
                            
                        }                        
                    }
                });

                $('.expand-all').on('click', function () {
                    $('#menu-management-liquid .inside').fadeIn('fast');
                    $.cookie('options-page-'+pageObject.getStructure().settings.alias, null);
                });

                $('.collapse-all').on('click', function () {
                    $('#menu-management-liquid .inside').fadeOut('fast');
                    var indexes = Array();
                    $('.page-element').each(function(i){
                        var index = $(this).data('index').toString();
                        indexes.push(index);
                    });
                    $.cookie('options-page-'+pageObject.getStructure().settings.alias, null);
                    $.cookie('options-page-'+pageObject.getStructure().settings.alias, indexes, { expires: 14});
                });

                $('body').on('click', '.page-element-controls .edit', function(e) {
                    // disable default event (scroll to up)
                    e.preventDefault();

                    // get field index
                    var objectIndex = $(this).parent().parent().parent().data('index');

                    // get field options
                    var options = pageObject.getElement(objectIndex);

                    // needs for jQuery Ui >= 1.10
                    var settings_dialog = $(".settings-dialog").dialog();
                    settings_dialog.data( "uiDialog" )._title = function(title) {
                        title.html( this.options.title );
                    };

                    switch(options.template) {
                        case "field":
                        {
                            settings_dialog.dialog('option', 'title', '<div id="icon-edit-pages" class="icon32 icon32-posts-page"></div><h2>'+translations_js.edit_field+'</h2>');
                        }
                        break;

                        case "container":
                        {
                            settings_dialog.dialog('option', 'title', '<div id="icon-edit-pages" class="icon32 icon32-posts-page"></div><h2>'+translations_js.edit_container+'</h2>');
                        }
                        break;

                        case "tab":
                        {
                            settings_dialog.dialog('option', 'title', '<div id="icon-edit-pages" class="icon32 icon32-posts-page"></div><h2>'+translations_js.edit_tab+'</h2>');
                        }
                        break;

                        default:
                        { }
                        break;
                    }

                    $('.settings-dialog').dialog({
                        open: function(event, ui) {
                            $(this).css({'max-height': 525, 'overflow-y': 'auto'}); 
			    
			    $('body').addClass('modal-open');
			    $('.ui-dialog').css({'top': '80px'});
                            $('#adminmenuwrap').css({'z-index':0});
			    
			    for(var key in builder.availableTypes) {
				if(typeof builder.availableTypes[key].onSettingsDialogOpen === 'function') {
				    builder.availableTypes[key].onSettingsDialogOpen();
				}

			    }
                        },
                        close: function(event, ui) {
                            $('#adminmenuwrap').css({'z-index':'99'});
			    $('body').removeClass('modal-open');
                        },
                        autoOpen:false,
                        modal: true,
                        resizable: false,
                        draggable: false,
                        closeOnEscape: true,
			dialogClass: "settings-dialog-modal",
                        position: ['center', 80]
                    });

                    // open field settings dialog                    
                    $('.settings-dialog').dialog('open');

                    $('.settings-dialog .accept-changes').focus();

                    $('.settings-dialog').keypress(function(e) {

                        if(e.keyCode == 13 && $(e['target']).data('set') != 'values') {
                            $('.settings-dialog .accept-changes').click();
                            return false;
                        }

                    });

                    uiObject.fillSettingsDialog(options);


                    /*
                     var objectIndex = $(this).parent().parent().parent().data('index');

                     var options = pageObject.getElement(objectIndex);

                     $(".settings-dialog-inside .settings-head").replaceWith($('#'+options.template+'-settings-head').tmpl(options));

                     $('.settings-dialog').find('[name="type"]').change(function () {
                     if(
                     $(this).val() == 'multiselect' ||
                     $(this).val() == 'checkbox' ||
                     $(this).val() == 'select') {

                     if($('[name="values"]').val() == '') {
                     $('[name="values"]').val('Example1 => example1\r\nExample2 => example2\r\nExample3 => example3');
                     }
                     } else {
                     $('[name="values"]').val() == '';
                     }

                     })*/

                });

                function htmlspecialchars_decode(text) {
                    var chars = Array("&quot;", "&#039;", "&gt;");
                    var replacements = Array('"', "'", ">");
                    for(var i = 0; i < chars.length; i++) {
                        var re = new RegExp(chars[i], "gi");
                        if(re.test(text)) {
                            text = text.replace(re, replacements[i]);
                        }
                    }
                    return text;
                }

                $('body').on('click', '.save-button', function() {

                    var settings = $('.page-settings-form').serializeArray();

                    settings.push({
                        name: "title",
                        value: $('#titlewrap #title').val()
                    });
                    settings.push({
                        name: "alias",
                        value: $('.dynamic-page-title').text()
                    });
                    settings.push({
                        name: "showPageTitle",
                        value: $('[name="showPageTitle"]').prop('checked')
                    });

                    pageObject.savePageSettings(settings);

                    var page = pageObject.getStructure();
                    $(".save-page").prop("action", 'admin.php?page=options-builder&navigation=edit-page&page_id=' + page.settings.page_id);
                    //                    settings.push({name: "separate", value: builder.availableTypes['input-text'].separate});
                    var priority = 1;
                    for(var key in page.elements) {
                        page.elements[key].title = htmlspecialchars_decode(page.elements[key].title);
                        page.elements[key].values = htmlspecialchars_decode(page.elements[key].values);
                        page.elements[key].requiredMessage = htmlspecialchars_decode(page.elements[key].requiredMessage);
                        page.elements[key].validationMessage = htmlspecialchars_decode(page.elements[key].validationMessage);
                        page.elements[key].cssClass = htmlspecialchars_decode(page.elements[key].cssClass);
                        page.elements[key].titleCaption = htmlspecialchars_decode(page.elements[key].titleCaption);
                        page.elements[key].fieldCaption = htmlspecialchars_decode(page.elements[key].fieldCaption);
                        page.elements[key].separate = '';
                        if(page.elements[key].template == 'container')
                            page.elements[key].priority = priority++;
                        if(page.elements[key].template == 'field' && page.elements[key] != 'none') {
                            if(builder.availableTypes[page.elements[key].type].separate != undefined) {
                                page.elements[key].separate = builder.availableTypes[page.elements[key].type].separate;
                            }
                        }
                    }
                    $(".save-page input.page").val(JSON.stringify(page));
                
                    $(".save-page").append($(".file-upload input"));
                    $(".save-page").append($('.tab-settings'));

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,                        
                        data: {
                            action: SAVE_ACTION,
                            json_form: JSON.stringify(page),
                            page_id : page.settings.page_id
                        }
                    }).done(function(response){
			            ajax_result = $.parseJSON(response);
			            if(ajax_result.page_alias !== "")
				            page.settings.alias = ajax_result.page_alias;
			
                        $.ajax({
                            type: 'post',
                            url: ajaxurl, 
                            data: {
                                action: 'add_page_to_pages_list',
                                json_form: JSON.stringify(page),
                                page_id : page.settings.page_id
                            }
                        });
                        
                        window.location = ajax_result.reload_url;
                    });
                });

                $('.page-preview').on('click', function() {

                    window.open('admin.php?page=' + pageObject.getPageSettings().alias);
                    return false;

                })

                $('body').on('click', '.accept-changes', function() {

                    //var current_options = uiObject.grabCurrentOptions($(this).parent());
                    var current_options = getFormData('.settings-head');                    

                    if(current_options.alias != ''){
                        current_options = uiObject.setDefaultForEmptyValue(current_options);
                        if(current_options == false)
                            return false;                

                        current_options = pageObject.updateOption(current_options);

                        // current_options.title = htmlspecialchars_decode(current_options.title);
                        // current_options.values = htmlspecialchars_decode(current_options.values);
                        // current_options.requiredMessage = htmlspecialchars_decode(current_options.requiredMessage);
                        // current_options.validationMessage = htmlspecialchars_decode(current_options.validationMessage);
                        // current_options.cssClass = htmlspecialchars_decode(current_options.cssClass);
                        // current_options.titleCaption = htmlspecialchars_decode(current_options.titleCaption);
                        // current_options.fieldCaption = htmlspecialchars_decode(current_options.fieldCaption);

                        uiObject.updatePageElement(current_options);

                        uiObject.settingsPreview(current_options);

                        $(".settings-dialog").dialog('close');
                    }
                    else{
                        $(this).parent().find('input[name="alias"]').css('border-color', 'red');
                    }

                });

                $('body').on('click', '.ui-widget-overlay', function() {
                    $(".settings-dialog").dialog('close');
                });

                // Click function for show/hide Alias field
                $('.edit-slug').click(function() {

                    $('.slug-editor-input').val($("#editable-post-name").text());
                    $('#slug-static').hide();
                    $('#slug-editor').show();

                    $('.slug-editor-save').click(function() {

                        var newAlias = $('.slug-editor-input').val();

                        $.ajax({
                            type: 'POST',
                            url: ajaxurl,                        
                            data: {
                                action: 'check_is_options_page_alias_unique',
                                alias: newAlias
                            }
                        }).done(function(response){
                            if(!response)
                                $("#editable-post-name").text(newAlias);
                        });

                        $('#slug-editor').hide();
                        $('#slug-static').show();
                    });
                    $('.slug-editor-cancel').on('click', function(){
                        $('#slug-editor').hide();
                        $('#slug-static').show();
                    });

                });

                // Click function to make slug from page title
                $('.make-from-title').click(function(){
                    var val = $('#title').val();
                    val = val.toLowerCase();
                    val = val.trim();
                    val = val.replace(/[^\sa-z-]+/gi,'');
                    val = val.trim();
                    val = val.replace(/\s+/g, '-');

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'check_is_options_page_alias_unique',
                            alias: val
                        }
                    }).done(function(response){
                        if(!response){
                            $("#editable-post-name").text(val);
                            $('#slug-editor').hide();
                            $('#slug-static').show();
                        }
                    });
                });

                // Click function to get primary page slug
                $('.get-primary-slug').on('click', function(){
                    $("#editable-post-name").text($('input[name="primary-page-slug"]').val());
                });

                // preload page settings
                $('.page-global-settings').html($("#page-settings-template").tmpl(pageObject.getPageSettings()));

                // control tabs switcher
                $('.nav-tabs a').on('click', function() {

                    if($(this).hasClass('nav-tab-active')) {
                        return false;
                    } else {

                        // set active tab
                        $('.nav-tabs a').removeClass('nav-tab-active');
                        $(this).addClass('nav-tab-active');
                        
                        $(".elements-list, .page-layer, .page-global-settings, .tabIn").hide();
                        $("." + $(this).data('switchto')).show();
                    }

                    return false;

                });


                function switchCustomIconAdvanced() {
                    if($('.icon-select').val() == 'custom-icon') {
                        $('.custom-icon-ob').show();
                    } else {
                        $('.custom-icon-ob').hide();
                    }
                }

                $('.icon-select').on("change", function() {
                    switchCustomIconAdvanced();
                });

                switchCustomIconAdvanced();

            }

        };

        return godObj;

    })();


    // load
    $(function() {
        if(typeof(pageJSON) != 'undefined') {
            if(pageObject.restore(pageJSON)) {
                $(".page-layer").append($("<div>").addClass("first"));
            }
        }

        uiObject.init();
        uiObject.initOther();

        uiObject.onAddTab();

    });

    builder = {
        // /**/ //
        page: pageObject,
        ui: uiObject,
        // /**/ //
        out: function(data) {
            console.log(data);
        },
        availableTypes: {},
        registerDataType: function(type) {
            if(builder.availableTypes[type.alias] !== undefined) return this.out(translations_js.type_with_this_alias+" " + options.alias + " "+translations_js.already_exists+".");
            else this.availableTypes[type.alias] = type;
        }
    };

    function empty(mixed_var) {
        // Checks if the argument variable is empty
        // undefined, null, false, number 0, empty string,
        // string "0", objects without properties and empty arrays
        // are considered empty
        //
        // http://kevin.vanzonneveld.net
        // +   original by: Philippe Baumann
        // +      input by: Onno Marsman
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +      input by: LH
        // +   improved by: Onno Marsman
        // +   improved by: Francesco
        // +   improved by: Marc Jansen
        // +      input by: Stoyan Kyosev (http://www.svest.org/)
        // +   improved by: Rafal Kukawski
        // *     example 1: empty(null);
        // *     returns 1: true
        // *     example 2: empty(undefined);
        // *     returns 2: true
        // *     example 3: empty([]);
        // *     returns 3: true
        // *     example 4: empty({});
        // *     returns 4: true
        // *     example 5: empty({'aFunc' : function () { alert('humpty'); } });
        // *     returns 5: false
        var undef, key, i, len;
        var emptyValues = [undef, null, false, 0, "", "0"];

        for(i = 0, len = emptyValues.length; i < len; i++) {
            if(mixed_var === emptyValues[i]) {
                return true;
            }
        }

        if(typeof mixed_var === "object") {
            for(key in mixed_var) {
                //if (mixed_var.hasOwnProperty(key)) {
                return false;
                //}
            }
            return true;
        }

        return false;
    }

})(jQuery);