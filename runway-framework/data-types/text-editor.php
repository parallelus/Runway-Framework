<?php

class Text_editor extends Data_Type {

    public static $type_slug = 'text-editor';

    public function __construct( $page, $field, $wp_customize = null, $alias = null, $params = null ) {

        $this->type  = 'text-editor';
        $this->label = 'Text Editor';

        parent::__construct( $page, $field, $wp_customize, $alias, $params );

    }

    public function render_content( $vals = null ) {

        do_action( self::$type_slug . '_before_render_content', $this );

        if ( $vals != null ) {
            $this->field = (object) $vals;
        }

        $value   = ( $vals != null ) ? $this->field->saved : $this->get_value();
        $section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="' . esc_attr( $this->page->section ) . '"' : '';
        ob_start();

        wp_editor( htmlspecialchars_decode( is_string( $value ) ? $value : '' ), $this->field->alias, array(
            'data-section' => $section
        ) );

        $this->link();
        $html = ob_get_contents();
        ob_end_clean();

        echo rf_string( $html ); ?>

        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                var $editorArea = $('textarea.wp-editor-area');

                $editorArea.addClass('custom-data-type');
                $editorArea.attr('data-section', '<?php echo isset( $this->page->section ) ? $this->page->section : '' ?>');
                $editorArea.attr('data-type', 'texteditor-type');

                <?php if( isset( $this->field->conditionalAlias ) && !empty( $this->field->conditionalAlias ) ): ?>
                $editorArea.attr('data-conditionalAlias', '<?php echo esc_js( $this->field->conditionalAlias ); ?>');
                <?php endif; if( isset( $this->field->conditionalValue ) && !empty( $this->field->conditionalValue ) ): ?>
                $editorArea.attr('data-conditionalValue', '<?php echo esc_js( $this->field->conditionalValue ); ?>');
                <?php endif; if( isset( $this->field->conditionalAction ) && !empty( $this->field->conditionalAction ) ): ?>
                $editorArea.attr('data-conditionalAction', '<?php echo esc_js( $this->field->conditionalAction ); ?>');
                <?php endif; ?>

                try {

                    // Fix the version compatibility issue for jquery-ui:
                    // check if method 'instance' exists
                    if ($.ui && $.ui.autocomplete) {
                        $('<input>').autocomplete().autocomplete('instance');
                    }

                } catch (e) {

                    // add it
                    $.widget('ui.autocomplete', $.ui.autocomplete, {
                        instance: function () {

                            var fullName = this.widgetFullName || '';
                            var returnValue = this;

                            if (!this.length) {
                                returnValue = undefined;
                            } else {
                                this.each(function () {
                                    returnValue = $.data(this, fullName);
                                    return false;
                                });
                            }

                            this._renderItem = function (ul, item) {
                                return $('<li role="option" id="mce-wp-autocomplete-' + item.ID + '">')
                                    .append('<span>' + item.title + '</span>&nbsp;<span class="wp-editor-float-right">' + item.info + '</span>')
                                    .appendTo(ul);
                            };

                            return returnValue;

                        }
                    });

                }
            })
        </script>

        <?php

        $this->wp_customize_js();
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

    public function link( $setting_key = 'default' ) {
        ?>

        <script type="text/javascript">

            (function () {

                var name = '<?php echo esc_attr( $this->field->alias ); ?>';

                jQuery('[name="' + name + '"]')
                    .attr('data-customize-setting-link', name);

            })();

        </script>

        <?php
    }

    public static function render_settings() {
        ?>

        <script id="text-editor" type="text/x-jquery-tmpl">

            <?php do_action( self::$type_slug . '_before_render_settings' ); ?>

            <div class="settings-container">
                <label class="settings-title">
                    <?php echo __( 'Values', 'runway' ); ?>:
                    <br><span class="settings-title-caption"></span>
                </label>
                <div class="settings-in">

                    <textarea data-set="values" name="values" class="settings-textarea">${values}</textarea>

                </div>
                <div class="clear"></div>
            </div>

            <div class="settings-container">
                <label class="settings-title">
                    <?php echo __( 'Required', 'runway' ); ?>:
                    <br><span class="settings-title-caption"></span>
                </label>
                <div class="settings-in">

                    <label>
                        {{if required == 'true'}}
                            <input data-set="required" name="required" value="true" checked="true" type="checkbox">
                        {{else}}
                            <input data-set="required" name="required" value="true" type="checkbox">
                        {{/if}}
                        <?php echo __( 'Yes', 'runway' ); ?>
                    </label>

                    <span class="settings-field-caption"><?php echo __( 'Is this a required field?', 'runway' ); ?></span><br>

                    <input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

                    <span class="settings-field-caption"><?php echo __( 'Optional. Enter a custom error message.', 'runway' ); ?></span>

                </div>
                <div class="clear"></div>

            </div>

            <?php
            parent::render_conditional_display();
            do_action( self::$type_slug . '_after_render_settings' );
            ?>

        </script>

        <?php
    }

    public function get_value() {

        $value = parent::get_value();

        if ( is_string( $value ) ) {
            return $value;
        } else {
            return isset( $this->field->values ) ? $this->field->values : '';
        }

    }

    public static function data_type_register() {
        ?>

        <script type="text/javascript">

            jQuery(document).ready(function ($) {
                builder.registerDataType({
                    name: '<?php echo __( 'Text editor', 'runway' ); ?>',
                    separate: 'none',
                    alias: '<?php echo self::$type_slug ?>',
                    settingsFormTemplateID: '<?php echo self::$type_slug ?>'
                });
            });

        </script>

        <?php
    }

    public function wp_customize_js() {
        ?>

        <script type="text/javascript">
            (function ($) {
                // hide editor
                $('#customize-control-<?php echo esc_js( $this->field->alias ); ?>').hide();
            })(jQuery);
        </script>

        <?php
    }

}
