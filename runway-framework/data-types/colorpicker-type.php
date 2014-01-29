<?php
class Colorpicker_type extends Data_Type {

    public $type = 'colorpicker-type';
    public static $type_slug = 'colorpicker-type';
    public $label = 'Colorpicker';

    public function render_content( $vals = null ) {

        do_action( self::$type_slug . '_before_render_content', $this );

        if ( $vals != null ) {
            $this->field = (object)$vals;
            extract( $vals );
        }

        $section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
?>

        <label>
            <div class="customize-control-content">
                <legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>
                <input class="color-picker-hex custom-data-type" <?php echo $section; ?> data-type="colorpicker-type" type="text" maxlength="7" <?php $this->link(); ?> name="<?php echo $this->field->alias ?>" value="<?php echo ( $vals != null ) ? $this->field->saved : $this->get_value(); ?>" />
                <script type="text/javascript">
                    (function () {

                        var name = '<?php echo $this->field->alias; ?>';

                        jQuery(function () {

                            jQuery('[name="'+name+'"]').wpColorPicker({ change: function () {

                                setTimeout(function () {

                                    jQuery('[name="'+name+'"]').trigger('change');

                                }, 50);

                            }});

                        });

                    })();
                </script>
            </div>
        </label> <?php

        do_action( self::$type_slug . '_after_render_content', $this );
    }

    public function enable_repeating($field_name){
        $add_id = 'add_'.$field_name;
        $del_id = 'del_'.$field_name;
        ?>
            <div id="<?php echo $add_id; ?>">
                <a href="#">
                    Add Field
                </a>
            </div>

            <script type="text/javascript">
                (function($){
                    $(document).ready(function(){
                        $('#<?php echo $add_id; ?>').click(function(e){
                            e.preventDefault();
                            var field = $('<input/>', {
                                type: 'text',
                                class: 'input-text custom-data-type',
                                name: '<?php echo $field_name; ?>[]'
                            })                          
                            .attr('data-type', 'input-text')
                            .insertBefore($(this));

                            field.after('<a href="#" class="delete_field">Delete</a><br>');                         
                        });

                        $('body').on('click', '.delete_field', function(e){
                            e.preventDefault();
                            $(this).prev('input').remove();
                            $(this).next('br').remove();
                            $(this).remove();
                        });
                    });
                })(jQuery);
            </script>
        <?php
    }

    public function save( $value = '' ) {
        if ( $value == '' ) {
            $submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
            $value = $submited_value->{$this->field->alias};
        }

        if ( strstr( $value, '#' ) == false ) {
            $value = '#' . $value;
        }

        parent::save( $value );

    }

    public function get_value() {

        $value = parent::get_value();

        if ( is_array( $value ) )
            $value = ( isset( $this->field->values ) ) ? $this->field->values : '';
        if ( strstr( $value, '#' ) === false ) {
            $value = '#' . $value;
        }

        return $value;

    }

    public static function render_settings() { ?>

    <script id="colorpicker-type" type="text/x-jquery-tmpl">

        <?php do_action( self::$type_slug . '_before_render_settings' ); ?>

    <div class="settings-container">
        <label class="settings-title">
            Values:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">
            <input name="values" value="${values}" class="settings-input color-picker" type="text" maxlength="7">
            <br><span class="settings-field-caption"></span>
        </div>
    </div><div class="clear"></div>

    <div class="settings-container">
        <label class="settings-title">
            Required:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">

            <label>
                {{if required == 'Yes'}}
                <input data-set="required" name="required" value="Yes" checked="true" type="checkbox">
                {{else}}
                <input data-set="required" name="required" value="Yes" type="checkbox">
                {{/if}}
                Yes
            </label>

            <br><span class="settings-field-caption">Is this a required field.</span><br>

            <input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

            <br><span class="settings-field-caption">Optional. Enter a custom error message.</span>
        </div>
    </div><div class="clear"></div>

    <!-- Repeating settings -->
    <div class="settings-container">
        <label class="settings-title">
            Repeating:                  
        </label>
        <div class="settings-in">
            <label class="settings-title"> 
                {{if repeating == 'Yes'}}
                    <input data-set="repeating" name="repeating" value="Yes" checked="true" type="checkbox">
                {{else}}
                    <input data-set="repeating" name="repeating" value="Yes" type="checkbox">
                {{/if}}
                Yes
            </label>
            <br><span class="settings-title-caption">Can this field repeat with multiple values.</span>
        </div>
    </div><div class="clear"></div>

    <?php do_action( self::$type_slug . '_after_render_settings' ); ?>

</script>

    <?php }

    public static function data_type_register() { ?>

        <script type="text/javascript">

            function colorPickerInit() {

                setTimeout(function () {
                    jQuery('.color-picker').wpColorPicker();
                    jQuery('.settings-select').one('change', colorPickerInit());
                }, 200);

            }

            jQuery(document).ready(function ($) {
                builder.registerDataType({
                    name: 'Colorpicker',
                    alias: '<?php echo self::$type_slug ?>',
                    settingsFormTemplateID: '<?php echo self::$type_slug ?>',
                    onSettingsDialogOpen: function () {
                        colorPickerInit();
                    }
                });
            });

        </script>

    <?php }
} ?>
