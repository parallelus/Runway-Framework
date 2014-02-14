<?php if ( __FILE__ == $_SERVER['SCRIPT_FILENAME'] ) { die(); }


// Execute hooks before framework loads
do_action( 'functions_before' );


#-----------------------------------------------------------------
# Load framework
#-----------------------------------------------------------------
include_once get_template_directory() . '/framework/load.php';



// Execute hooks after framework loads
do_action( 'functions_after' ); ?>