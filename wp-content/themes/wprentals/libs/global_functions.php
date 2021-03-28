<?php


add_action('wp_footer', 'wprentals_add_to_footer_code');
if (!function_exists('wprentals_add_to_footer_code')):
  function wprentals_add_to_footer_code(){

      // show contact owner form
      if( is_singular('estate_property') || is_singular('estate_agent') ||  basename(get_page_template()) == 'user_dashboard_my_reservations.php' ){
        wpestate_ajax_show_contact_owner_form();
      }

      // show login modal form
      if ( !is_user_logged_in() ) {
          include(locate_template('templates/login_modal_form.php'));
      }

      // ajax filterning nonce
      $ajax_nonce = wp_create_nonce( "wprentals_ajax_filtering_nonce" );
      print'<input type="hidden" id="wprentals_ajax_filtering" value="'.esc_html($ajax_nonce).'" />    ';

      //login register nonce
      $ajax_nonce_log_reg = wp_create_nonce( "wpestate_ajax_log_reg_nonce" );
      print'<input type="hidden" id="wpestate_ajax_log_reg" value="'.esc_html($ajax_nonce_log_reg).'" />    ';

  }
endif;


?>
