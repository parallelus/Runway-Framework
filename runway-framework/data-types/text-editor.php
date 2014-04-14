<?php
class Text_editor extends Data_Type {

    public $type = 'text-editor';
    public static $type_slug = 'text-editor';
    public $label = 'Text Editor';

    public function render_content( $vals = null ) {

        do_action( self::$type_slug . '_before_render_content', $this );

        if ( $vals != null ) {
            $this->field = (object)$vals;
        }

        $value = ( $vals != null ) ? $this->field->saved : $this->get_value();
        $section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
        ob_start();

        wp_editor( htmlspecialchars_decode( is_string( $value )? $value : '' ), $this->field->alias, array(
            'data-section' => isset( $this->page->section ) ? $this->page->section : ''
        ) );
        $this->link();
        $html = ob_get_contents();
        ob_end_clean();

        echo $html; ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('textarea.wp-editor-area').addClass('custom-data-type');
                $('textarea.wp-editor-area').attr('data-section', '<?php echo isset( $this->page->section ) ? $this->page->section : '' ?>' );
                $('textarea.wp-editor-area').attr('data-type', 'texteditor-type' );
            })
        </script>
        <?php

        do_action( self::$type_slug . '_after_render_content', $this );

    }

    public static function assign_actions_and_filters() {

        global $wp_embed;

        add_filter( 'get_options_data_type_' . self::$type_slug, 'htmlspecialchars_decode', 5 );

        add_filter( 'get_options_data_type_' . self::$type_slug, array( $wp_embed, 'run_shortcode' ), 8 );
        add_filter( 'get_options_data_type_' . self::$type_slug, array( $wp_embed, 'autoembed' ), 8 );

        add_filter( 'get_options_data_type_' . self::$type_slug, 'wptexturize', 10 );
        add_filter( 'get_options_data_type_' . self::$type_slug, 'convert_smilies', 10 );
        add_filter( 'get_options_data_type_' . self::$type_slug, 'convert_chars', 10 );
        add_filter( 'get_options_data_type_' . self::$type_slug, 'wpautop', 10 );
        add_filter( 'get_options_data_type_' . self::$type_slug, 'shortcode_unautop', 10 );
        add_filter( 'get_options_data_type_' . self::$type_slug, 'prepend_attachment', 10 );

        add_filter( 'get_options_data_type_' . self::$type_slug, 'capital_P_dangit', 11 );
        add_filter( 'get_options_data_type_' . self::$type_slug, 'do_shortcode', 11 );

    }

    public function link( $setting_key = 'default' ) { ?><script type="text/javascript">

        (function () {

            var name = '<?php echo $this->field->alias; ?>';

            jQuery('[name="'+name+'"]')
                .attr('data-customize-setting-link', name);

        })();

    </script><?php }

    public static function render_settings() { ?>

    <script id="text-editor" type="text/x-jquery-tmpl">

        <?php do_action( self::$type_slug . '_before_render_settings' ); ?>

    <div class="settings-container">
        <label class="settings-title">
            <?php echo __('Values', 'framework'); ?>:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">

            <textarea data-set="values" name="values" class="settings-textarea">${values}</textarea>

            <br><span class="settings-field-caption"></span>

        </div>

    </div><div class="clear"></div>

    <div class="settings-container">
        <label class="settings-title">
            <?php echo __('Required', 'framework'); ?>:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">

            <label>
                {{if required == 'true'}}
                <input data-set="required" name="required" value="true" checked="true" type="checkbox">
                {{else}}
                <input data-set="required" name="required" value="true" type="checkbox">
                {{/if}}
                <?php echo __('Yes', 'framework'); ?>
            </label>

            <br><span class="settings-field-caption"><?php echo __('Is this a required field', 'framework'); ?>.</span><br>

            <input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

            <br><span class="settings-field-caption"><?php echo __('Optional. Enter a custom error message', 'framework'); ?>.</span>

        </div>

    </div><div class="clear"></div>

    <?php do_action( self::$type_slug . '_after_render_settings' ); ?>

</script>

    <?php }

    public function get_value() {

        $value = parent::get_value();

        if ( is_string( $value ) ) {
            return $value;
        } else {
            return ( isset( $this->field->values ) ) ? $this->field->values : '';
        }

    }

    public static function data_type_register() { ?>

        <script type="text/javascript">

            jQuery(document).ready(function ($) {
                builder.registerDataType({
                    name: 'Text editor',
                    separate: 'none',
                    alias: '<?php echo self::$type_slug ?>',
                    settingsFormTemplateID: '<?php echo self::$type_slug ?>'
                });
            });

        </script>

    <?php }
} ?>
