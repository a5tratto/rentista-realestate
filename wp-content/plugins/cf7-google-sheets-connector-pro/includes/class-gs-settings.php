<?php
/**
 * Settings class for Google Sheet settings
 * @since 1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
   exit;
}

/**
 * GS_License_Settings Class
 * @since 1.1
 */

class GS_Settings {
   /**
    * @var string group name
    */
   protected $gs_group_name = 'gs-settings';
   
   /**
    * @var string roles that can access Google Sheet page
    */
   protected $gs_page_roles_setting_option_name = 'gs_page_roles_setting';
   
   /**
    * @var string roles that can access Google Sheet tab at contact form settings
    */
   protected $gs_tab_roles_setting_option_name = 'gs_tab_roles_setting';
   
   /**
    * Set things up.
    * @since 1.2
    */
   public function __construct() {
      add_action( 'admin_init', array( $this, 'init_settings' ) );
   }
   
   // White list our options using the Settings API
	public function init_settings() {
		register_setting( 'gs-settings', $this->gs_page_roles_setting_option_name, array( $this, 'validate_gs_access_roles') );
      register_setting( 'gs-settings', $this->gs_tab_roles_setting_option_name, array( $this, 'validate_gs_access_roles') );
	}
   
   /**
    * do validate and sanitize selected participants
    * @param array $selected_roles
    * @return array $roles
    * @since 1.1
    */
   public function validate_gs_access_roles( $selected_roles ) {
      $roles = array();
      $system_roles = Gs_Connector_Utility::instance()->get_system_roles();
      
      if ( count( $selected_roles ) > 0 ) {         
         foreach ( $system_roles as $role => $display_name ) {
            if ( is_array( $selected_roles ) && in_array( esc_attr( $role ), $selected_roles ) ) { // preselect specified role
              $roles[$role] = $display_name;
            } 
         }
      }
      return $roles;
   }
   
   /*
	 * generate the page
	 *
	 * @since 2.0
	 */
	public function add_settings_page() { 
      $page_roles = get_option( $this->gs_page_roles_setting_option_name );
      $tab_roles = get_option( $this->gs_tab_roles_setting_option_name );
   ?>
      <form id="gs_settings_form" method="post" action="options.php">
         <?php
         settings_fields( $this->gs_group_name ); // adds nonce for current settings page
         ?>
         <div class="wrap gs-form">
            <div class="gs-card">
               <div><label><?php echo __( 'Roles that can access Google Sheet Page', 'gsconnector' ); ?></label></div>
               <?php Gs_Connector_Utility::instance()->gs_checkbox_roles_multi(
                      		$this->gs_page_roles_setting_option_name . '[]',
                      		$page_roles ); ?>

               <br/>
               <div><label><?php echo __('Roles that can access Google Sheet Tab at Contact Form', 'gsconnector' ); ?></label></div>
               <?php Gs_Connector_Utility::instance()->gs_checkbox_roles_multi(
                      		$this->gs_tab_roles_setting_option_name . '[]',
                      		$tab_roles ); ?>
               <br/>
               <div class="select-info">
						<input type="submit" class="button button-primary button-large" name="gs_settings" value="<?php echo __( "Save", "gsconnector" ); ?>"/>
					</div>
            </div>
         </div>
      </form>
   <?php
   }
   
}
$gs_settings = new GS_Settings();