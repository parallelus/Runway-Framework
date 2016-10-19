<!-- tab basis -->
<script id="tab" type="text/x-jquery-tmpl">

    <div class="meta-box-sortables metabox-holder page-element page-tab" data-template="${template}" data-index="${index}">
        <div class="postbox tabbox">
            <div class="handlediv" title="<?php _e( 'Click to toggle', 'runway' ); ?>"><br></div>
            <div class="page-element-controls" <?php if ( ! $this->resolutions['options-tabs'] ){ echo 'style="display:none;"'; } ?>>
                <a href="#" class="edit" data-type="tab"><?php _e( 'Edit', 'runway' ); ?></a> |
                <span class="delete"><a href="#" class="remove submitdelete"><?php _e( 'Delete', 'runway' ); ?></a></span>
            </div>
            <div class="postbox-title hndle"><span>${title}</span></div>
            <div class="inside accept-container" style="display: block; min-height: 50px;"></div>
        </div>
    </div>
    <div class="clear"></div>

</script>
<!-- tab basis -->

<!-- container basis -->
<script id="container" type="text/x-jquery-tmpl">

    <div class="meta-box-sortables metabox-holder page-element page-container" data-template="${template}" data-index="${index}">
        <div class="postbox containerbox">
            <div class="handlediv" title="<?php _e( 'Click to toggle', 'runway' ); ?>"><br></div>
            <div class="page-element-controls" <?php if ( ! $this->resolutions['options-containers'] ){ echo 'style="display:none;"'; } ?>>
                <a href="#" class="edit" data-type="container"><?php _e( 'Edit', 'runway' ); ?></a> |
                <a href="#" class="duplicate" data-type="container"><?php _e( 'Duplicate', 'runway' ); ?></a> |
                <span class="delete"><a href="#" class="remove submitdelete"><?php _e( 'Delete', 'runway' ); ?></a></span>
            </div>
            <div class="postbox-title hndle"><span>${title}</span></div>
            <div class="inside accept-field" style="display: block; min-height: 50px;"></div>
        </div>
    </div>
    <div class="clear"></div>

</script>
<!-- container basis -->

<!-- field basis -->
<script id="field" type="text/x-jquery-tmpl">

    <div class="meta-box-sortables metabox-holder page-element page-field" data-template="${template}" data-index="${index}">
        <div class="postbox fieldbox">
            <div class="handlediv" title="<?php _e( 'Click to toggle', 'runway' ); ?>"><br></div>
            <div class="page-element-controls" <?php if ( ! $this->resolutions['options-fields'] ){ echo 'style="display:none;"'; } ?>>
                <a href="#" class="edit" data-type="field"><?php _e( 'Edit', 'runway' ); ?></a> |
                <a href="#" class="duplicate" data-type="field"><?php _e( 'Duplicate', 'runway' ); ?></a> |
                <span class="delete"><a href="#" class="remove submitdelete"><?php _e( 'Delete', 'runway' ); ?></a></span>
            </div>
            <div class="postbox-title sidebar-name hndle" style="width:70%;"><span>${title}</span> (${type})</div>
            <div class="inside" style="display: block;"></div>
        </div>
    </div>
    <div class="clear"></div>

</script>
<!-- field basis -->
