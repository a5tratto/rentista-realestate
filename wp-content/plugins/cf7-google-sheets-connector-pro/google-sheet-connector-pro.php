<?php
/*
  Plugin Name: CF7 Google Sheet Connector Pro
  Plugin URI: https://www.gsheetconnector.com/
  Description: Send your Contact Form 7 data to your Google Sheets spreadsheet.
  Version: 1.4
  Author: WesternDeal
  Author URI: https://www.gsheetconnector.com/
  Text Domain: gsconnector
 */

if ( ! defined('ABSPATH') ) {
   exit; // Exit if accessed directly
}

// Declare some global constants
define('GS_CONNECTOR_PRO_VERSION', '1.4');
define('GS_CONNECTOR_PRO_DB_VERSION', '1.4');
define('GS_CONNECTOR_PRO_ROOT', dirname(__FILE__));
define('GS_CONNECTOR_PRO_URL', plugins_url('/', __FILE__));
define('GS_CONNECTOR_PRO_BASE_FILE', basename(dirname(__FILE__)) . '/google-sheet-connector-pro.php');
define('GS_CONNECTOR_PRO_BASE_NAME', plugin_basename(__FILE__));
define('GS_CONNECTOR_PRO_PATH', plugin_dir_path(__FILE__)); //use for include files to other files
define('GS_CONNECTOR_PRO_PRODUCT_NAME', 'CF7 Google Sheet Connector PRO');
define('GS_CONNECTOR_PRO_CURRENT_THEME', get_stylesheet_directory());
define('GS_CONNECTOR_PRO_STORE_URL', 'https://gsheetconnector.com' );
load_plugin_textdomain('gsconnector', false, basename(dirname(__FILE__)) . '/languages');

/*
 * include utility classes
 */
if ( ! class_exists('Gs_Connector_Utility') ) {
   include( GS_CONNECTOR_PRO_ROOT . '/includes/class-gs-utility.php' );
}
if ( ! class_exists('Gs_Connector_Service') ) {
   include( GS_CONNECTOR_PRO_ROOT . '/includes/class-gs-service.php' );
}
require_once GS_CONNECTOR_PRO_ROOT . '/lib/CF7GSC/Client.php';

/*
 * Main GS connector class
 * @class Gs_Connector_Init
 * @since 1.0
 */

class Gs_Connector_Init {

   /**
    *  Set things up.
    *  @since 1.0
    */
   public function __construct() {
      //run on activation of plugin
      register_activation_hook(__FILE__, array($this, 'gs_connector_activate'));

      //run on deactivation of plugin
      register_deactivation_hook(__FILE__, array($this, 'gs_connector_deactivate'));

      //run on uninstall
      register_uninstall_hook(__FILE__, array('Gs_Connector_Init', 'gs_connector_uninstall'));

      // validate is contact form 7 plugin exist
      add_action('admin_init', array($this, 'validate_parent_plugin_exists'));

      // register admin menu under "Contact" > "Integration"
      add_action('admin_menu', array($this, 'register_gs_menu_pages'));

      // load the js and css files
      add_action('init', array($this, 'load_css_and_js_files'));
      
      // load the classes
		add_action( 'init', array( $this, 'load_all_classes' ) );

      // Add custom link for our plugin
      add_filter('plugin_action_links_' . GS_CONNECTOR_PRO_BASE_NAME, array($this, 'gs_connector_plugin_action_links'));
      
      // Display widget to dashboard
      add_action( 'wp_dashboard_setup', array( $this, 'add_gs_connector_summary_widget' ) );
   }

   /**
    * Do things on plugin activation
    * @since 1.0
    */
   public function gs_connector_activate() {
      global $wpdb;
      $this->run_on_activation();
      if ( function_exists('is_multisite') && is_multisite() ) {
         // check if it is a network activation - if so, run the activation function for each blog id
         if ( $network_wide ) {
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");
            foreach ( $blogids as $blog_id ) {
               switch_to_blog($blog_id);
               $this->run_for_site();
               restore_current_blog();
            }
            return;
         }
      }

      // for non-network sites only
      $this->run_for_site();
   }

   /**
    * deactivate the plugin
    * @since 1.0
    */
   public function gs_connector_deactivate() {
      
   }

   /**
    *  Runs on plugin uninstall.
    *  a static class method or function can be used in an uninstall hook
    *
    *  @since 1.5
    */
   public static function gs_connector_uninstall() {
      global $wpdb;
      Gs_Connector_Init::run_on_uninstall();
      if ( function_exists('is_multisite') && is_multisite() ) {
         //Get all blog ids; foreach of them call the uninstall procedure
         $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");

         //Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
         foreach ( $blog_ids as $blog_id ) {
            switch_to_blog($blog_id);
            Gs_Connector_Init::delete_for_site();
            restore_current_blog();
         }
         return;
      }
      Gs_Connector_Init::delete_for_site();
   }

   /**
    * Validate parent Plugin Contact Form 7 exist and activated
    * @access public
    * @since 1.0
    */
   public function validate_parent_plugin_exists() {
      $plugin = plugin_basename(__FILE__);
      if ( ( ! is_plugin_active('contact-form-7/wp-contact-form-7.php') ) && ( ! is_plugin_active('google-sheet-connector/google-sheet-connector') ) ) {
         add_action('admin_notices', array($this, 'contact_form_7_missing_notice'));
         add_action( 'network_admin_notices',  array( $this, 'contact_form_7_missing_notice') );
         deactivate_plugins($plugin);
         if ( isset($_GET['activate']) ) {
            // Do not sanitize it because we are destroying the variables from URL
            unset($_GET['activate']);
         }
      }      
   }

   /**
    * If Contact Form 7 plugin is not installed or activated then throw the error
    *
    * @access public
    * @return mixed error_message, an array containing the error message
    *
    * @since 1.0 initial version
    */
   public function contact_form_7_missing_notice() {
      $plugin_error = Gs_Connector_Utility::instance()->admin_notice(array(
          'type' => 'error',
          'message' => 'Google Sheet Connector Add-on requires Contact Form 7 plugin to be installed and activated OR Lite version of Google Sheet Connector is active.'
              ));
      echo $plugin_error;
   }

   /**
    * Create/Register menu items for the plugin.
    * @since 1.0
    */
   public function register_gs_menu_pages() {
      $current_role = Gs_Connector_Utility::instance()->get_current_user_role();
      $gs_roles = get_option( 'gs_page_roles_setting' );      
      if( ( is_array( $gs_roles ) && array_key_exists( $current_role, $gs_roles ) ) || $current_role === "administrator" ) {
         add_submenu_page('wpcf7', __('Google Sheet', 'gsconnector'), __('Google Sheet', 'gsconnector'), $current_role, 'wpcf7-google-sheet-config', array($this, 'google_sheet_config'));
      }
   }

   /**
    * Google Sheets page action.
    * This method is called when the menu item "Google Sheets" is clicked.
    * @since 1.0
    */
   public function google_sheet_config() {
      include( GS_CONNECTOR_PRO_PATH . "includes/pages/google-sheet-settings.php" );
   }

   public function load_css_and_js_files() {
      add_action('admin_print_styles', array($this, 'add_css_files'));
      add_action('admin_print_scripts', array($this, 'add_js_files'));
   }
   
   /**
	 * Load all the classes - as part of init action hook
	 * @since 1.0
	 */
	public function load_all_classes() {
      if ( ! class_exists( 'GS_License_Settings' ) ) {
			include( GS_CONNECTOR_PRO_PATH . 'includes/class-gs-license-settings.php' );
		}
      if ( ! class_exists( 'GS_License_Service' ) ) {
			include( GS_CONNECTOR_PRO_PATH . 'includes/class-gs-license-service.php' );
		}
      if ( ! class_exists( 'GS_Settings' ) ) {
			include( GS_CONNECTOR_PRO_PATH . 'includes/class-gs-settings.php' );
		}
   }

   /**
    * enqueue CSS files
    * @since 1.0
    */
   public function add_css_files() {
      if ( is_admin() && ( isset($_GET['page']) && ( ( $_GET['page'] == 'wpcf7-new' ) || ( $_GET['page'] == 'wpcf7-google-sheet-config' ) || ( $_GET['page'] == 'wpcf7' ) ) ) ) {
        wp_enqueue_style('gs-connector-css', GS_CONNECTOR_PRO_URL . 'assets/css/gs-connector.css', GS_CONNECTOR_PRO_VERSION, true);
		wp_enqueue_style( 'gs-connector-faq-css', GS_CONNECTOR_PRO_URL . 'assets/css/faq-style.css', GS_CONNECTOR_PRO_VERSION, true );
      }
   }

   /**
    * enqueue JS files
    * @since 1.0
    */
   public function add_js_files() {
      if ( is_admin() && ( isset( $_REQUEST['page'] ) && preg_match_all( '/page=wpcf7-new(.*)|page=wpcf7-google-sheet-config(.*)|page=wpcf7(.*)/', $_SERVER['REQUEST_URI'], $matches ) ) ) {
         wp_enqueue_script('gs-connector-js', GS_CONNECTOR_PRO_URL . 'assets/js/gs-connector.js', GS_CONNECTOR_PRO_VERSION, true);
         wp_enqueue_script('jquery-json', GS_CONNECTOR_PRO_URL . 'assets/js/jquery.json.js', '', '2.3', true);
      }
   }

   /**
    * called on upgrade. 
    * checks the current version and applies the necessary upgrades from that version onwards
    * @since 1.0
    */
   public function run_on_upgrade() {
      $plugin_options = get_site_option('google_sheet_info');
      
      if ( $plugin_options['version'] == "1.0" ) {
         $this->upgrade_database_11();
         $this->upgrade_database_12();
      } elseif ( $plugin_options['version'] == "1.1" ) {
         $this->upgrade_database_12();
      }

      // update the version value
      $google_sheet_info = array(
          'version' => GS_CONNECTOR_PRO_VERSION,
          'db_version' => GS_CONNECTOR_PRO_DB_VERSION
      );
      update_site_option('google_sheet_info', $google_sheet_info);
   }
   
   /**
	 * Upgrade helper for v1.1 upgrade function
	 * @since 1.1
	 */
	private function upgrade_database_11() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists('is_multisite') && is_multisite() )
		{
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id )
			{
				switch_to_blog( $blog_id );
            $this->upgrade_helper_11();
				restore_current_blog();
			}
		}
		$this->upgrade_helper_11();
	} 
   
   private function upgrade_helper_11() { 
      if ( ! get_option( 'gs_page_roles_setting' ) ) {
			update_option( "gs_page_roles_setting", array() );
		}
      if ( ! get_option( 'gs_tab_roles_setting' ) ) {
			update_option( "gs_tab_roles_setting", array() );
		}
   }
   
   /**
	 * Upgrade helper for v1.2 upgrade function
	 * @since 1.2
	 */
	private function upgrade_database_12() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists('is_multisite') && is_multisite() )
		{
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id )
			{
				switch_to_blog( $blog_id );
            $this->upgrade_helper_12();
				restore_current_blog();
			}
		}
		$this->upgrade_helper_12();
	} 
   
   private function upgrade_helper_12() { 
      if ( ! get_option( 'gs_page_roles_setting' ) ) {
			update_option( "gs_page_roles_setting", array() );
		}
      if ( ! get_option( 'gs_tab_roles_setting' ) ) {
			update_option( "gs_tab_roles_setting", array() );
		}
   }

   /**
    * Add custom link for the plugin beside activate/deactivate links
    * @param array $links Array of links to display below our plugin listing.
    * @return array Amended array of links.    * 
    * @since 1.5
    */
   public function gs_connector_plugin_action_links( $links ) {
      // We shouldn't encourage editing our plugin directly.
      unset($links['edit']);

      // Add our custom links to the returned array value.
      return array_merge(array(
          '<a href="' . admin_url('admin.php?page=wpcf7-google-sheet-config') . '">' . __('Settings', 'gsconnector') . '</a>'
              ), $links);
   }
   
   /**
    * Add widget to the dashboard
    * @since 1.0
    */
   public function add_gs_connector_summary_widget() {
      wp_add_dashboard_widget( 'gs_dashboard', __( 'Google Sheet Connector','gsconnector' ), array( $this, 'gs_connector_summary_dashboard' ) );
   }

   /**
    * Display widget conetents
    * @since 1.0
    */
	public function gs_connector_summary_dashboard() {
      include_once( GS_CONNECTOR_PRO_ROOT . '/includes/pages/gs-dashboard-widget.php' );
   }

   /**
    * Called on activation.
    * Creates the site_options (required for all the sites in a multi-site setup)
    * If the current version doesn't match the new version, runs the upgrade
    * @since 1.0
    */
   private function run_on_activation() {
      $plugin_options = get_site_option('google_sheet_info');
      if ( false === $plugin_options ) {
         $google_sheet_info = array(
             'version' => GS_CONNECTOR_PRO_VERSION,
             'db_version' => GS_CONNECTOR_PRO_DB_VERSION
         );
         update_site_option('google_sheet_info', $google_sheet_info);
      } else if ( GS_CONNECTOR_PRO_DB_VERSION != $plugin_options['version'] ) {
         $this->run_on_upgrade();
      }
   }

   /**
    * Called on activation.
    * Creates the options and DB (required by per site)
    * @since 1.0
    */
   private function run_for_site() {
      if ( ! get_option('gs_access_code') ) {
         update_option('gs_access_code', '');
      }
      if ( ! get_option('gs_verify') ) {
         update_option('gs_verify', 'invalid');
      }
      if ( ! get_option('gs_token') ) {
         update_option('gs_token', '');
      }
      if ( ! get_option('gs_feeds') ) {
         update_option('gs_feeds', '');
      }
      if ( ! get_option('gs_sheetId') ) {
         update_option('gs_sheetId', '');
      }
      if ( ! get_option( 'gs_page_roles_setting' ) ) {
			update_option( "gs_page_roles_setting", array() );
		}
      if ( ! get_option( 'gs_tab_roles_setting' ) ) {
			update_option( "gs_tab_roles_setting", array() );
		}
      
      // Create directory
      $upload = wp_upload_dir();
      $upload_dir = $upload['basedir'];
      $upload_dir = $upload_dir . '/cf7gs';
      if ( ! is_dir( $upload_dir ) ) {
         wp_mkdir_p( $upload_dir );
      }

   }

   /**
    * Called on uninstall - deletes site_options
    *
    * @since 1.5
    */
   private static function run_on_uninstall() {
      if ( ! defined('ABSPATH') && ! defined('WP_UNINSTALL_PLUGIN') ) exit();

      delete_site_option('google_sheet_info');
   }

   /**
    * Called on uninstall - deletes site specific options
    *
    * @since 1.5
    */
   private static function delete_for_site() {
      
      // deactivate the license
		$license = trim( get_option( 'gs_license_key' ) );

		// data to send in our API request
		$api_params = array(
				'edd_action' => 'deactivate_license',
				'license' => $license,
				'item_name' => urlencode( GS_CONNECTOR_PRO_PRODUCT_NAME ), // the name of our product in EDD
				'url' => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( GS_CONNECTOR_PRO_STORE_URL, array( 'timeout' => 15, 'body' => $api_params ) );

		delete_option( 'gs_license_status' );
		delete_option( 'gs_license_key' );
      
      delete_option('gs_access_code');
      delete_option('gs_verify');
      delete_option('gs_token');
      delete_option('gs_feeds');
      delete_option('gs_sheetId');
      delete_option('gs_page_roles_setting');
      delete_option('gs_tab_roles_setting');
      delete_post_meta_by_key( 'gs_settings' );
      delete_post_meta_by_key( 'gs_map_mail_tags' );
      delete_post_meta_by_key( 'gs_map_special_mail_tags' );
      delete_post_meta_by_key( 'gs_map_custom_mail_tags' );
   }
   
   /**
	 * Plugin Update notifier
	 */
	public function gs_plugin_updater() {
		if ( ! class_exists( 'GS_Plugin_Updater' ) ) {
			include( GS_CONNECTOR_PRO_PATH . "includes/class-gs-plugin-updater.php" );

			// setup the plugin updater
			$edd_updater = new GS_Plugin_Updater( GS_CONNECTOR_PRO_STORE_URL, __FILE__, array(
					'version' => GS_CONNECTOR_PRO_VERSION, // current version number
					'license' =>  trim( get_option( 'gs_license_key' ) ), // license key (used get_option above to retrieve from DB)
					'item_name' => GS_CONNECTOR_PRO_PRODUCT_NAME, // name of this plugin
					'author' => 'WesternDeal' // author of this plugin
				)
			);
		}
	}

}

// Initialize the google sheet connector class
$init = new Gs_Connector_Init();
add_action( 'admin_init', array( $init, 'gs_plugin_updater' ) );
