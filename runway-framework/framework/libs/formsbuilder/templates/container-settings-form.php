<script id="container-settings" type="text/x-jquery-tmpl">

    <form class="settings-head">

        <input data-set="index" name="index" type="hidden" value="${index}">

        <div class="settings-container">
            <label class="settings-title">
                <?php _e( 'Container title', 'runway' ); ?>:<br>
                <span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">
                <input class="settings-input" data-set="title" type="text" name="title" value="${title}" /><br>
                <span class="settings-field-caption"></span>
            </div>
        </div>

        <div class="clear"></div>

        <div class="settings-container">
            <label class="settings-title">
                <?php _e( 'Container type', 'runway' ); ?>: <br>
                <span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">
                <select name="type" class="settings-select">
                    <option {{if type == 'visible'}} selected="selected" {{/if}} value="visible">
                        <?php _e( 'Visible', 'runway' ); ?>
                    </option>
                    <option {{if type == 'invisible'}} selected="selected" {{/if}} value="invisible">
                        <?php _e( 'Invisible', 'runway' ); ?>
                    </option>
                    <option {{if type == 'show-hide'}} selected="selected" {{/if}} value="show-hide">
                        <?php _e( 'Show / hide', 'runway' ); ?>
                    </option>
                </select><br>
                <span class="settings-field-caption"></span>
            </div>
        </div>

        <div class="clear"></div>

        <div class="settings-container">
        <label class="settings-title">
            <?php _e( 'Theme customizer', 'runway' ); ?>:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">

            <label>
                {{if typeof display_on_customization_page != 'undefined' && display_on_customization_page == 'true'}}
                    <input data-set="display_on_customization_page" name="display_on_customization_page" value="true" checked="true" type="checkbox">
                {{else}}
                    <input data-set="display_on_customization_page" name="display_on_customization_page" value="true" type="checkbox">
                {{/if}}
                <?php _e( 'Yes', 'runway' ); ?>
            </label>
            <p><?php _e( 'Include this section in the Theme Customizer options panel.', 'runway' ); ?></p>

        </div>

    </div><div class="clear"></div>

    </form>

</script>
