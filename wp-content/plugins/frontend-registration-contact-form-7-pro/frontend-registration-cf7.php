<?php
/**
 * Plugin Name: Frontend Registration - Contact Form 7 Pro
 * Plugin URL: http://www.wpbuilderweb.com/frontend-registration-contact-form-7/
 * Description:  This plugin will convert your Contact form 7 in to registration form for WordPress. You can also use User meta field by created ACF plugin.
 * Version: 3.1
 * Author: David Pokorny
 * Author URI: http://www.wpbuilderweb.com/
 * Developer: Pokorny David
 * Developer E-Mail: pokornydavid4@gmail.com
 * Text Domain: contact-form-7-freg
 * Domain Path: /languages
 * 
 * Copyright: © 2009-2015 izept.com.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
/**
 * 
 * @access      public
 * @since       1.1
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}
require_once (dirname(__FILE__) . '/frontend-registration-opt-cf7.php');
register_activation_hook (__FILE__, 'cf7fr_submit_activation_check');
/*wp_enqueue_script( 'password-strength-meter' );*/
function cf7fr_submit_activation_check()
{
    if ( !in_array( 'contact-form-7/wp-contact-form-7.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        wp_die( __( '<b>Warning</b> : Install/Activate Contact Form 7 to activate "Contact Form 7 - Frontend Registration" plugin', 'contact-form-7' ) );
    }
}
add_action('init', 'contact_form_7_password_field', 11);
function contact_form_7_password_field() {	
	if(function_exists('wpcf7_add_shortcode')) {
		wpcf7_add_shortcode( 'Password*', 'wpcf7_password_field_shortcode_handler', true );		
	} else {
		 return; 		
	}
}
add_action( 'admin_enqueue_scripts', 'load_admin_style' );
function load_admin_style() {
$plugins_url = plugins_url();
wp_enqueue_style( 'admin_css', $plugins_url . '/frontend-registration-contact-form-7-pro/css/style.css', false, '1.0.0' );
}
function filterarray()
{
	$users = get_users();
	foreach ($users as $key => $value) {
		if($value->data->ID){
			$useridget = $value->data->ID;
			break;
		}
	}
	$user_data = wp_update_user( array( 'ID' => $useridget ) );
	$all_meta_for_user = get_user_meta($useridget);
	
	$returnfiltered = array();
	foreach ($all_meta_for_user as $key => $value) {
		$returnfiltered[] = $key;
	}
	$excludefields = array('rich_editing','comment_shortcuts','admin_color','use_ssl','show_admin_bar_front','wp_capabilities','wp_user_level','last_activity','closedpostboxes_page','metaboxhidden_page','meta-box-order_product','dismissed_wp_pointers','show_welcome_panel','session_tokens','screen_layout_product','pmpro_visits','wp_dashboard_quick_press_last_post_id','manageedit-shop_ordercolumnshidden','pmpro_views','_woocommerce_persistent_cart','nav_menu_recently_edited','managenav-menuscolumnshidden','metaboxhidden_nav-menus','managenav-menuscolumnshidden','wp_user-settings','wp_user-settings-time','wpro_capabilities','wpro_user_level','_yoast_wpseo_profile_updated','last_update','wpseo-remove-upsell-notice','wpseo-dismiss-onboarding-notice','wpseo-dismiss-gsc','paying_customer','wpro_yoast_notifications','wpro_dashboard_quick_press_last_post_id','wpro_user-settings','wpro_user-settings-time');
	$result = array_diff($returnfiltered,$excludefields);
	foreach ($result as $key => $value) {
		if(substr($value,0,1)=='_')
		{
			$newresult[] = ltrim($value, '_');
		}
	}
	if($newresult){
			$resultlast = array_diff($result,$newresult);
		}else{
			$resultlast = $result;
		}
	return $resultlast;	
}

function wpcf7_password_field_shortcode_handler( $tag ) {		
	$tag = new WPCF7_Shortcode( $tag );	
	$class = wpcf7_form_controls_class( $tag->type );	
	$validation_error = wpcf7_get_validation_error( $tag->name );
	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}
	$atts = array();	
	$atts2 = array();	
	$atts['class'] = $tag->get_class_option( $class );
	$atts2['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

	$value = isset( $tag->values[0] ) ? $tag->values[0] : '';
	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
		$atts2['aria-required'] = 'true';
	}
	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';
	if ( empty( $value ) )
		$value = __( 'Submit', 'contact-form-7' );

	$atts['type'] = 'password';
	$atts2['type'] = 'password';
	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$atts2['placeholder'] = 'Confirm Password';
		$value = '';
	}
	$atts['name'] = $tag->name;
	$atts2['name'] = $tag->name."-2";
	$atts = wpcf7_format_atts( $atts );
	$atts2 = wpcf7_format_atts( $atts2 );

	$htmls = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error );
	$htmls .= sprintf(
		'<br/><label>'.esc_html( __( 'Confirm Password (Required)', 'contact-form-7' ) ).'</label><span class="wpcf7-form-control-wrap %1$s"><br/><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts2, $validation_error );

	return $htmls;
}
/************************************~: Admin Section of Password Field :~************************************/

/* Tag generator */
add_action( 'admin_init', 'wpcf7_add_tag_generator_password_field', 55 );
function wpcf7_add_tag_generator_password_field() {	
	if(class_exists('WPCF7_TagGenerator')){
		$tag_generator = WPCF7_TagGenerator::get_instance();
		$tag_generator->add( 'password', __( 'Password*', 'contact-form-7' ),
		'wpcf7_tg_pane_password_field');
	}	
}
/** Parameters field for generating tag at backend **/
function wpcf7_tg_pane_password_field( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$description = __( "Generate a form-tag for a Password field which is use for making your password by user after submitting the form.", 'contact-form-7' );
	$desc_link = wpcf7_link( '',__( 'Password field', 'contact-form-7' ) );
?>
<div class="control-box">
<fieldset>
	<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>
	<table class="form-table">
		<tbody>
			<tr>
				<td colspan="2"><b>NOTE: Set this field to direct provide to user for set password at the time of registration.It's By default Required Field.</b></td>
			</tr>
			<tr>
				<td><code>id</code> <?php echo '<font style="font-size:10px"> (optional)</font>';?><br />
				<input type="text" name="id" class="idvalue oneline option" /></td>
				<td><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (optional)</font>'; ?><br/>
				<input type="text" name="name" class="tg-name oneline" id="tag-generator-panel-password*-name"/></td>
			</tr>
			<tr>
				<td><code>class</code> <?php echo '<font style="font-size:10px"> (optional)</font>'; ?><br />
				<input type="text" name="class" class="classvalue oneline option" /></td>
			
				<td><?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (optional)</font>'; ?><br/>
					<input name="values" class="oneline" id="tag-generator-panel-password-values" type="text"><br>
				<label><input name="placeholder" class="option" type="checkbox"> Use this text as the placeholder of the field</label></td>
			</tr>
		</tbody>
	</table>
</fieldset>
</div>
<div class="insert-box">
	<input type="text" name="password*" class="tag code" readonly="readonly" onfocus="this.select()" />
	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>
</div>
<?php
}

function cf7fr_editor_panels_reg ( $panels ) {
		
		$new_page = array(
			'Error' => array(
				'title' => __( 'Registration Settings', 'contact-form-7' ),
				'callback' => 'cf7fr_admin_reg_additional_settings'
			)
		);
		
		$panels = array_merge($panels, $new_page);
		
		return $panels;
		
	}
	add_filter( 'wpcf7_editor_panels', 'cf7fr_editor_panels_reg' );

function cf7fr_admin_reg_additional_settings( $cf7 )
{
	
	$post_id = sanitize_text_field($_GET['post']);
	$tags = $cf7->form_scan_shortcode();
	$enable = get_post_meta($post_id, "_cf7fr_enable_registration", true);
	$enablemail = get_post_meta($post_id, "_cf7fr_enablemail_registration", true);
	$enablcustomemail = get_post_meta($post_id, "_cf7fr_enablcustomemail_registration", true);
	$passwordfield = get_post_meta($post_id, "_cf7fr_passwordfield_registration", true);
	$returnfieldarr = filterarray();
	$cf7fru = get_post_meta($post_id, "_cf7fru_", true);
	$cf7fre = get_post_meta($post_id, "_cf7fre_", true);
	foreach ($returnfieldarr as $key => $value) {
		$cf7 = $value;
		$$cf7 = get_post_meta($post_id, "_cf7".$value."_", true);
	}
	$cf7frr = get_post_meta($post_id, "_cf7frr_", true);
	$cf7frel = get_post_meta($post_id, "_cf7frel_", true);
	$_cf7frfrom_ = get_post_meta($post_id, "_cf7frfrom_", true);
	$_cf7frsub_ = get_post_meta($post_id, "_cf7frsub_", true);
	$_cf7freb_ = get_post_meta($post_id, "_cf7freb_", true);
	$cf7frp = get_post_meta($post_id, "_cf7frp_", true);
	$selectedrole = $cf7frr;
	if(!$selectedrole)
	{
		$selectedrole = 'subscriber';
	}
	if ($enable == "1") { $checked = "CHECKED"; } else { $checked = ""; }
	if ($enablemail == "1") { $checkedmail = "CHECKED"; } else { $checkedmail = ""; }
	if ($enablcustomemail == "1") { $cenablcustomemail = "CHECKED"; } else { $cenablcustomemail = ""; }
	if ($passwordfield == "1") { $passwordfield = "CHECKED"; } else { $passwordfield = ""; }
	$selected = "";
	$admin_cm_output = "";
	echo "<div id='additional_settings-sortables' class='meta-box'><div id='additionalsettingsdiv'>";
	echo "<div class='handlediv' title='Click to toggle'><br></div><h3 class='hndle ui-sortable-handle'><span>Frontend Registration Settings</span></h3>";
	echo "<div class='inside'>";
	echo "<div class='mail-field'>";
	echo "<input name='enable' type='checkbox' $checked>";
	echo "<label>Enable Registration on this form</label>";
	echo "</div>";
	echo "<div class='mail-field'>";
	echo "<input name='enablemail' value='' type='checkbox' $checkedmail>";
	echo "<label>Skip Contact Form 7 Mails ?</label>";
	echo "</div>";
	echo "<div class='mail-field'>";
	echo "<input name='enablcustomemail' value='' type='checkbox' $cenablcustomemail>";
	echo "<label>Enable custom email for registration detail?</label>";
	echo "</div>";
	echo "<div class='mail-field'>";
	echo "<input name='passwordfield' value='' type='checkbox' $passwordfield>";
	echo "<label>Enable Password field for registration ?</label>";
	echo "</div><table>";
	echo "<tr><td>Insert Custom Login Link Here :</td></tr>";
	echo "<tr><td><input type='text' name='_cf7frel_' value='$cf7frel' size='50' /></td></tr>";
	echo "<tr><td>Selected Field Name For User Name :</td></tr>";
	echo "<tr><td><select name='_cf7fru_'>";
	echo "<option value=''>Select Field</option>";
	foreach ($tags as $key => $value) {
		if($cf7fru==$value['name']){$selected='selected=selected';}else{$selected = "";}			
		echo "<option ".$selected." value='".$value['name']."'>".$value['name']."</option>";
	}
	echo "</select>";
	echo "</td></tr>";
	echo "<tr><td>Selected Field Name For Email Address :</td></tr>";
	echo "<tr><td><select name='_cf7fre_'>";
	echo "<option value=''>Select Field</option>";
	foreach ($tags as $key => $value) {
		if($cf7fre==$value['name']){$selected='selected=selected';}else{$selected = "";}			
		echo "<option ".$selected." value='".$value['name']."'>".$value['name']."</option>";
	}
	echo "</select>";
	echo "</td></tr>";
	echo "<tr><td><strong>Select Other User Fields values :</strong></td></tr><br/><br/>";
	foreach ($returnfieldarr as $key => $value) {
		echo "<tr border='1'><td class='border'>Selected Field Name For <strong>".$value."</strong>:";
		echo "</td><td><select name='_cf7".$value."_'>";
		echo "<option value=''>Select Field</option>";
		$cf7 = $value;
		foreach ($tags as $key => $values) {
			if($$cf7==$values['name']){$selected='selected=selected';}else{$selected = "";}			
			echo "<option ".$selected." value='".$values['name']."'>".$values['name']."</option>";
		}
		echo "</select>";
		echo "</td></tr>";
	}
	echo "<br/><tr><td style='color:red;'><b>Note :</b> Above Field list are display from User Meta Table. If your custom Field not listed in above list then Just go in your admin Profile and Once Update Profile from Admin side. <a href='".get_site_url()."/wp-admin/profile.php'>Click Here and Update Profile.</a> For Custom field we prefered Advance Custom Field Plugin (ACF Plugin).</td></tr>";
	echo "<br/><br/><tr><td>Selected User Role:</td></tr>";
	echo "<tr><td>";
	echo "<select name='_cf7frr_'>";
		wp_dropdown_roles($selectedrole);
	echo "</select>";
	echo "</td></tr>";
	echo "<tr><td>Selected Field Name For Password :</td></tr>";
	echo "<tr><td><select name='_cf7frp_'>";
	echo "<option value=''>Select Field</option>";
	foreach ($tags as $key => $value) {
		if($cf7frp==$value['name']){$selected='selected=selected';}else{$selected = "";}
		echo "<option ".$selected." value='".$value['name']."'>".$value['name']."</option>";
	}
	/*$roles = get_editable_roles();*/
	echo "</select>";
	echo "</td></tr>";
	
	echo "<tr><td><strong><br><br>Email Settings :</strong></td></tr><br>";
	echo "<tr><td>Use this shortcode for Mail content : [site-name] &nbsp; [login-link] &nbsp; [login-user] &nbsp; [login-user-name] &nbsp; [login-email] &nbsp; [login-password] &nbsp; [login-role]</td></tr>";
	echo "<tr><td>Add Email From Here :</td></tr>";
	echo "<tr><td><input type='text' name='_cf7frfrom_' value='$_cf7frfrom_' size='50' /></td></tr>";
	echo "<tr><td>Add Email Subject Here:</td></tr>";
	echo "<tr><td><input type='text' name='_cf7frsub_' value='$_cf7frsub_' size='50' /></td></tr>";
	echo "<tr><td>Add Email Body Content Here :</td></tr>";
	echo "<tr><td>";
	echo "<textarea name='_cf7freb_' cols='70' rows='10'>$_cf7freb_</textarea>";
	echo "</td></tr>";
	echo "<tr><td>";
	echo "<input type='hidden' name='email' value='2'>";
	echo "<input type='hidden' name='post' value='$post_id'>";
	echo "</td></tr></table>";
	echo "</div>";
	echo "</div>";
	echo "</div>";
	//echo $admin_cm_output;
}
// hook into contact form 7 admin form save
add_action('wpcf7_save_contact_form', 'cf7_save_reg_contact_form');
function cf7_save_reg_contact_form( $cf7 ) {
		$tags = $cf7->form_scan_shortcode();
		$post_id = sanitize_text_field($_POST['post']);
		if (isset($_POST['enable'])) {
			update_post_meta($post_id, "_cf7fr_enable_registration", 1);
		} else {
			update_post_meta($post_id, "_cf7fr_enable_registration", 0);
		}
		if (isset($_POST['enablemail'])) {
			update_post_meta($post_id, "_cf7fr_enablemail_registration", 1);
		} else {
			update_post_meta($post_id, "_cf7fr_enablemail_registration", 0);
		}
		if (isset($_POST['enablcustomemail'])) {
			update_post_meta($post_id, "_cf7fr_enablcustomemail_registration", 1);
		} else {
			update_post_meta($post_id, "_cf7fr_enablcustomemail_registration", 0);
		}
		if (isset($_POST['passwordfield'])) {
			update_post_meta($post_id, "_cf7fr_passwordfield_registration", 1);
		} else {
			update_post_meta($post_id, "_cf7fr_passwordfield_registration", 0);
		}
		$key = "_cf7frel_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);
		$key = "_cf7fru_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);

		$returnfieldarr = filterarray();
		foreach ($returnfieldarr as $key => $value) {
			$key = "_cf7".$value."_";
			$vals = sanitize_text_field($_POST[$key]);
			update_post_meta($post_id, $key, $vals);
		}
		$key = "_cf7fre_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);

		$key = "_cf7frp_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);

		$key = "_cf7frr_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);

		$key = "_cf7frfrom_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);

		$key = "_cf7frsub_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);

		$key = "_cf7freb_";
		$vals = htmlentities($_POST[$key]);
		update_post_meta($post_id, $key, $vals);
}
?>