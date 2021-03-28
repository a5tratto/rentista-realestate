<?php
global $post;
global $wpestate_where_currency;
global $wpestate_currency;
global $user_login;

$link               =   esc_url(get_permalink());
$booking_status     =   get_post_meta($post->ID, 'booking_status', true);
$booking_status_full=   get_post_meta($post->ID, 'booking_status_full', true);
$booking_id         =   get_post_meta($post->ID, 'booking_id', true);
$booking_from_date  =   get_post_meta($post->ID, 'booking_from_date', true);
$booking_to_date    =   get_post_meta($post->ID, 'booking_to_date', true);
$booking_guests     =   get_post_meta($post->ID, 'booking_guests', true);
$preview            =   wp_get_attachment_image_src(get_post_thumbnail_id($booking_id), 'wpestate_blog_unit');
$author             =   get_the_author();
$author_id          =   get_the_author_meta('ID');
$userid_agent       =   get_user_meta($author_id, 'user_agent_id', true);
$invoice_no         =   get_post_meta($post->ID, 'booking_invoice_no', true);
$booking_array      =   wpestate_booking_price($booking_guests,$invoice_no,$booking_id, $booking_from_date, $booking_to_date);     
$invoice_no         =   get_post_meta($post->ID, 'booking_invoice_no', true);
$booking_pay        =   $booking_array['total_price'];
$booking_company    =   get_post_meta($post->ID, 'booking_company', true);
$no_of_days         =   $booking_array['numberDays'];
$property_price     =   $booking_array['default_price'];
$event_description  =   get_the_content();   

if ( $booking_status=='confirmed'){
    $total_price        =   floatval( get_post_meta($post->ID, 'total_price', true) );
    $to_be_paid         =   floatval( get_post_meta($post->ID, 'to_be_paid', true) );
    $to_be_paid         =   $total_price-$to_be_paid;
    $to_be_paid_show    =   wpestate_show_price_booking ( $to_be_paid ,$wpestate_currency,$wpestate_where_currency,1);
}else{
    $to_be_paid         =   floatval( get_post_meta($post->ID, 'total_price', true) );
    $to_be_paid_show    =   wpestate_show_price_booking ( $to_be_paid ,$wpestate_currency,$wpestate_where_currency,1);
}

if($invoice_no== 0){
    $invoice_no='-';
}
$price_per_booking         =   wpestate_show_price_booking($booking_array['total_price'],$wpestate_currency,$wpestate_where_currency,1);            

?>


<div class="col-md-12 ">
    <div class="dasboard-prop-listing">
    
   <div class="blog_listing_image book_image">
        <a href="<?php print esc_url ( get_permalink($booking_id) );?>"> 
            <?php if (has_post_thumbnail($booking_id)){?>
            <img  src="<?php  print esc_url($preview[0]); ?>"  class="img-responsive" alt="<?php esc_html_e('image','wprentals');?>"/>
            <?php           
            }else{
                $thumb_prop_default =  get_stylesheet_directory_uri().'/img/defaultimage_prop.jpg';
                ?>
                <img  src="<?php  print esc_url($thumb_prop_default); ?>"  class="img-responsive" alt="<?php esc_html_e('image','wprentals');?>" />
            <?php }?>
        </a>
   </div>
    

    <div class="prop-info">
        <h4 class="listing_title_book">
            <?php 
         
            echo esc_html__('Booking request','wprentals').' '.$post->ID;
            print ' <strong>'. esc_html__( 'for','wprentals').'</strong> <a href="'.esc_url (get_permalink($booking_id) ).'">'.get_the_title($booking_id).'</a>'; 
            ?>      
        </h4>
        
        
        
        <div class="user_dashboard_listed">
            <span class="booking_details_title">  <?php esc_html_e('Request by ','wprentals');?></span>
                <?php if(intval($userid_agent)!=0) {
                    print '<a href="'.esc_url ( get_permalink($userid_agent) ).'" target="_blank" > '. esc_html($author).' </a>';
                }else{
                    print esc_html($author);
                }
                ?>
        </div>
        <?php
        $booking_from_date  =  wpestate_convert_dateformat_reverse($booking_from_date);
        $booking_to_date    =  wpestate_convert_dateformat_reverse($booking_to_date);
        ?>
        <div class="user_dashboard_listed">
            <span class="booking_details_title"><?php esc_html_e('Period: ','wprentals');?>   </span>  <?php print esc_html($booking_from_date).' <strong>'.esc_html__( 'to','wprentals').'</strong> '.esc_html($booking_to_date); ?>
        </div>
        
        <?php if( $author!= $user_login ) { ?>
            <div class="user_dashboard_listed">
                <span class="booking_details_title"><?php esc_html_e('Invoice No: ','wprentals');?></span> <span class="invoice_list_id"><?php print esc_html($invoice_no);?></span>   
            </div>
            
            <div class="user_dashboard_listed">
                <span class="booking_details_title"><?php esc_html_e('Pay Amount: ','wprentals');?> </span> <?php print wpestate_show_price_booking ( floatval( get_post_meta($invoice_no, 'item_price', true)) ,$wpestate_currency,$wpestate_where_currency,1); ?>  
                <span class="booking_details_title guest_details"><?php esc_html_e('Guests: ','wprentals');?> </span> <?php print esc_html($booking_guests); ?>  
            </div>
            
            <?php
            if($to_be_paid>0 && $booking_status_full!='confirmed') { ?>
                <div class="user_dashboard_listed" style="color:red;">
                   <strong><?php esc_html_e('Balance: ','wprentals');?> </strong> <?php print esc_html($to_be_paid_show).' '.__('to be paid until ','wprentals').' '.esc_html($booking_from_date); ?>  
                   <div class="full_invoice_reminder" data-invoiceid="<?php print esc_attr($invoice_no); ?>" data-bookid="<?php print esc_attr($post->ID);?>"><?php esc_html_e('Send reminder email!','wprentals');?></div>
                </div> 
            <?php } ?>
        
            <div class="user_dashboard_listed">
            </div>  
        <?php } 
        
        if($event_description!=''){
            print ' <div class="user_dashboard_listed event_desc"> <span class="booking_details_title">'.esc_html__( 'Reservation made by owner','wprentals').'</span></div>';
            print ' <div class="user_dashboard_listed event_desc"> <span class="booking_details_title">'.esc_html__( 'Comments: ','wprentals').'</span>'.esc_html($event_description).'</div>';
        }
        ?>                
    </div>

    
    <div class="info-container_booking">
        <?php 
        if ($booking_status=='confirmed'){
            if($booking_status_full=="confirmed"){
               print '<span class="tag-published">'.esc_html__( 'Confirmed & Fully Paid','wprentals').'</span>';
            }else{
                print '<span class="tag-published">'.esc_html__( 'Confirmed / Not Fully Paid','wprentals').'</span>';
            }
            if( $author!= $user_login ){
                print '<span class="tag-published confirmed_booking" data-invoice-confirmed="'.esc_attr($invoice_no).'" data-booking-confirmed="'.esc_attr($post->ID).'">'.esc_html__( 'View Details','wprentals').'</span>';
                print '<span class="cancel_user_booking" data-listing-id="'.esc_attr($booking_id).'"  data-booking-confirmed="'.esc_attr($post->ID).'">'.esc_html__( 'Cancel booking','wprentals').'</span>';
            
                
            }else{
                print '<span class="cancel_own_booking" data-listing-id="'.esc_attr($booking_id).'"  data-booking-confirmed="'.esc_attr($post->ID).'">'.esc_html__( 'Cancel my own booking','wprentals').'</span>';
            
            }
      
        }else if( $booking_status=='waiting'){
            print '<span class="waiting_payment" data-bookid="'.esc_attr($post->ID).'">'.esc_html__( 'Invoice Issued ','wprentals').'</span>';             
            print '<span class="delete_invoice" data-invoiceid="'.esc_attr($invoice_no).'" data-bookid="'.esc_attR($post->ID).'">'.esc_html__( 'Delete Invoice','wprentals').'</span>';
            print '<span class="delete_booking" data-bookid="'.esc_attr($post->ID).'">'.esc_html__( 'Reject Booking Request','wprentals').'</span>';    
        }else{
            print '<span class="generate_invoice" data-bookid="'.esc_attr($post->ID).'">'.esc_html__( 'Issue invoice','wprentals').'</span>';  
            print '<span class="delete_booking" data-bookid="'.esc_attr($post->ID).'">'.esc_html__( 'Reject Booking Request','wprentals').'</span>';    
        } 
        ?>
    </div>
   </div> 
 </div>