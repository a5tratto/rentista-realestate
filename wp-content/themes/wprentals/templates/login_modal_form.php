<?php  
$type=0;
if( isset( $_POST['type'] ) ){
    $type   =   intval($_POST['type']);
}

$ispop  =   0;
global $post;
global $wpestate_social_login;
if(isset($post->ID)){       
    $wpestate_propid =   intval($post->ID);
}

$show_login     =   '';
$show_register  =   '';
$login_text=0;

if(isset ( $_POST['login_modal_type'] ) ){
    $login_text     =   intval($_POST['login_modal_type']);
}

if(wprentals_get_option('wp_estate_item_rental_type')!=1){
    $mesaj_big  =   esc_html__( 'Log in or Sign up to continue','wprentals');
}else{
    $mesaj_big  =   esc_html__( 'Log in or Sign up to continue','wprentals');
}
$sub_mesaj  =   esc_html__( 'Please fill in the log in or register forms','wprentals');
if($login_text==2){
    $mesaj_big  =   esc_html__( 'Please login!','wprentals');
    $sub_mesaj  =   esc_html__( 'You need to login in order to send a message','wprentals');
}else if($login_text==3){
    $mesaj_big  =   esc_html__( 'Please login!','wprentals');
    $sub_mesaj  =   esc_html__( 'You need to login in order to book a listing','wprentals');
}
$social_register_on  =   esc_html( wprentals_get_option('wp_estate_social_register_on','') );

print'
        <!-- Modal -->
        <div class="modal fade" id="loginmodal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"> 
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h2 class="modal-title_big" >'.esc_html($mesaj_big).'</h4>
                      <h4 class="modal-title" id="myModalLabel">'.esc_html($sub_mesaj).'</h4>
                    </div>

                <div class="modal-body">
                <div id="ajax_login_div" class="'.esc_html($show_login).'">
                    <div class="login_form" id="login-div">
                        <div class="loginalert" id="login_message_area" ></div>

                        <div class="loginrow">
                            <input type="text" class="form-control" name="log" id="login_user" placeholder="'.esc_html__( 'Username','wprentals').'" size="20" />
                        </div>

                        <div class="loginrow">
                            <input type="password" class="form-control" name="pwd" placeholder="'.esc_html__( 'Password','wprentals').'" id="login_pwd" size="20" />
                        </div>

                        <input type="hidden" name="loginpop" id="loginpop" value="'.esc_attr($ispop).'">    
                        <input type="hidden" id="security-login" name="security-login" value="'. estate_create_onetime_nonce( 'login_ajax_nonce' ).'">

                        <button id="wp-login-but" class="wpb_button  wpb_btn-info  wpb_regularsize   wpestate_vc_button  vc_button" data-mixval="'.esc_attr($wpestate_propid).'">'.esc_html__( 'Login','wprentals').'</button>
                            <div class="navigation_links">
                                <a href="#" id="reveal_register">'.esc_html__( 'Don\'t have an account?','wprentals').'</a> | 
                                <a href="#" id="forgot_password_mod">'.esc_html__( 'Forgot Password','wprentals').'</a>
                            </div>

                  </div><!-- end login div-->   ';
                        $facebook_status            = esc_html( wprentals_get_option('wp_estate_facebook_login','') ) ;
                        $google_status              = esc_html( wprentals_get_option('wp_estate_google_login','') );
                        $twiter_status              = esc_html( wprentals_get_option('wp_estate_twiter_login','') );
                        if($facebook_status=='yes' || $google_status=='yes' || $twiter_status=='yes' ){
                    
                            print '<div class="login-links" >';
                                if(class_exists('Wpestate_Social_Login')){
                                    print  trim($wpestate_social_login->display_form('',1));
                                }
                            print '</div> <!-- end login links-->'; 

                        }
                    print'
                    </div><!-- /.ajax_login_div -->

                    <div id="ajax_register_div" class="'.esc_attr($show_register).'">
                        '.do_shortcode('[register_form type=""][/register_form]').'
                        <div id="reveal_login"><a href="#">'.esc_html__( 'Already a member? Sign in!','wprentals').'</a></div> ';
                         
                            if($social_register_on=='yes'){
                                print'
                                <div class="login-links" >';
                                if(class_exists('Wpestate_Social_Login')){ 
                                    print trim($wpestate_social_login->display_form('register',1));
                                }    
                                print '</div> <!-- end login links--> ';
                            }
                    print'         
                    </div>

                    <div class="login_form" id="forgot-pass-div_mod">

                        <div class="loginalert" id="forgot_pass_area_shortcode"></div>
                        <div class="loginrow">
                                <input type="text" class="form-control forgot_email_mod" name="forgot_email" id="forgot_email_mod" placeholder="'.esc_html__( 'Enter Your Email Address','wprentals').'" size="20" />
                        </div>
                        '. wp_nonce_field( 'login_ajax_nonce_forgot_wd', 'security-login-forgot_wd',true).'  
                        <input type="hidden" id="postid" value="0">    
                        <button class="wpb_button  wpb_btn-info  wpb_regularsize wpestate_vc_button  vc_button" id="wp-forgot-but_mod" name="forgot" >'.esc_html__( 'Reset Password','wprentals').'</button>

                        <a href="#" id="return_login_mod">'.esc_html__( 'Return to Login','wprentals').'</a>

                    </div>

            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
      </div> <!-- /.login model -->';
?>