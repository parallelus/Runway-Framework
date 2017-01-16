<?php

$ext_err_name = (isset($this->extensions_addons->$item->Name) && !empty($this->extensions_addons->$item->Name))? $this->extensions_addons->$item->Name : __('Unknown', 'runway');
$error_msg = (isset($body['error_message']) && !empty($body['error_message']))? sprintf( __('There was an error while attempting to install the %s  extension: %s', 'runway'), $ext_err_name, $body['error_message'] ) : sprintf( __('There was an error while attempting to install the %s extension', 'runway'), $ext_err_name );
echo '<div id="message" class="error"><p>' . $error_msg . '</p></div>';
