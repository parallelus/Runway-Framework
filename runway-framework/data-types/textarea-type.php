<?php
class Textarea_type extends Data_Type {

    public $type = 'textarea-type';
    public static $type_slug = 'textarea-type';
    public $label = 'Textarea';

    public function render_content( $vals = null ) {

        do_action( self::$type_slug . '_before_render_content', $this );

        if ( $vals != null ) {
            $this->field = (object)$vals;
        }

        $value = ( $vals != null ) ? $this->field->saved : $this->get_value();
        $section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
        $customize_title = stripslashes( $this->field->title );
?>
        <legend class='customize-control-title'><span><?php echo $customize_title; ?></span></legend>
        <textarea
            class="input-textarea<?php echo " " . $this->field->cssClass; ?> custom-data-type"
            <?php $this->link() ?>
            name="<?php echo $this->field->alias; ?>"
            <?php echo $section; ?>
            data-type='textarea-image'
            ><?php echo is_string( $value )? $value : ''; ?></textarea><?php

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

    public function get_value() {

        $value = parent::get_value();

        if ( is_string( $value ) ) {  // because string is array always
            return $value;
        } else {
            return ( isset( $this->field->values ) ) ? $this->field->values : '';
        }

    }

    public static function render_settings() { ?>

    <script id="textarea-type" type="text/x-jquery-tmpl">

        <?php do_action( self::$type_slug . '_before_render_settings' ); ?>

    <div class="settings-container">
        <label class="settings-title">
            Values:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">

            <textarea data-set="values" name="values" class="settings-textarea">${values}</textarea>

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
                {{if required == 'true'}}
                <input data-set="required" name="required" value="true" checked="true" type="checkbox">
                {{else}}
                <input data-set="required" name="required" value="true" type="checkbox">
                {{/if}}
                Yes
            </label>

            <br><span class="settings-field-caption">Is this a required field.</span><br>

            <input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

            <br><span class="settings-field-caption">Optional. Enter a custom error message.</span>

        </div>

    </div><div class="clear"></div>

    <div class="settings-container">
        <label class="settings-title">
            CSS Class:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">

            <input data-set="cssClass" name="cssClass" value="${cssClass}" class="settings-input" type="text">

            <br><span class="settings-field-caption"></span>

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

            jQuery(document).ready(function ($) {
                builder.registerDataType({
                    name: 'Textarea',
                    separate: 'none',
                    alias: '<?php echo self::$type_slug ?>',
                    settingsFormTemplateID: '<?php echo self::$type_slug ?>'
                });
            });

        </script>

    <?php }
} ?>
