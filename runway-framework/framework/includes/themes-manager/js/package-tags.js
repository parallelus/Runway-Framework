jQuery( document ).ready(function( $ ) {

    $( ".tags-dialog" ).dialog( {
      	autoOpen: false,
	    height: 300,
		width: 350,
		modal: true,
		resizable: false,
		draggable: false,
		closeOnEscape: true,  
		open: function(event, ui) {
			$(this).css({'max-height': 500});
			$('ul').css({'z-index':0}); 
			$('#adminmenuwrap').css({'z-index':0});
            $('html,body').css('overflow', 'hidden');
		},
		close: function(event, ui) {
            $('html,body').css('overflow', 'auto');
			$('#adminmenuwrap').css({'z-index':'auto'});
            $(this).find('textarea').val('');
        },
    });


    $( ".rebuild-package" ).click(function(e) {

      	e.preventDefault();
		$( ".tags-dialog" ).dialog('option', 'title', 'Package Options');
      	$( "#tags-save" ).text('Create Package');
      	$( "#tags-save" ).val('add');
        $( ".tags-dialog" ).dialog( "open" );
    });

    $( ".link-tags-edit" ).click(function(e) {

      	e.preventDefault();
      	var params = getURLParameters( $(this).attr("href") );
      	var id = params.package;

		$.ajax({
			url: ajaxurl,
			data: {
				action:'get_package_tags',
				id: id,
			}
		}).done(function(response){
			var data = $.parseJSON($.trim(response));

			$( "#package-id" ).val(id);
			if(data.tags_show == "true")
				$('#tags-show').attr("checked","checked");

			$('#tags-edit').val(data.tags_edit);
	       	$( ".tags-dialog" ).dialog('option', 'title', 'Edit Tags');
	       	$( "#tags-save" ).text('Update Tags');
	       	$( "#tags-save" ).val('edit');
	        $( ".tags-dialog" ).dialog( "open" );
		});
    });


  	$('#tags-save').click(function(e){	
  		
  		var package_id = $( "#package-id" ).val();
		var tags = update_tags( package_id );
	});

  	function update_tags( id ) {

		var tags_show =  true;
		var tags_edit = $('#tags-edit').val();
		var tags_mode = $( "#tags-save" ).val();
      	var redirect;

		$.ajax({
			url: ajaxurl,
			data: {
				action:'update_package_tags',
				id: id,
				tags_show: tags_show,
				tags_edit: tags_edit
			}
		}).done(function(response){
			$('.tags-dialog').dialog('close');
			params = getURLParameters( $(location).attr('href') );
			if(tags_mode == 'add') {
      			params = getURLParameters( $(location).attr('href') );
				redirect = "admin.php?page=themes&action=rebuild&navigation=do-package&name="+params.name+"&tags_show="+tags_show+"&tags_edit="+tags_edit;
			}
			else
				redirect = "admin.php?page=themes&navigation=do-package&name="+params.name;
			window.location = redirect;
		});
  	}

	function getURLParameters(url) {

	    var result = {};
	    var searchIndex = url.indexOf("?");
	    if (searchIndex == -1 ) 
	    	return result;
	    var sPageURL = url.substring(searchIndex +1);
	    var sURLVariables = sPageURL.split('&');
	    for (var i = 0; i < sURLVariables.length; i++) {       
	      var sParameterName = sURLVariables[i].split('=');      
	      result[sParameterName[0]] = sParameterName[1];
	    }
	    return result;
	}

});