<?php


/*
*Select SMS Type
*/


if (!function_exists('wpestate_select_sms_type')):
    function wpestate_select_sms_type($user_mobile,$type,$arguments,$user_email, $user_data_id){
        $current_user = wp_get_current_user();
        $userID                 =   $current_user->ID;

        $sms_verification =esc_html( wprentals_get_option('wp_estate_sms_verification',''));
        if($sms_verification!=='yes'){
            return;
        }
       
        
        
        if($user_data_id!=0 && $type!='validation'){
            $roles=array('administrator');
            if( array_intersect($roles, $current_user->roles )){
               //is admin - do not check
            }else{
                $check_phone = get_the_author_meta( 'check_phone_valid' , $user_data_id);
                if($check_phone!='yes'){
                    return;
                }
            }
        }
        
        
        
        $sms_data =( rcapi_retrive_sms());
        $sms_data = json_decode($sms_data,true);
         
      
        if( isset($sms_data['use_sms'][$type]) && $sms_data['use_sms'][$type]==1 ){
            $value          =   $sms_data['sms_content'][$type];
            if (function_exists('icl_translate') ){
                $value          =  icl_translate('wpestate','wp_estate_sms_'.$value, $value ) ;
            }
            

            wpestate_sms_filter_replace($user_mobile,$value,$arguments,$user_email);
        }else{
            return;
        }
         
         
        
    }
endif;

/*
*Compose sms Message / replace 
*/


if( !function_exists('wpestate_sms_filter_replace')):
    function  wpestate_sms_filter_replace($user_phone_no,$message,$arguments,$user_email){
        $arguments ['website_url'] = get_option('siteurl');
        $arguments ['website_name'] = get_option('blogname');       
        $arguments ['user_email'] = $user_email;     
        $user= get_user_by('email',$user_email);
        $arguments ['username'] = $user-> user_login;
        
        foreach($arguments as $key_arg=>$arg_val){
            $to_replace =   trim('%'.$key_arg);
            $message    =   str_replace($to_replace, $arg_val, $message);
        }

        
       $response= rcapi_send_sms($user_phone_no, $message );    
       //print_r($response);
    }
endif;






/*
*Sedn the actula SMS
*/
function rcapi_send_sms($to,$body){
    
    $token= rcapi_retrive_token();
    
    $values_array=array(
        "to"       =>  $to,
        "body"     =>  $body,
       
    );
    

    
    // save sms content
    $url="http://www.rentalsclub.org/wp-json/rcapi/v1/sendsms/?access_token=".$token;
    $arguments = array(
	'method' => 'POST',
	'timeout' => 45,
	'redirection' => 5,
	'httpversion' => '1.0',
	'blocking' => true,
	'headers' => array(),
	'body' => $values_array,
	'cookies' => array()
    );
   $response= wp_remote_post($url,$arguments);
 

   
   
   return  json_decode( wp_remote_retrieve_body($response) ,true)  ;
 
}


/*
*Save the sms information to rentals club api account
*/
function rcapi_retrive_sms(){
    
    $token= rcapi_retrive_token();
    if($token==''){
        return;
    }
    
    // save sms functions 
    $url="http://www.rentalsclub.org/wp-json/rcapi/v1/sms/?access_token=".$token;
    $arguments = array(
	'method' => 'GET',
	'timeout' => 45,
	'redirection' => 5,
	'httpversion' => '1.0',
	'blocking' => true,
	'headers' => array(),
	'cookies' => array()
    );
    $response= wp_remote_post($url,$arguments);
    
    $body=  wp_remote_retrieve_body($response);
 
    return $body;
 
}




/*
*Save the sms information to rentals club api account
*/
function rcapi_save_sms($sms_content,$use_sms=array(),$twilio_api_key,$twilio_auth_token,$twilio_phone_no){
    
    $token= rcapi_retrive_token();
    
    $values_array=array(
        "sms_content"       =>  $sms_content,
        "use_sms"           =>  $use_sms,
        "twilio_api_key"    =>  $twilio_api_key,
        "twilio_auth_token" =>  $twilio_auth_token,
        "twilio_phone_no"   =>  $twilio_phone_no
    );
    
    // save sms content
    $url="http://www.rentalsclub.org/wp-json/rcapi/v1/sms/?access_token=".$token;
    $arguments = array(
	'method' => 'POST',
	'timeout' => 45,
	'redirection' => 5,
	'httpversion' => '1.0',
	'blocking' => true,
	'headers' => array(),
	'body' => $values_array,
	'cookies' => array()
    );
   $response= wp_remote_post($url,$arguments);
   

    return  json_decode( wp_remote_retrieve_body($response) ,true)  ;
 
}




if(!function_exists('wpestate_sms_notice_managment')):
    function wpestate_sms_notice_managment(){

       $sms_data =( rcapi_retrive_sms());
       $sms_data = json_decode($sms_data,true);


        $sms_array=array(
            'validation'                =>  __('Phone Number Validation','wprentals-core'),
          // scos ca nu are logica  'new_user'                  =>  __('New user notification','wprentals-core'),
            'admin_new_user'            =>  __('New user admin notification','wprentals-core'),
            'password_reset_request'    =>  __('Password Reset Request','wprentals-core'),
            'password_reseted'          =>  __('Password Reseted','wprentals-core'),
            'approved_listing'          =>  __('Approved Listings','wprentals-core'),
            'admin_expired_listing'     =>  __('Admin - Expired Listing','wprentals-core'),
            'paid_submissions'          =>  __('Paid Submission','wprentals-core'),
            'featured_submission'       =>  __('Featured Submission','wprentals-core'),
            'account_downgraded'        =>  __('Account Downgraded','wprentals-core'),
            'membership_cancelled'      =>  __('Membership Cancelled','wprentals-core'),
            'free_listing_expired'      =>  __('Free Listing Expired','wprentals-core'),
            'new_listing_submission'    =>  __('New Listing Submission','wprentals-core'),
            'recurring_payment'         =>  __('Recurring Payment','wprentals-core'),
            'membership_activated'      =>  __('Membership Activated','wprentals-core'),
            'agent_update_profile'      =>  __('Update Profile','wprentals-core'),
            'bookingconfirmeduser'      =>  __('Booking Confirmed - User','wprentals-core'),
            'bookingconfirmed'          =>  __('Booking Confirmed','wprentals-core'),
            'bookingconfirmed_nodeposit'=>  __('Booking Confirmed - no deposit','wprentals-core'),
            'inbox'                     =>  __('Inbox- New Message','wprentals-core'),
            'newbook'                   =>  __('New Booking Request','wprentals-core'),
            'mynewbook'                 =>  __('User - New Booking Request','wprentals-core'),
            'newinvoice'                =>  __('Invoice generation','wprentals-core'),
            'deletebooking'             =>  __('Booking request rejected','wprentals-core'),
            'deletebookinguser'         =>  __('Booking Request Cancelled','wprentals-core'),
            'deletebookingconfirmed'    =>  __('Booking Period Cancelled ','wprentals-core'),
            'new_wire_transfer'         =>  __('New wire Transfer','wprentals-core'),
            'admin_new_wire_transfer'   =>  __('Admin - New wire Transfer','wprentals-core'),
            'full_invoice_reminder'     =>  __('Invoice Payment Reminder','wprentals-core'),
        );
        
              
      
       
        print '<input type="hidden" name="is_club_sms" value="1">';
        
        print '<p class="admin-exp">'.esc_html__('SMS Management is offered through Twilio API','wprentals-core').' <a href="https://www.twilio.com/" target="_blank">https://www.twilio.com.</a> '.esc_html__('You will need an active account with them to use their SMS service and you may need to buy extra SMS as well. Your account info will have to be added below.','wprentals-core').'</p>';
        
        
        $cache_array                =   array('no','yes');
        $sms_verification_symbol    =   wpestate_dropdowns_theme_admin($cache_array,'sms_verification');
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Enable SMS service','wprentals-core').'</div>
            <div class="option_row_explain">'.__('Enable SMS service.','wprentals-core').'</div>    
                <select id="sms_verification" name="sms_verification">
                    '.$sms_verification_symbol.'
                </select>
        </div>';
        

       
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__(' Twilio phone number','wprentals-core').'</div>
            <div class="option_row_explain">'.__(' Twilio phone number(ex +1256973878)','wprentals-core').'</div>    
                <input type="text" id="twilio_phone_no" name="twilio_phone_no" value="';
                if (isset( $sms_data['twilio_phone_no']) ){
                    print $sms_data['twilio_phone_no'];
                }
            print '"/>
            </div>';
        
        
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Twilio Account Sid','wprentals-core').'</div>
            <div class="option_row_explain">'.__('Twilio Account Sid','wprentals-core').'</div>    
                <input type="text" id="twilio_api_key" name="twilio_api_key" value="';
                if (isset( $sms_data['twilio_api_key']) ){
                    print $sms_data['twilio_api_key'];
                }
                print '"/>
            </div>';
        
       
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Twilio Auth Token','wprentals-core').'</div>
            <div class="option_row_explain">'.__('Twilio Auth Token','wprentals-core').'</div>    
                <input type="text" id="twilio_auth_token" name="twilio_auth_token" value="';
                if (isset( $sms_data['twilio_auth_token']) ){
                    print $sms_data['twilio_auth_token'];
                }
                print '"/>
            </div>';
         
        
          print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Global variables: %website_url as website url,%website_name as website name, %user_email as user_email, %username as username','wprentals-core').'</div>
            </div>';
        
        foreach ($sms_array as $key=>$label ){

            print '<div class="estate_option_row">';
            $value          = stripslashes( wprentals_get_option('wp_estate_'.$key,'') );
            $value_subject  = stripslashes( wprentals_get_option('wp_estate_subject_'.$key,'') );
            
         
            
            
            print '<input type="checkbox" class="admin_checker" name="use_sms['.$key.']" ';
            if( isset($sms_data['use_sms'][$key]) && $sms_data['use_sms'][$key]==1 ){
               print ' checked ';
            }
            print ' value="1"></input>';
            
            
            
            print '<label class="label_option_row"  for="use_sms_'.$key.'">'.__('Send this SMS','wprentals-core').'</label></br></br>';
                 
            print '<label class="label_option_row"  for="'.$key.'">'.__('SMS for','wprentals-core').' '.$label.'</label>';
            print '<div class="option_row_explain">'.__('SMS text for','wprentals-core').' '.$label.'</div>    ';

            $sms_content='';
            
            if(isset($sms_data['sms_content'][$key])){
                $sms_content = stripslashes($sms_data['sms_content'][$key]);
            }
            print '<textarea rows="10" style="width:100%;" name="sms_content['.$key.']">'.$sms_content.'</textarea>';
            print '<div class="extra_exp"> '.wpestate_emails_extra_details($key).'</div>';
            print '</div>';

        }

        print'<p class="submit" style="margin-left:230px;">
             <input type="submit" name="submit"  class="new_admin_submit " value="' . __('Save Changes', 'wprentals-core') . '" />
            </p>';

       
    }
endif;