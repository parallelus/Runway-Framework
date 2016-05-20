<script id="field-settings" type="text/x-jquery-tmpl">
    <form class="settings-head">

        <input data-set="index" name="index" type="hidden" value="${index}">

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Title', 'runway'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="title" name="title" value="${title}" class="settings-input" type="text">

            </div>
            <div class="clear"></div>

        </div>

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Title caption', 'runway'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="titleCaption" name="titleCaption" value="${titleCaption}" class="settings-input" type="text">

            </div>

        </div><div class="clear"></div>

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Alias', 'runway'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="alias" name="alias" value="${alias}" class="settings-input" type="text">

            </div>
            <div class="clear"></div>

        </div>

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Field type', 'runway'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in" >

                <select data-set="type" name="type" class="settings-select">
                    {{if false}}
                        <option value="hidden" {{if type == 'hidden'}}selected="true"{{/if}}>_<?php echo __('Hidden', 'runway'); ?></option>
                        <option value="input" {{if type == 'input'}}selected="true"{{/if}}>_<?php echo __('Text input', 'runway'); ?></option>
                        <option value="textarea" {{if type == 'textarea'}}selected="true"{{/if}}>_<?php echo __('Textarea', 'runway'); ?></option>
                        <option value="checkbox" {{if type == 'checkbox'}}selected="true"{{/if}}>_<?php echo __('Checkbox', 'runway'); ?></option>
                        <option value="checkbox_boolean" {{if type == 'checkbox_boolean'}}selected="true"{{/if}}>_<?php echo __('Checkbox boolean', 'runway'); ?></option>
                        <option value="radio" {{if type == 'radio'}}selected="true"{{/if}}>_<?php echo __('Radio buttons', 'runway'); ?></option>
                        <option value="radioimage" {{if type == 'radioimage'}}selected="true"{{/if}}>_<?php echo __('Image radio buttons', 'runway'); ?></option>
                        <option value="select" {{if type == 'select'}}selected="true"{{/if}}>_<?php echo __('Select', 'runway'); ?></option>
                        <option value="multiselect" {{if type == 'multiselect'}}selected="true"{{/if}}>_<?php echo __('Multiselect', 'runway'); ?></option>
                        <option value="file" {{if type == 'file'}}selected="true"{{/if}}>_<?php echo __('File upload', 'runway'); ?></option>
                        <option value="colorpicker" {{if type == 'colorpicker'}}selected="true"{{/if}}>_<?php echo __('Colorpicker', 'runway'); ?></option>
                        <option value="datepicker" {{if type == 'datepicker'}}selected="true"{{/if}}>_<?php echo __('Datepicker', 'runway'); ?></option>
                        <option value="blogedit" {{if type == 'blogedit'}}selected="true"{{/if}}>_<?php echo __('Blog editor', 'runway'); ?></option>
                    {{/if}}

                    {{each(key, value) builder.availableTypes}}
                        <option value="${value.alias}" {{if type == value.alias}}selected="true"{{/if}}>${value.name}</option>
                    {{/each}}

                </select>

                <span class="settings-field-caption"></span>

            </div>
            <div class="clear"></div>

        </div>

        <div class="settings-container">
            <label class="settings-title">
                <?php echo __('Field caption', 'runway'); ?>:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="fieldCaption" name="fieldCaption" value="${fieldCaption}" class="settings-input" type="text">

                <span class="settings-field-caption"></span>

            </div>
            <div class="clear"></div>

        </div>

        <span class="field-settings-form-middle-section"></span>
    </form>
</script>
