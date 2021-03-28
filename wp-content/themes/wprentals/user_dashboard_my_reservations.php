<?php
// Template Name: User Dashboard My Reservations
// Wp Estate Pack
if ( !is_user_logged_in() ) {   
    wp_redirect( esc_url(home_url('/')) );exit();
} 

global $user_login;
$current_user = wp_get_current_user();
$userID                         =   $current_user->ID;
$user_login                     =   $current_user->user_login;
$user_pack                      =   get_the_author_meta( 'package_id' , $userID );
$user_registered                =   get_the_author_meta( 'user_registered' , $userID );
$user_package_activation        =   get_the_author_meta( 'package_activation' , $userID );   
$paid_submission_status         =   esc_html ( wprentals_get_option('wp_estate_paid_submission','') );
$price_submission               =   floatval( wprentals_get_option('wp_estate_price_submission','') );
$submission_curency_status      =   wpestate_curency_submission_pick();
$edit_link                      =   wpestate_get_template_link('user_dashboard_edit_listing.php');
$processor_link                 =   wpestate_get_template_link('processor.php');
$wpestate_where_currency        =   esc_html( wprentals_get_option('wp_estate_where_currency_symbol', '') );
$wpestate_currency              =   wpestate_curency_submission_pick();
$wpestate_options               =   wpestate_page_details($post->ID);
get_header();

$title_search='';
 $new_mess=0;
if( isset($_POST['wpestate_prop_title']) ){
    if (  ! isset( $_POST['wpestate_dash_rez_search_nonce'] )  || ! wp_verify_nonce( $_POST['wpestate_dash_rez_search_nonce'], 'wpestate_dash_rez_search' ) ) {
        esc_html_e('your nonce does not validated','wprentals');
        exit();
    }
    $title=sanitize_text_field($_POST['wpestate_prop_title']);
    

    $args = array(
                    'post_type'         => 'estate_property',
                    'posts_per_page'    => -1,
                    's'                 =>  $title
                 );
    $new_mess=1;
    
    if(function_exists('wpestate_search_by_title_only_filter')){
        $prop_selection =   wpestate_search_by_title_only_filter($args);
    }

    $right_array=array();
    $right_array[]=0;

    while ($prop_selection->have_posts()): $prop_selection->the_post(); 
            $right_array[]=$post->ID;
           // print get_the_title($post->ID).',';
    endwhile;
    wp_reset_postdata();
    $title_search= array(
                array(
                    'key'     => 'booking_id',
                    'value'   => $right_array,
                    'compare' => 'IN',
                    ),
                );
}
?>

<div class="row is_dashboard">
    <?php
    if( wpestate_check_if_admin_page($post->ID) ){
        if ( is_user_logged_in() ) {   
            include(locate_template('templates/user_menu.php' ) ); 
        }  
    }
    ?> 
    
    <div class="dashboard-margin">
        <div class="dashboard-header">
            <?php if (esc_html( get_post_meta($post->ID, 'page_show_title', true) ) != 'no') { ?>
                <h1 class="entry-title listings-title-dash"><?php the_title(); ?></h1>
            <?php } ?>
                
            <div class="back_to_home">
                <a href="<?php echo esc_url( home_url('/') );?>" title="home url"><?php esc_html_e('Front page','wprentals');?></a>  
            </div> 
        </div>    
        
        <div class="search_dashborad_header">
            <form method="post" action="<?php echo wpestate_get_template_link('user_dashboard_my_reservations.php');?>">
            <?php wp_nonce_field( 'wpestate_dash_rez_search', 'wpestate_dash_rez_search_nonce' ); ?>
            <div class="col-md-4">
                <input type="text" id="title" class="form-control" value="" size="20" name="wpestate_prop_title" placeholder="<?php esc_html_e('Search by listing name.','wprentals');?>">
            </div>
            <div class="col-md-6">
                <input type="submit" class="wpb_btn-info wpb_btn-small wpestate_vc_button  vc_button" value="<?php esc_html_e('Search','wprentals');?>">
            </div>
            </form>    
        </div>    
        
        <div class="row admin-list-wrapper booking_list">   
        <?php
        wp_reset_query();
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $args = array(
                'post_type'         => 'wpestate_booking',
                'post_status'       => 'publish',
                'paged'             => $paged,
                'posts_per_page'    => 30,
                'order'             => 'DESC',
                'author'            =>  $userID    
             );
            
           if ($title_search!=''){
               $args['meta_query']=$title_search;
           }
           
            
            $book_selection = new WP_Query($args);
            if ( $book_selection->have_posts() ){
                while ($book_selection->have_posts()): $book_selection->the_post(); 
                    $booking_id    =    get_post_meta($post->ID, 'booking_id', true);
                    $listing_owner =    wpsestate_get_author($booking_id);
                    if ( $userID != $listing_owner){
                        include(locate_template('templates/book-listing-user-unit.php' ) );  
                    }
                endwhile;
                wprentals_pagination($book_selection->max_num_pages, $range =2);
            }else{
             
                if($new_mess==1){
                    print '<h4 class="no_favorites">'.esc_html__( 'No results!','wprentals').'</h4>';
                }else{
                    print '<h4 class="no_favorites">'.esc_html__( 'You don\'t have any reservations made!','wprentals').'</h4>';
                }
      
            } 
            wp_reset_query();
        ?>
        </div>
    </div>
</div>  

<?php 
$ajax_nonce = wp_create_nonce( "wprentals_reservation_actions_nonce" );
print'<input type="hidden" id="wprentals_reservation_actions" value="'.esc_html($ajax_nonce).'" />    ';

$ajax_nonce_book = wp_create_nonce( "wprentals_booking_confirmed_actions_nonce" );
print'<input type="hidden" id="wprentals_booking_confirmed_actions" value="'.esc_html($ajax_nonce_book).'" />    ';
  
wp_reset_query();
get_footer(); 
?>