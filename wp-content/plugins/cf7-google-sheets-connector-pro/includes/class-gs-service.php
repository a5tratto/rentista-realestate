<?php
/**
 * Service class for Google Sheet Connector
 * @since 1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

/**
 * Gs_Connector_Service Class
 *
 * @since 1.0
 */
class Gs_Connector_Service {
   
   /**
     * Custom tags by plugin
     * @var array
     */
   private $allowed_tags = ['text', 'email', 'url', 'tel', 'number', 'range', 'date', 'textarea', 'select', 'checkbox', 'radio', 'acceptance', 'quiz', 'file', 'hidden' ];

   private $special_mail_tags = array( 'date', 'time', 'serial_number', 'remote_ip', 'user_agent', 'url', 'post_id', 'post_name', 'post_title', 'post_url', 'post_author', 'post_author_email', 'site_title', 'site_description', 'site_url', 'site_admin_email', 'user_login', 'user_email', 'user_display_name' ); 
   /**
    *  Set things up.
    *  @since 1.0
    */
   public function __construct() {
      add_action( 'wp_ajax_verify_gs_integation', array( $this, 'verify_gs_integation' ) );
      add_action( 'wp_ajax_gs_clear_log', array( $this, 'gs_clear_logs' ) );
      add_action( 'wp_ajax_sync_google_account', array( $this, 'sync_google_account' ) );
      add_action( 'wp_ajax_get_tab_list', array( $this, 'get_tab_list_by_sheetname' ) );
      add_action( 'wp_ajax_get_sheet_id', array( $this, 'get_sheet_id_by_sheetname' ) );

      // Add new tab to contact form 7 editors panel
      add_filter( 'wpcf7_editor_panels', array( $this, 'cf7_gs_editor_panels' ) );

      add_action( 'wpcf7_after_save', array( $this, 'save_gs_settings' ) );
      add_action( 'wpcf7_mail_sent', array( $this, 'cf7_save_to_google_sheets' ) );
	  
      add_action( 'wpcf7_after_create', array( $this, 'duplicate_forms_support' ) );
   }
	 
   /**
    * AJAX function - verifies the token
    * @since 1.0
    */
   public function verify_gs_integation() {
      // nonce check
      check_ajax_referer( 'gs-ajax-nonce', 'security' );

      /* sanitize incoming data */
      $Code = sanitize_text_field( $_POST["code"] );

      update_option( 'gs_access_code', $Code );

      if ( get_option( 'gs_access_code' ) != '' ) {
         include_once( GS_CONNECTOR_PRO_ROOT . '/lib/google-sheets.php');
         CF7GSC_googlesheet::preauth( get_option( 'gs_access_code' ) );
         update_option( 'gs_verify', 'valid' );   
         // After validation fetch sheetname and tabs from the user account
         //$this->sync_google_account();  
         wp_send_json_success();
      } else {
         update_option( 'gs_verify', 'invalid' );
         wp_send_json_error();
      }
   }
   
   /**
    * AJAX function - clear log file
    * @since 1.0
    */
   public function gs_clear_logs() {
      // nonce check
      check_ajax_referer( 'gs-ajax-nonce', 'security' );
      
      $handle = fopen ( GS_CONNECTOR_PRO_PATH . 'logs/log.txt', 'w');
      fclose( $handle );
      
      wp_send_json_success();
   }
   
   /**
    * Function - sync with google account to fetch sheet and tab name
    * @since 1.0
    */
   public function sync_google_account() {
      $return_ajax = false;
      
      if( isset( $_POST['isajax'] ) && $_POST['isajax'] == 'yes' ) {
         // nonce check
         check_ajax_referer( 'gs-ajax-nonce', 'security' );
         $init = sanitize_text_field( $_POST['isinit'] );
         $return_ajax = true;
      }
      
      include_once( GS_CONNECTOR_PRO_ROOT . '/lib/google-sheets.php');
      $sheetdata = array();
      $sheetId = array();
      $doc = new CF7GSC_googlesheet();
      $doc->auth();
      $spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
      // Get all spreadsheets
      $spreadsheetFeed = $spreadsheetService->getSpreadsheets();
      foreach ( $spreadsheetFeed as $sheetfeeds ) {
         // get sheet title
         $sheetname =  $sheetfeeds->getTitle();
         
         // Get sheet id
         $link = $sheetfeeds->getId();
         $getid = substr($link, 64);
         $sheetId[ $sheetname ] = $getid;        
         
         $tablist = $spreadsheetFeed->getByTitle( $sheetname );
         $worksheets = $tablist->getWorksheets();
         foreach( $worksheets as $worksheetfeeds ) {
            $worksheetname = $worksheetfeeds->getTitle();
            $worksheet_array[] = $worksheetname;
         }
         $sheetdata[ $sheetname ] = $worksheet_array;
         unset( $worksheet_array );
      }
      
      update_option( 'gs_feeds', $sheetdata );
      update_option( 'gs_sheetId', $sheetId );

      if ( $return_ajax == true ) {
         if( $init == 'yes' ) {
            wp_send_json_success( array( "success" => 'yes' ) );
         } else {
            wp_send_json_success( array( "success" => 'no' ) );
         }
      }
   }
   
   /**
    * AJAX function - Fetch tab list by sheet name
    * @since 1.0
    */
   public function get_tab_list_by_sheetname() {
      // nonce check
      check_ajax_referer( 'gs-ajax-nonce', 'security' );
      
      $sheetname = sanitize_text_field( $_POST['sheetname'] );
      $sheet_data = get_option('gs_feeds');
      $html = "";
      $tablist = "";
      if( ! empty( $sheet_data ) && array_key_exists( $sheetname, $sheet_data ) ) {
         $tablist = $sheet_data[ $sheetname ];
      }
      
      if ( ! empty( $tablist ) ) {
         $html = '<option value="">'. __("Select","gsconnector") . '</option>';
         foreach( $tablist as $tab ) {
            $html .= '<option value="'. $tab .'">' . $tab . '</option>';
         }
      }
      wp_send_json_success( htmlentities( $html ) );
   }
   
   /**
    * AJAX function - Fetch sheet URL
    * @since 1.3
    */
   public function get_sheet_id_by_sheetname() {
      // nonce check
      check_ajax_referer( 'gs-ajax-nonce', 'security' );

      $sheetname = sanitize_text_field( $_POST['sheetname'] );
      $sheetId_data = get_option( 'gs_sheetId' );
      $html = "";
      
      if( $sheetId_data ) {
         foreach ( $sheetId_data as $key => $tab ) {
            if ( $key == $sheetname ) {
               $html .= '<label> Google Sheet URL </label> <a href="https://docs.google.com/spreadsheets/d/' . $tab . '/edit#gid=0" target="_blank">Sheet URL</a>
                <input type="hidden" name="gsheet_id" value="' . $tab . '">';
            }
         }
         wp_send_json_success( htmlentities( $html ) );
      }
   }

   /**
    * Add new tab to contact form 7 editors panel
    * @since 1.0
    */
   public function cf7_gs_editor_panels( $panels ) {
      $current_role = Gs_Connector_Utility::instance()->get_current_user_role();
      $gs_roles = get_option( 'gs_tab_roles_setting' );      
         if( array_key_exists( $current_role, $gs_roles ) || $current_role === "administrator" ) {
         $panels['google_sheets'] = array(
            'title' => __( 'Google Sheet Pro', 'contact-form-7' ),
            'callback' => array( $this, 'cf7_editor_panel_google_sheet' )
         );
      }

      return $panels;
   }
   
   /**
	 * Copy key values and assign it to duplicate form
	 *
	 * @param object $contact_form WPCF7_ContactForm Object - All data that is related to the form.
	 */
	public function duplicate_forms_support( $contact_form ) {
		$contact_form_id = $contact_form->id();
			
		if ( ! empty( $_REQUEST['post'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
         
			$post_id = intval( $_REQUEST['post'] );
         
			$get_settings = get_post_meta( $post_id,'gs_settings' );
			foreach($get_settings as $gskey => $gsval){
				update_post_meta( $contact_form_id, 'gs_settings', $gsval );
			}
         
			$get_special_tags = get_post_meta( $post_id,'gs_map_special_mail_tags' );
			foreach( $get_special_tags as $gstkey => $gstval ){
				update_post_meta( $contact_form_id, 'gs_map_special_mail_tags', $gstval );
			}
         
			$get_custom_tags = get_post_meta( $post_id,'gs_map_custom_mail_tags' );
			foreach( $get_custom_tags as $gctkey => $gctval ){
				update_post_meta( $contact_form_id, 'gs_map_custom_mail_tags', $gctval);
			}
         
			$get_mail_tags = get_post_meta( $post_id,'gs_map_mail_tags' );
			foreach( $get_mail_tags as $gmtkey => $gmtval ){
				update_post_meta( $contact_form_id, 'gs_map_mail_tags', $gmtval );
			}
         
		}
	}
   
   /**
    * Set Google sheet settings with contact form
    * @since 1.0
    */
   public function save_gs_settings( $post ) {  
      
      $form_id = $post->id();
      $get_existing_data = get_post_meta( $form_id, 'gs_settings' );
     
      $sheet_name = isset( $_POST['cf7-gs']['sheet-name'] ) ? $_POST['cf7-gs']['sheet-name'] : "";
      $tab_name   = isset( $_POST['cf7-gs']['sheet-tab-name'] ) ? $_POST['cf7-gs']['sheet-tab-name'] : "";
     
      // If data exist and user want to disconnect
      if ( ! empty( $get_existing_data ) && $sheet_name == "" ) {
         update_post_meta( $form_id, 'gs_settings', "" );
      }
            
      $gs_map_tags = array();
       
      // Save special mail tags
      $special_mail_tag             = isset( $_POST['gs-st-ck'] ) ? $_POST['gs-st-ck'] : array();
      $special_mail_tag_key         = $_POST['gs-st-key'];
      $special_mail_tag_placeholder = $_POST['gs-st-placeholder'];
      $special_mail_tag_column      = $_POST['gs-st-custom-header'];
      $special_mail_tag_array       = array();
      if( ! empty( $special_mail_tag ) ) {
         foreach( $special_mail_tag as $key=>$value) {
            $smt_key = $special_mail_tag_key[$key];
            $smt_val = ( ! empty ( $special_mail_tag_column[$key] ) ) ? $special_mail_tag_column[$key] : $special_mail_tag_placeholder[$key] ;
            if( $smt_val !== "" ) {
               $special_mail_tag_array[$smt_key] = $smt_val;
               $gs_map_tags[] = $smt_val;
            }
         }
      }
      update_post_meta( $form_id, 'gs_map_special_mail_tags', $special_mail_tag_array );
      
      // Save custom mail tags
      $custom_mail_tag              = isset( $_POST['gs-ct-ck'] ) ? $_POST['gs-ct-ck'] : array();
      $custom_mail_tag_key          = isset( $_POST['gs-ct-key'] ) ? $_POST['gs-ct-key'] : array();
      $custom_mail_tag_placeholder  = isset( $_POST['gs-st-placeholder'] ) ? $_POST['gs-st-placeholder'] : array();
      $custom_mail_tag_column       = isset( $_POST['gs-ct-custom-header'] ) ? $_POST['gs-ct-custom-header'] : array();
      $custom_mail_tag_array  = array();
      if( ! empty( $custom_mail_tag ) ) {
         foreach( $custom_mail_tag as $key=>$value ) {
            $cmt_key = ltrim( $custom_mail_tag_key[$key], '_' );
            $cmt_val = ( ! empty ( $custom_mail_tag_column[$key] ) ) ? $custom_mail_tag_column[$key] : $custom_mail_tag_placeholder[$key] ;
            if( $cmt_val !== "" ) {
               $custom_mail_tag_array[$cmt_key] = $cmt_val;
               $gs_map_tags[] = $cmt_val;
            }
         }
      }
      update_post_meta( $form_id, 'gs_map_custom_mail_tags', $custom_mail_tag_array );
      
      // Save mail tags
      $mail_tag_chk           = isset( $_POST['gs-custom-ck'] ) ? $_POST['gs-custom-ck'] : array();      
      $mail_tag               = $_POST['gs-custom-header-key'];
      $mail_tag_placeholder   = $_POST['gs-custom-header-placeholder'];
      $mail_tag_column        = $_POST['gs-custom-header'];
      $mail_tag_array         = array();
      if( ! empty( $mail_tag_chk ) ) {
         foreach( $mail_tag_chk as $key=>$value ) {
            $mt_key = $mail_tag[$key];
            $mt_val = ( ! empty ( $mail_tag_column[$key] ) ) ? $mail_tag_column[$key] : $mail_tag_placeholder[$key];
            if( $mt_val !== "" ) {
               $mail_tag_array[$mt_key] = $mt_val;
               $gs_map_tags[] = $mt_val;
            }
         }
      }
      update_post_meta( $form_id, 'gs_map_mail_tags', $mail_tag_array );
      
      // if not empty sheet and tab name than save and add header to sheet
      if ( ! empty( $sheet_name ) && ( ! empty( $tab_name ) ) ) {
         update_post_meta( $post->id(), 'gs_settings', $_POST['cf7-gs'] );
         try {
            include_once( GS_CONNECTOR_PRO_ROOT . "/lib/google-sheets.php" );
            $doc = new CF7GSC_googlesheet();
            $doc->auth();
            $doc->add_header( $sheet_name, $tab_name, $gs_map_tags );
         }  catch ( Exception $e ) {
            $data['ERROR_MSG'] = $e->getMessage();
            $data['TRACE_STK'] = $e->getTraceAsString();
            Gs_Connector_Utility::gs_debug_log( $data );
         }  
      }         
   }

   /**
    * Function - To send contact form data to google spreadsheet
    * @param object $form
    * @since 1.0
    */
   public function cf7_save_to_google_sheets( $form ) {
      $submission = WPCF7_Submission::get_instance();
      // get form data
      $form_id = $form->id();
      $form_data = get_post_meta( $form_id, 'gs_settings' );
      
      $mail_tags = get_post_meta( $form_id , 'gs_map_mail_tags' );      
      $special_mail_tags = get_post_meta( $form_id , 'gs_map_special_mail_tags' );
      $custom_mail_tags = get_post_meta( $form_id , 'gs_map_custom_mail_tags' );
      $mereged_mail_tags = ( ! empty( $special_mail_tags ) ) && ( ! empty( $custom_mail_tags ) ) ? array_merge( $special_mail_tags, $custom_mail_tags ) : array();

      $data = array();
      $meta = array();

      // if contact form sheet name and tab name is not empty than send data to spreedsheet
      if ( $submission && ( ! empty( $form_data[0]['sheet-name'] ) ) && ( ! empty( $form_data[0]['sheet-tab-name'] ) ) ) {
         $posted_data = $submission->get_posted_data();
         
         // Store upload files locally
         $uploads_stored = $this->save_uploaded_files_local( $submission->uploaded_files() );
		 
         // make sure the form ID matches the setting otherwise don't do anything
         try {
            include_once( GS_CONNECTOR_PRO_ROOT . "/lib/google-sheets.php" );
            $doc = new CF7GSC_googlesheet();
            $doc->auth();
            $doc->settitleSpreadsheet( $form_data[0]['sheet-name'] );
            $doc->settitleWorksheet( $form_data[0]['sheet-tab-name'] );
            
            
            foreach ( $mereged_mail_tags as $k=>$v ) {
               foreach( $v as $k1=>$v1) {
                  $meta[$v1] = apply_filters( 'wpcf7_special_mail_tags', '', sprintf( '_%s', $k1 ), false );
               }
            }
            
            // Enter special mail tag values to sheet
            foreach( $meta as $k2=>$v2 ) {
               $data[ $k2 ] = $v2;
            }            
            
            foreach ( $posted_data as $key => $value ) {
               // exclude the default wpcf7 fields in object
               if ( strpos( $key, '_wpcf7' ) !== false || strpos( $key, '_wpnonce' ) !== false ) {
                  // do nothing
               } else {                  
                  // Get custom column name by key
                  if( array_key_exists( $key, $mail_tags[0] ) ) {
                     $key = $mail_tags[0][$key];
                  }
                  
                  // Get file uploaded URL
                  if( array_key_exists( $key, $uploads_stored ) ) {
                     $value = $uploads_stored[$key];
                  }
                  
                  // handle strings and array elements
                  if ( is_array( $value ) ) {
                     $data[$key] = implode( ', ', $value );
                  } else {
                     $data[$key] = $value;
                  }
               }
            }           
            $doc->add_row( $data );
         } catch ( Exception $e ) {
            $data['ERROR_MSG'] = $e->getMessage();
            $data['TRACE_STK'] = $e->getTraceAsString();
            Gs_Connector_Utility::gs_debug_log( $data );
         }
      }
   }

   /*
    * Google sheet settings page  
    * @since 1.0
    */

   public function cf7_editor_panel_google_sheet( $post ) {
      
	  //print_r($post);
	  
      $form_id = sanitize_text_field( $_GET['post'] );
      $form_data = get_post_meta( $form_id, 'gs_settings' );
      
      $saved_sheet_name = isset( $form_data[0]['sheet-name'] ) ? $form_data[0]['sheet-name'] : "" ;
      $saved_tab_name = isset( $form_data[0]['sheet-tab-name'] ) ? $form_data[0]['sheet-tab-name'] : "" ;
      
      $sheet_data = get_option('gs_feeds');
      ?>
      <form method="post">
         <div class="gs-fields">
            <h2><span><?php echo esc_html( __( 'Google Sheet Settings', 'gsconnector' ) ); ?></span></h2>
            <p>
               <label><?php echo esc_html( __( 'Google Sheet Name', 'gsconnector' ) ); ?></label>
               <select name="cf7-gs[sheet-name]" id="gs-sheet-name" >
                  <option value=""><?php echo __('Select', 'gsconnector'); ?></option>
                  <?php 
                     if ( ! empty( $sheet_data ) ) {                        
                        foreach( $sheet_data as $key=>$value ) { 
                           $selected = "";
                           if( $saved_sheet_name !== "" && $key == $saved_sheet_name ) {
                              $selected = "selected";
                           }
                        ?>
                           <option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $key; ?></option>
                        <?php
                        }
                     }
                  ?>
               </select>
               <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
               <input type="hidden" name="gs-ajax-nonce" id="gs-ajax-nonce" value="<?php echo wp_create_nonce( 'gs-ajax-nonce' ); ?>" />
            </p>
            <p>
               <label><?php echo esc_html( __( 'Google Sheet Tab Name', 'gsconnector' ) ); ?></label>
               
               <select name="cf7-gs[sheet-tab-name]" id="gs-sheet-tab-name" >
                  <?php 
                     if( $saved_sheet_name !== "" ) {
                        $selected_tabs = $sheet_data[ $saved_sheet_name ];
                        if ( ! empty( $selected_tabs ) ) {
                           foreach( $selected_tabs as $tab ) { 
                              $selected = "";
                              if( $saved_tab_name !== "" && $tab == $saved_tab_name ) {
                                 $selected = "selected";
                              }
                        ?>
                              <option value="<?php echo $tab;?>" <?php echo $selected; ?> ><?php echo $tab; ?></option>
                        <?php      
                           }
                        }
                     }
                  ?>
               </select>
            </p> 
            <p class="sheet-url" id="sheet-url">
				<?php
				$getsheets_id = get_option('gs_sheetId');
            if( $getsheets_id ) {
               foreach( $getsheets_id as $key => $val ){
                  if( $saved_sheet_name !== "" && $key == $saved_sheet_name ) { ?>
                     <label><?php echo __( 'Google Sheet URL', 'gsconnector'); ?> </label> <a href="https://docs.google.com/spreadsheets/d/<?php echo $val; ?>/edit#gid=0" target="_blank"><?php echo __( 'Sheet URL', 'gsconnector'); ?></a>
               <?php }
            }
				}
				?>
				</p>
            <p class="gs-sync-row"><?php echo __('Not showing Sheet Name, Tab Name and Sheet URL Link ? <a id="gs-sync" data-init="no">Click here </a> to fetch it.', 'gsconnector' );?><span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>
         </div>
         <?php
			include( GS_CONNECTOR_PRO_PATH . "includes/pages/gs-field-list.php" );
			
			include( GS_CONNECTOR_PRO_PATH . "includes/pages/gs-special-mail-tags.php" );
			
			include( GS_CONNECTOR_PRO_PATH . "includes/pages/gs-custom-mail-tags.php" );
		 ?>
         
      </form>
      <?php
   }
   
   /**
    * Function - display contact form fields to be mapped to google sheet
    * @param int $form_id
    * @since 1.0
    */
   public function display_form_fields( $form_id ) { ?>
      <?php
      // fetch saved fields
      $saved_mail_tags = get_post_meta( $form_id, 'gs_map_mail_tags' );
      
      // fetch mail tags
      $assoc_arr = [ ];
      $meta = get_post_meta( $form_id, '_form', true );
      $fields = $this->get_contact_form_fields( $meta );
      if( $fields ) {
         foreach ( $fields as $field ) {
            $single = $this->get_field_assoc( $field );
            if ( $single ) {
               $assoc_arr[] = $single;
            }
         }
      }
      
      if( ! empty( $assoc_arr ) ) {
      ?>
	  <input type="checkbox" id="fckbCheckAll" ><span class="select-all-field">Select All </span><br>
      
      <table class="gs-field-list">
      <?php
      $count = 0;
      foreach ( $assoc_arr as $key => $value ) {
         foreach ( $value as $k => $v ) {
            $saved_val = "";
            $checked = "";
            if( ! empty( $saved_mail_tags ) && array_key_exists( $v, $saved_mail_tags[0] ) ) :
               $saved_val = $saved_mail_tags[0][$v];
               $checked = "checked";
            endif;
            
            $placeholder = preg_replace('/[\\_]|\\s+/', '-', $v );
            ?>
               <tr>
                  <td><input type="checkbox" class="fcheckBoxClass" name="gs-custom-ck[<?php echo $count; ?>]" value="1" <?php echo $checked; ?> ></td>
                  <td><?php echo $v; ?> : </td>
                  <td>
                     <input type="hidden" value="<?php echo $v; ?>" name="gs-custom-header-key[<?php echo $count; ?>]">
                     <input type="hidden" value="<?php echo $placeholder; ?>" name="gs-custom-header-placeholder[<?php echo $count; ?>]">
                     <input type="text" name="gs-custom-header[<?php echo $count; ?>]" value="<?php echo $saved_val; ?>" placeholder="<?php echo $placeholder; ?>">
                  </td>
               </tr>
         <?php 
         $count++;
         }
      }
      ?>
      </table>
      <?php
      } else {
         echo '<p><span class="gs-info">' . __( 'No mail tags available.','gsconnector' ) . '</span></p>';
      }
   }
   
   /**
    * Function - display contact form Custom mail tags to be mapped to google sheet
    * @since 1.0
    */
   public function display_form_special_tags( $form_id ) {
      
      $custom_mail_tags = array();
      
      // fetch saved fields
      $saved_smail_tags = get_post_meta( $form_id, 'gs_map_special_mail_tags' );
      
      $tags_count = count( $this->special_mail_tags );
      $num_of_cols = 2;
      ?>
	  <input type="checkbox" id="ckbCheckAll" ><span class="select-all">Select All </span><br>
	  
      <table class="gs-field-list">
         <?php 
            echo '<tr>';
            for ( $i = 0; $i <= $tags_count; $i++ ) {
               if ( $i == $tags_count ) {
                  break;
               }  
               $tag_name = $this->special_mail_tags[ $i ];
               $saved_val = "";
               $checked = "";
               if( ! empty( $saved_smail_tags ) && array_key_exists( $tag_name, $saved_smail_tags[0] ) ) :
                  $saved_val = $saved_smail_tags[0][$tag_name];
                  $checked = "checked";
               endif;
               
               $placeholder = str_replace( '_', '-', $tag_name );
            
               echo '<td><input type="checkbox" class="checkBoxClass" name="gs-st-ck['. $i . ']" value="1" '.$checked.'></td>';
               echo '<td>[_' . $tag_name . ']</td>';
               echo '<td class="gs-r-pad"><input type="hidden" name="gs-st-key['. $i . ']" value="'. $tag_name .'" ><input type="hidden" name="gs-st-placeholder['. $i . ']" value="'. $placeholder .'" ><input type="text" name="gs-st-custom-header['. $i . ']" value="' . $saved_val . '" placeholder="'. $placeholder .'"></td>';
               if ( $i % $num_of_cols == 1 ) {
                     echo '</tr><tr>';
                  }
               }
         ?>
      </table>
      <?php  
   }

   function display_form_custom_tag($form_id){
		$custom_mail_tags = array();
		$num_of_cols = 2;
	   
      if ( has_filter( "gscf7_special_mail_tags" ) ) {
         // Filter hook for custom mail tags
         $custom_tags = apply_filters( "gscf7_special_mail_tags", $custom_mail_tags, $form_id );
         $custom_tags_count = count( $custom_tags );
         $num_of_cols = 2;
         // fetch saved fields
         $saved_cmail_tags = get_post_meta( $form_id, 'gs_map_custom_mail_tags' );
      ?>
         <table class="gs-field-list">
            <?php 
               echo '<tr>';
               for ( $i = 0; $i <= $custom_tags_count; $i++ ) {
                  if ( $i == $custom_tags_count ) {
                     break;
                  } 
                  $tag_name = $custom_tags[ $i ];
                  $modify_tag = ltrim( $tag_name, '_' );
                  $saved_val = "";
                  $checked = "";
                  if( ! empty( $saved_cmail_tags ) && array_key_exists( $modify_tag, $saved_cmail_tags[0] ) ) :
                     $saved_val = $saved_cmail_tags[0][$modify_tag];
                     $checked = "checked";
                  endif;
                  
                  //hack - todo
                  $placeholder_explode = explode( '_', $tag_name, 2 );
                  $placeholder = str_replace( '_', '-', $placeholder_explode[1] );
               
                  echo '<td><input type="checkbox" name="gs-ct-ck['. $i . ']" value="1" ' . $checked . '></td>';
                  echo '<td>[' . $tag_name . ']</td>';
                  echo '<td class="gs-r-pad"><input type="hidden" name="gs-ct-key['. $i . ']" value="' . $tag_name . '" ><input type="hidden" name="gs-ct-placeholder['. $i . ']" value="' . $placeholder . '" ><input type="text" name="gs-ct-custom-header['. $i . ']" value="' . $saved_val . '" placeholder="'. $placeholder .'"></td>';
                  if ( $i % $num_of_cols == 1 ) {
                        echo '</tr><tr>';
                     }
                  }
            ?>
         </table>
      <?php 
      } else {
         echo '<p><span class="gs-info">' . __( 'No custom mail tags available.','gsconnector' ) . '</span></p>';
      } 
	   
   }
   
   /**
    * Function - fetch contant form list that is connected with google sheet
    * @since 1.0
    */
   public function get_forms_connected_to_sheet() {
      global $wpdb;
      
      $query = $wpdb->get_results("SELECT ID,post_title,meta_value from ".$wpdb->prefix."posts as p JOIN ".$wpdb->prefix."postmeta as pm on p.ID = pm.post_id where pm.meta_key='gs_settings' AND p.post_type='wpcf7_contact_form'");
      return $query;
   }
   
   public function get_contact_form_fields( $meta ) {
      $regexp = '/\[.*\]/';
      $arr = [];
      if ( preg_match_all($regexp, $meta, $arr) == false) {
          return false;
      }
      return $arr[0];
   }
   
   public function get_field_assoc($content) {
      $regexp_type = '/(?<=\[)[^\s\*]*/';
      $regexp_name = '/(?<=\s)[^\s\]]*/';
      $arr_type = [];
      $arr_name = [];
      if (preg_match($regexp_type, $content, $arr_type) == false) {
          return false;
      }
      if (!in_array($arr_type[0], $this->allowed_tags)) {
          return false;
      }
      if (preg_match($regexp_name, $content, $arr_name) == false) {
          return false;
      }
      return array($arr_type[0] => $arr_name[0]);
   }
   
   public function save_uploaded_files_local( $files ) {
      $upload = wp_upload_dir();
      if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
         // Generate the yearly and monthly dirs
         $time = current_time( 'mysql' );
         $y = substr( $time, 0, 4 );
         $m = substr( $time, 5, 2 );
         $upload['subdir'] = "/$y/$m";
      }

      $upload['subdir'] = '/cf7gs' . $upload['subdir'];
      $upload['path'] = $upload['basedir'] . $upload['subdir'];
      $upload['url'] = $upload['baseurl'] . $upload['subdir'];

      if ( ! is_dir( $upload['path'] ) ) {
         wp_mkdir_p( $upload['path'] );
      }

      $htaccess_file = sprintf( '%s/.htaccess', $upload['path'] );

      // Make sure that uploads directory is protected from listing
      if ( !file_exists( $htaccess_file ) )
         file_put_contents( $htaccess_file, 'Options -Indexes' );

      $uploads_stored = array();

      foreach ( $files as $name => $path ) {
         if ( ! isset( $_FILES[ $name ] ) )
            continue;

         $file_name = basename( $path );
         $destination = sprintf( '%s/%s', $upload['path'], $file_name );
         $destination_url = sprintf( '%s/%s', $upload['url'], $file_name );
         $uploads_stored[ $name ] = $destination_url;
         @copy( $path, $destination );
      }
      return $uploads_stored;
   }

}

$gs_connector_service = new Gs_Connector_Service();


