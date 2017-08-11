<?php
	
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
 
$gravitation_slider_cat = 'gravitation_slider_cat';
$slider_home_active = 'slider_home_active';


 
delete_option( $gravitation_slider_cat );
delete_option( $slider_home_active );


?>