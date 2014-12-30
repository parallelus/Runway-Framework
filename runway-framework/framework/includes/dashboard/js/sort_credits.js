jQuery(document).ready(function($) {
	$('.credits-sort').on('change', function(e) {
		e.preventDefault();
		$('#credits_sort_form').submit();
	});
	
	$('.tablenav-pages').on('click', '.first-page', function(e){
		$('#credits_sort_form').find('input[name=startPage]').val('0');
		e.preventDefault();
		$('#credits_sort_form').submit();
	});
	$('.tablenav-pages').on('click', '.prev-page', function(e){
		var page = $('#credits_sort_form').find('input[name=startPage]').val();
		page = parseInt(page) - 1;
		
		if(page <= 0)
			page = 0;
		$('#credits_sort_form').find('input[name=startPage]').val(page);
		e.preventDefault();
		$('#credits_sort_form').submit();
	});
	$('.next-page').on('click', function(e){
		var page = $('#credits_sort_form').find('input[name=startPage]').val();
		page = parseInt(page) + 1;
		var totalPages = $('.total-pages').text();
		
		if(page >= parseInt(totalPages) - 1)
			page = parseInt(totalPages) - 1;
		
		$('#credits_sort_form').find('input[name=startPage]').val(page);
		
		e.preventDefault();
		$('#credits_sort_form').submit();
	});
	$('.tablenav-pages').on('click', '.last-page', function(e){
		$('#credits_sort_form').find('input[name=startPage]').val(parseInt($('.total-pages').text()) - 1);
		e.preventDefault();
		$('#credits_sort_form').submit();
	});
	
	$('.tablenav-pages').on('keypress', '.current-page', function(e) {
		var code = e.keyCode || e.which;
		
		if(code == 13) {
			var page = parseInt($(this).val());
			page--;
			if(page <= 0)
			page = 0;
		
			var totalPages = $('.total-pages').text();
			if(page >= parseInt(totalPages) - 1)
				page = parseInt(totalPages) - 1;
			
			$('#credits_sort_form').find('input[name=startPage]').val(page);
			e.preventDefault();
			$('#credits_sort_form').submit();
		}
	});

	$('.tab-controlls a').click(function () {

		if(!$(this).hasClass('nav-tab-active')) {
			$('.tab-controlls a').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			$('.tab-active').removeClass('tab-active');
			$($(this).data('tabrel')).addClass('tab-active');
		}

		return false;
	});	

	$('#credits_sort_form').submit(function (e) {
		$('input[name=state]').val(1);
	});	

	if($('#credits_sort_form').find('input[name=state]').val() == 1)
		$('.tab-controlls a[href*=credits]').trigger("click");
});