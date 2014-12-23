jQuery(document).ready(function($){

	$.fn.slideFadeToggle = function(easing, callback) {
	  var key = this.parent().find('.add-ons-installed').attr('data-key');
	  $('.add-ons-installed-menu').not('.'+key+'-installed-item').hide();
	  return this.animate({ opacity: 'toggle', height: 'toggle' }, 'fast', easing, callback);
	};

	$('.add-ons-installed').on('click', function(e){
		e.preventDefault();
		e.stopPropagation();

		var key = $(this).attr('data-key');
		$('.'+key+'-installed-item.add-ons-installed-menu').slideFadeToggle();
	});

	$(document).click(function(event) {
		var target = $( event.target );
	  	if(!target.hasClass('add-ons-installed-menu')){
	  	   $('.add-ons-installed-menu').hide();
	  	}
	});	
});