<?php
/* @access      public
 * @since       1.1 
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}
function create_user_from_registration($cfdata) {
    global $wpcf7,$loginlink;
    $post_id = sanitize_text_field($_POST['_wpcf7']);
    $cf7fru = get_post_meta($post_id, "_cf7fru_", true);
    $cf7fre = get_post_meta($post_id, "_cf7fre_", true);
    $returnfieldarr = filterarray();
    foreach ($returnfieldarr as $key => $value) {
        $cf7 = $value;
        $$cf7 = get_post_meta($post_id, "_cf7".$value."_", true);
    }
    $cf7frr = get_post_meta($post_id, "_cf7frr_", true);
    $cf7frel = get_post_meta($post_id, "_cf7frel_", true);
    $passwordfield = get_post_meta($post_id, "_cf7fr_passwordfield_registration", true);
    $cf7frp = get_post_meta($post_id, "_cf7frp_", true);

    $enablemail = get_post_meta($post_id,'_cf7fr_enablemail_registration');
    if($enablemail[0]==1){ $cfdata->skip_mail = true; }
    
    $enable = get_post_meta($post_id,'_cf7fr_enable_registration');
    if($enable[0]!=0)
    {
            if (!isset($cfdata->posted_data) && class_exists('WPCF7_Submission')) {
                $submission = WPCF7_Submission::get_instance();
                if ($submission) {
                    $formdata = $submission->get_posted_data();
                }
            } elseif (isset($cfdata->posted_data)) {
                $formdata = $cfdata->posted_data;
            } 
        $email = $formdata["".$cf7fre.""];
        $name = $formdata["".$cf7fru.""];
        $pass = $formdata["".$cf7frp.""];
        // Construct a username from the user's name
        $username = strtolower(str_replace(' ', '', $name));
        $name_parts = explode(' ',$name);
        if ( !email_exists( $email ) ) 
        {
            //Find an unused username
            $username_tocheck = $username;
            $i = 1;
            while ( username_exists( $username_tocheck ) ) {
                $username_tocheck = $username . $i++;
            }
            $username = $username_tocheck;
            // Create the user
            
            foreach ($returnfieldarr as $key => $value) {
                    $cf7 = $value;
                    $key = "_cf7".$value."_";
                    $cf7 = get_post_meta($post_id, $key, true);
                    $dynamicarray[$value] = $formdata[$cf7];
                    
                }
                
            if($passwordfield =="1"){
                $password = $pass;
            }else{
                $password = wp_generate_password( 12, false );
            }
            // Create the user
            $userdata = array(
                'user_login' => $username,
                'user_pass' => $password,
                'user_email' => $email,
                'role' => $cf7frr
            );
            $mergeuserdata = array_merge($dynamicarray,$userdata);
            $user_id = wp_insert_user( $mergeuserdata );
            if($user_id)
            {
                if($dynamicarray){
                foreach ($dynamicarray as $key => $value) {
                        if(substr($key, 0,1)=="_"){
                            $field_name = str_replace_first("_","",$key);
                            $fieldkey = acf_get_field_key($field_name);
                            update_user_meta( $user_id, $key, $fieldkey );
                            update_user_meta( $user_id, $field_name, $value );
                        }else{
                            update_user_meta( $user_id, $key, $value );
                        }
                    }
                }
            }
            if(!$cf7frel)
            {
                $loginlink = wp_login_url();
            }
            else
            {
                $loginlink = $cf7frel;
            }
            if ( !is_wp_error($user_id) ) {
                // Email login details to user
                sendEmailToUser($post_id, $email, $username, $loginlink, $password);    
            }
            
        }

    }
    return $cfdata;
}
add_action('wpcf7_before_send_mail', 'create_user_from_registration', 1, 2);
function your_validation_text_func( $result, $tag ) 
{
    global $wpcf7;
    $post_id = sanitize_text_field($_POST['_wpcf7']);
    $cf7fru = get_post_meta($post_id, "_cf7fru_", true);
    $tag = new WPCF7_Shortcode( $tag );
    $type = $tag->type;
    $name = $tag->name;
    global $wpdb;
    if(isset($_POST[''.$cf7fru.'']) && $_POST[''.$cf7fru.'']!="")
    {
        if($name =="".$cf7fru."")
        {
            $username = $_POST[''.$cf7fru.''];
            if(username_exists($username))
            {
                   $result->invalidate($tag, "Username already registered!");
                 
            }

        }
    }
   
    return $result;
 }
add_filter( 'wpcf7_validate_text*', 'your_validation_text_func', 20, 2 );
function your_validation_password_func( $result, $tag ) 
{
    global $wpcf7;
    $tag = new WPCF7_Shortcode( $tag );
    $type = $tag->type;
    $name = $tag->name;
    $name2 = $tag->name."-2";
    //global $wpdb;
    if(isset($_POST[''.$name.'']) && $_POST[''.$name.'']=="")
    {
       $result->invalidate($tag, "Please enter Password");
    }
    if(isset($_POST[''.$name2.'']) && $_POST[''.$name2.'']=="")
    {
       $result->invalidate($tag, "Please enter Confirm Password");
    }
    if($_POST[''.$name.'']!=$_POST[''.$name2.''])
    {
        $result->invalidate($tag, "Password & Confirm Password do not match.");   
    }
   
    return $result;
 }
add_filter( 'wpcf7_validate_password*', 'your_validation_password_func', 20, 2 );
function your_validation_email_filter( $result, $tag ) 
{
    global $wpcf7;
    $post_id = sanitize_text_field($_POST['_wpcf7']);
    $cf7fre = get_post_meta($post_id, "_cf7fre_", true);
    $tag = new WPCF7_Shortcode( $tag );
    $type = $tag->type;
    $name = $tag->name;
    global $wpdb;
     if(isset($_POST[''.$cf7fre.'']) && $_POST[''.$cf7fre.'']!="")
    {
        if($name =="".$cf7fre."")
        {
            $email = $_POST[''.$cf7fre.''];
            if(email_exists($email))
            {
                $result->invalidate($tag, "Email already registered!");
                 
            }
        }
    }
    return $result;
}
add_filter( 'wpcf7_validate_email*', 'your_validation_email_filter', 20, 2 );
add_filter( 'wpcf7_validate_email', 'your_validation_email_filter', 20, 2 );
add_filter( 'wp_mail_content_type', 'set_content_type' );
function set_content_type( $content_type ) {
	return 'text/html';
}
function sendEmailToUser($post_id, $email, $username, $loginlink, $password)
{
    $_cf7frfrom_ = get_post_meta($post_id, "_cf7frfrom_", true);
    $_cf7frsub_ = get_post_meta($post_id, "_cf7frsub_", true);
    $_cf7freb_ = get_post_meta($post_id, "_cf7freb_", true);
    $cf7frr = get_post_meta($post_id, "_cf7frr_", true);
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    $enablcustomemail = get_post_meta($post_id,'_cf7fr_enablcustomemail_registration');
    
    if($enablcustomemail[0]==1){
        $_cf7freb_ = str_replace("[login-user]",$username,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-user-name]",$username,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-password]",$password,$_cf7freb_);
        $_cf7freb_ = str_replace("[site-name]",$blogname,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-link]",$loginlink,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-email]",$email,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-role]",$cf7frr,$_cf7freb_);
        //$headers[] = 'MIME-Version: 1.0' . "\r\n";
        // Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: '.$blogname.' <'.$_cf7frfrom_.'>' . "\r\n";
        $body = nl2br($_cf7freb_,false);
        $body = html_entity_decode($body);
        $message = '<html>';
        $message .= '<body>';
        $message .= $body;
        $message .= '</body>';
        $message .= '</html>';
        //mail($to,$subject,$message,$headers);
        wp_mail($email, sprintf(__('[%s] - '.$_cf7frsub_), $blogname), $message, $headers);
    }else{
        $message = "Welcome! Your login details are as follows:" . "\r\n";
        $message .= sprintf(__('Username: %s'), $username) . "\r\n";
        $message .= sprintf(__('Password: %s'), $password) . "\r\n";
        $message .= $loginlink . "\r\n";
        wp_mail($email, sprintf(__('[%s] Your username and password'), $blogname), $message);
    }
}
function acf_field_key($field_name, $post_id = false){
    
    if ( $post_id )
        return get_field_reference($field_name, $post_id);
    
    if( !empty($GLOBALS['acf_register_field_group']) ) {
        
        foreach( $GLOBALS['acf_register_field_group'] as $acf ) :
            
            foreach($acf['fields'] as $field) :
                
                if ( $field_name === $field['name'] )
                    return $field['key'];
            
            endforeach;
            
        endforeach;
    }
        return $field_name;
}
function acf_get_field_key( $field_name ) {
    global $wpdb;
    $result = $wpdb->get_results("SELECT * from ".$wpdb->prefix."postmeta WHERE meta_value like '%".$field_name."%' AND meta_key like '%field_%'");
    return $result[0]->meta_key;
}
function str_replace_first($from, $to, $subject)
{
    $from = '/'.preg_quote($from, '/').'/';
    return preg_replace($from, $to, $subject, 1);
}
?>