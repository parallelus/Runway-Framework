<script id="field-settings" type="text/x-jquery-tmpl">
    <form class="settings-head">

        <input data-set="index" name="index" type="hidden" value="${index}">

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Title', 'framework'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="title" name="title" value="${title}" class="settings-input" type="text">

                <br><span class="settings-field-caption"></span>

            </div>

        </div><div class="clear"></div>

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Title caption', 'framework'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="titleCaption" name="titleCaption" value="${titleCaption}" class="settings-input" type="text">

                <br><span class="settings-field-caption"></span>

            </div>

        </div><div class="clear"></div>

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Alias', 'framework'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="alias" name="alias" value="${alias}" class="settings-input" type="text">

                <br><span class="settings-field-caption"></span>

            </div>

        </div><div class="clear"></div>

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Field type', 'framework'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in" >

                <select data-set="type" name="type" class="settings-select">
                    {{if false}}
                        <option value="hidden" {{if type == 'hidden'}}selected="true"{{/if}}>_<?php echo __('Hidden', 'framework'); ?></option>
                        <option value="input" {{if type == 'input'}}selected="true"{{/if}}>_<?php echo __('Text input', 'framework'); ?></option>
                        <option value="textarea" {{if type == 'textarea'}}selected="true"{{/if}}>_<?php echo __('Textarea', 'framework'); ?></option>
                        <option value="checkbox" {{if type == 'checkbox'}}selected="true"{{/if}}>_<?php echo __('Checkbox', 'framework'); ?></option>
                        <option value="checkbox_boolean" {{if type == 'checkbox_boolean'}}selected="true"{{/if}}>_<?php echo __('Checkbox boolean', 'framework'); ?></option>
                        <option value="radio" {{if type == 'radio'}}selected="true"{{/if}}>_<?php echo __('Radio buttons', 'framework'); ?></option>
                        <option value="radioimage" {{if type == 'radioimage'}}selected="true"{{/if}}>_<?php echo __('Image radio buttons', 'framework'); ?></option>
                        <option value="select" {{if type == 'select'}}selected="true"{{/if}}>_<?php echo __('Select', 'framework'); ?></option>
                        <option value="multiselect" {{if type == 'multiselect'}}selected="true"{{/if}}>_<?php echo __('Multiselect', 'framework'); ?></option>
                        <option value="file" {{if type == 'file'}}selected="true"{{/if}}>_<?php echo __('File upload', 'framework'); ?></option>
                        <option value="colorpicker" {{if type == 'colorpicker'}}selected="true"{{/if}}>_<?php echo __('Colorpicker', 'framework'); ?></option>
                        <option value="datepicker" {{if type == 'datepicker'}}selected="true"{{/if}}>_<?php echo __('Datepicker', 'framework'); ?></option>
                        <option value="blogedit" {{if type == 'blogedit'}}selected="true"{{/if}}>_<?php echo __('Blog editor', 'framework'); ?></option>
                    {{/if}}

                    {{each(key, value) builder.availableTypes}}
                        <option value="${value.alias}" {{if type == value.alias}}selected="true"{{/if}}>${value.name}</option>
                    {{/each}}

                </select>

                <br><span class="settings-field-caption"></span>

            </div>

        </div><div class="clear"></div>

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Field caption', 'framework'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="fieldCaption" name="fieldCaption" value="${fieldCaption}" class="settings-input" type="text">

                <br><span class="settings-field-caption"></span>

            </div>

        </div><div class="clear"></div>

        <span class="field-settings-form-middle-section"></span>
    </form>
</script>
