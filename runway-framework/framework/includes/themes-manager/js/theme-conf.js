(function($){
	$(document).ready(function(){
		$('.input-select').change(function(){
			if($(this).val() == 'custom-icon'){
				$('.custom-icon-upload').show();
			}
			else{
				$('.custom-icon-upload').hide();	
			}
		});
		
		if($('.input-select').val() == 'default-wordpress-icon'){
			$('.choose-default-wordpress').show();
		}			
		if($('.input-select').val() == 'custom-icon'){
			$('.choose-another').show();
		}

		$('.input-select').change(function(){
			if($('.input-select').val() == 'default-wordpress-icon')
				$('.choose-another').hide();
			else
				$('.choose-another').show();
			$('.custom-icon-upload').hide();
			$('.choose-default-wordpress').toggle();
		});

		$('.choose-another-link').click(function(e){
			e.preventDefault();
			$('.choose-another').hide();
			$('.custom-icon-upload').show();

		});

		$("#menu_icon").val('menu-icon-page').attr('selected',true);						
	});
})(jQuery);