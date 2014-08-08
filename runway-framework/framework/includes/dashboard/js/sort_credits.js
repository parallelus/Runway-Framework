jQuery(document).ready(function($) {
	$('.credits-sort').on('change', function(e) {
		e.preventDefault();
		$('#credits_sort_form').submit();
	})
});