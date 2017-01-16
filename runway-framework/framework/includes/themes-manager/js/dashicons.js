(function ($) {

    $(document).ready(function ($) {

        var code_selected = $('.dashicon-code-selected').val();

        if (code_selected != false && code_selected != '' && code_selected != undefined) {
            $('[data-code=' + code_selected + ']').addClass('active');
        }

        $('#iconlist .dashicons').on('click', function () {
            var $this = $(this);
            var data_code = $this.attr('data-code');
            var classNames = $this.attr('class').toString().split(' ');

            $('.dashicons').removeClass('active');
            $this.addClass('active');

            $('.dashicon-code-selected').val(data_code);
            $('.dashicon-class-selected').val(classNames[1]);
        });

    });

})(jQuery);
