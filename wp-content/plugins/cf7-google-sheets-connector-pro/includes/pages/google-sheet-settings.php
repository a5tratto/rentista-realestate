<?php
/*
 * Google Sheet configuration and settings page
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
   exit();
}

$active_tab = ( isset ( $_GET['tab'] ) && sanitize_text_field( $_GET["tab"] )) ?  sanitize_text_field( $_GET['tab'] ) : 'integration';

// if the license info is incomplete or license status is invalid, go to the license tab
$license = get_option( 'gs_license_key' );
$status 	= get_option( 'gs_license_status' );

if ( empty( $license ) || $status == 'invalid' ) {
   $active_tab = 'license_settings';
}
?>

<div class="wrap">
	<?php
       $tabs = array( 
          'license_settings' => __( 'License', 'gsconnector' ), 
          'integration' => __( 'Integration', 'gsconnector' ),
          'settings' => __( 'Settings', 'gsconnector' ),     
          );
       echo '<div id="icon-themes" class="icon32"><br></div>';
       echo '<h2 class="nav-tab-wrapper">';
       foreach( $tabs as $tab => $name ){
           $class = ( $tab == $active_tab ) ? ' nav-tab-active' : '';
           echo "<a class='nav-tab$class' href='?page=wpcf7-google-sheet-config&tab=$tab'>$name</a>";

       }
       echo '</h2>';
   	switch ( $active_tab ){
         case 'license_settings' :
   		   $gs_license_settings = new GS_License_Settings();
			   $gs_license_settings->add_settings_page();
   		   break;
   		case 'integration' :
   		   include( GS_CONNECTOR_PRO_PATH . "includes/pages/gs-integration.php" ) ;
   		   break;
         case 'settings' :
   		   $gs_settings = new GS_Settings();
			   $gs_settings->add_settings_page();
   		   break;
   	}
	?>
</div>

