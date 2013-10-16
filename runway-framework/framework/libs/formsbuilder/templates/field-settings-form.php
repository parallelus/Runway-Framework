<script id="field-settings" type="text/x-jquery-tmpl">
    <form class="settings-head">

        <input data-set="index" name="index" type="hidden" value="${index}">

        <div class="settings-container">
            <label class="settings-title">
                Title:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="title" name="title" value="${title}" class="settings-input" type="text">

                <br><span class="settings-field-caption"></span>

            </div>

        </div><div class="clear"></div>

        <div class="settings-container">
            <label class="settings-title">
                Title caption:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="titleCaption" name="titleCaption" value="${titleCaption}" class="settings-input" type="text">

                <br><span class="settings-field-caption"></span>

            </div>

        </div><div class="clear"></div>

        <div class="settings-container">
            <label class="settings-title">
                Alias:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in">

                <input data-set="alias" name="alias" value="${alias}" class="settings-input" type="text">

                <br><span class="settings-field-caption"></span>

            </div>

        </div><div class="clear"></div>

        <div class="settings-container">
            <label class="settings-title">
                Field type:
                <br><span class="settings-title-caption"></span>
            </label>
            <div class="settings-in" >

                <select data-set="type" name="type" class="settings-select">
                    {{if false}}
                        <option value="hidden" {{if type == 'hidden'}}selected="true"{{/if}}>_Hidden</option>
                        <option value="input" {{if type == 'input'}}selected="true"{{/if}}>_Text input</option>
                        <option value="textarea" {{if type == 'textarea'}}selected="true"{{/if}}>_Textarea</option>
                        <option value="checkbox" {{if type == 'checkbox'}}selected="true"{{/if}}>_Checkbox</option>
                        <option value="checkbox_boolean" {{if type == 'checkbox_boolean'}}selected="true"{{/if}}>_Checkbox boolean</option>
                        <option value="radio" {{if type == 'radio'}}selected="true"{{/if}}>_Radio buttons</option>
                        <option value="radioimage" {{if type == 'radioimage'}}selected="true"{{/if}}>_Image radio buttons</option>
                        <option value="select" {{if type == 'select'}}selected="true"{{/if}}>_Select</option>
                        <option value="multiselect" {{if type == 'multiselect'}}selected="true"{{/if}}>_Multiselect</option>
                        <option value="file" {{if type == 'file'}}selected="true"{{/if}}>_File upload</option>
                        <option value="colorpicker" {{if type == 'colorpicker'}}selected="true"{{/if}}>_Colorpicker</option>
                        <option value="datepicker" {{if type == 'datepicker'}}selected="true"{{/if}}>_Datepicker</option>
                        <option value="blogedit" {{if type == 'blogedit'}}selected="true"{{/if}}>_Blog editor</option>
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
                Field caption:
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
