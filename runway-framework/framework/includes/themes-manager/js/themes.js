/* global _wpThemeSettings, confirm */
window.wp = window.wp || {};

( function($) {

// Set up our namespace...
var themes, l10n;
themes = wp.themes = wp.themes || {};

// Store the theme data and settings for organized and quick access
// themes.data.settings, themes.data.themes, themes.data.l10n
themes.data = _wpThemeSettings;
l10n = themes.data.l10n;

var current_index = -1;
var max_index = themes.data.themes.length;

$('body').on('click', '.theme-browser .themes .theme', function() {
	current_index = $('.theme-browser .themes .theme').index($(this));
	
	for(var i=0; i<themes.data.themes.length; i++) {
		if(themes.data.themes[i].id == $(this).data('themeid')) {
			//$('#tmpl-theme-modal').tmpl(themes.data.themes[i]).appendTo('.theme-overlay');
			$('.theme-overlay').html($('#tmpl-theme-modal').tmpl(themes.data.themes[i]));
			break;
		}
	}
	
});

$('body').on('click', '.theme-overlay .theme-header .close', function(){
	$('.theme-overlay').html('');
	current_index = -1;
});

$('body').on('click', '.theme-overlay .theme-header .right', function(){
	current_index ++;
	
	if(current_index >= max_index)
		current_index = 0;
	var themeid = $('.theme-browser .themes .theme').eq(current_index).data('themeid');
	
	for(var i=0; i<themes.data.themes.length; i++) {
		if(themes.data.themes[i].id == themeid) {
			$('.theme-overlay').html($('#tmpl-theme-modal').tmpl(themes.data.themes[i]));
			break;
		}
	}
});

$('body').on('click', '.theme-overlay .theme-header .left', function(){
	current_index --;
	
	if(current_index < 0)
		current_index = max_index - 1;
	var themeid = $('.theme-browser .themes .theme').eq(current_index).data('themeid');
	
	for(var i=0; i<themes.data.themes.length; i++) {
		if(themes.data.themes[i].id == themeid) {
			$('.theme-overlay').html($('#tmpl-theme-modal').tmpl(themes.data.themes[i]));
			break;
		}
	}
});
    $('.themes .theme').on('click', '.dashicons',function(event){

        event.stopPropagation();

    });
    $('.theme').on('mouseover',function(){

        var _this = $(this);

        _this.find('div[class*=dashicons-container-]').on('mouseover', function(){

            var actionText = $(this).data('action-text');

            if(actionText){
                _this.find('.more-details .primary-text').hide();
                _this.find('.more-details .action-text').text($(this).data('action-text')).show();
            }

        }).on('mouseout', function(){

            _this.find('.more-details .action-text').hide();
            _this.find('.more-details .primary-text').show();

        });

    });

})( jQuery );