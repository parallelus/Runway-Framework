(function ($) {

    $(document).ready(function () {
        var $inputSelect = $('.input-select');
        var $customIconUpload = $('.custom-icon-upload');
        var $chooseAnother = $('.choose-another');
        var $chooseDefaultWP = $('.choose-default-wordpress');

        $inputSelect.change(function () {
            if ($(this).val() == 'custom-icon') {
                $customIconUpload.show();
            }
            else {
                $customIconUpload.hide();
            }
        });

        if ($inputSelect.val() == 'default-wordpress-icon') {
            $chooseDefaultWP.show();
        }

        if ($inputSelect.val() == 'custom-icon') {
            $chooseAnother.show();
        }

        $inputSelect.change(function () {
            if ($inputSelect.val() == 'default-wordpress-icon') {
                $chooseAnother.hide();
            } else {
                $chooseAnother.show();
            }

            $customIconUpload.hide();
            $chooseDefaultWP.toggle();
        });

        $('.choose-another-link').click(function (e) {
            e.preventDefault();

            $chooseAnother.hide();
            $customIconUpload.show();
        });

        $('#menu_icon').val('menu-icon-page').attr('selected', true);
    });

})(jQuery);
