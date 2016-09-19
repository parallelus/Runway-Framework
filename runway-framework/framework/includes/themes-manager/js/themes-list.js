function in_array(what, where) {
	for(var i=0; i<where.length; i++)
		if(what == where[i])
			return true;
	return false;
}

function popup_loader() {
    jQuery('html,body').css('overflow', 'auto');
    jQuery('#adminmenuwrap').css({'z-index':'auto'});
	var $dlg = jQuery(".loader").dialog({
        open: function(event, ui) {
            jQuery('html,body').css('overflow', 'hidden');
            jQuery('#adminmenuwrap').css({'z-index':0});
        },
        close: function(event, ui) {
            jQuery('html,body').css('overflow', 'auto');
            jQuery('#adminmenuwrap').css({'z-index':'auto'});
        },				
		position: "center",
		modal: true,
		resizable: false,
		dialogClass: 'loaderPopup'
	});
	jQuery(".ui-dialog-titlebar").hide();
}

(function ($) {
	$(function () {

		$(".duplicate-theme").on("click", function () {

			var name = $(this).data("theme-folder");
			var adminMenuZIndex = $('#adminmenuwrap').css('z-index');

            $('html,body').css('overflow', 'auto');

			var $dlg = $(".duplicate-theme-popup").dialog({
                open: function(event, ui) {
                    $('html,body').css('overflow', 'hidden');
                    $('#adminmenuwrap').css({'z-index': 0});
                },
                close: function(event, ui) {
                    $('html,body').css('overflow', 'auto');
                    $('#adminmenuwrap').css({'z-index': adminMenuZIndex});
                },							
				//position: "center",
				modal: true,
				resizable: false,
				//dialogClass: 'duplicateThemePopup'
			});
			$(".ui-dialog-titlebar").hide();

			var currunt_theme_folder = $(this).data("theme-folder");
			
			var regex = new RegExp(""+currunt_theme_folder+"-copy-\d$", "g");

			var last_index = 0;
			
			if(currunt_theme_folder.match(regex)) {
				var tmp = currunt_theme_folder.split("-copy-");
				currunt_theme_folder = tmp[0];
			}

			var themes_list = [];

			$(".duplicate-theme").each(function () {

				themes_list.push($(this).data("theme-folder"));

				if($(this).data("theme-folder").match(regex)) {
					var tmp = $(this).data("theme-folder").split("-copy-");
					if(tmp.length >= 2) {
						tmp = tmp[tmp.length-1];
						if(last_index < tmp) {
							last_index = tmp;
						}
					}
				}

			});

			$(".duplicate-theme-popup #duplicate-theme-name").val(currunt_theme_folder + "-copy-" + (++last_index));

			$(".submit-theme-new-folder").on("click", function (e) {

				e.preventDefault();

				var new_name = $(".duplicate-theme-popup #duplicate-theme-name").val();
				if(new_name.length) {
					if(in_array(new_name, themes_list)) {
						var folder_exists_response = $(".duplicate-theme-popup #response-folder-name-exists").val();
						alert(folder_exists_response);
					} else {
						$dlg.dialog("close");
						var url = $(this).attr('href') + name + "&new_name=" + new_name;
						document.location = url;
					}
				}

			});

			$(".duplicate-theme-popup a.cancel-duplicate").on("click", function () {
				$dlg.dialog("close");
			});
		});

		$(".activate-theme").on("click", function () {

			popup_loader();
			$.ajax({url: $(this).attr("href"), success: function (responce) {
				document.location = "admin.php?page=themes";
			}});
			return false;
		});

		$(".cancel-activate-theme").on("click", function () {

			var $dlg = $(".enable-theme-popup").dialog({
				position: "center",
				modal: true,
				resizable: false,
				dialogClass: 'activateThemePopup'
			});
			$(".ui-dialog-titlebar").hide();

			$(".enable-theme-popup a").on("click", function () {
				$dlg.dialog("close");
			});
			$('.enable-theme-popup .themeActionsPupup').show();
		});

	});
})(jQuery);