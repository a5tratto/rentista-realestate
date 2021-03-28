<?php
global $post;
global $wpestate_where_currency;
global $wpestate_currency;
global $userID;
global $user_login;

$link               =   esc_url (get_permalink());
$booking_status     =   get_post_meta($post->ID, 'booking_status', true);
$booking_status_full=   get_post_meta($post->ID, 'booking_status_full', true);
$booking_id         =   get_post_meta($post->ID, 'booking_id', true);
$booking_from_date  =   get_post_meta($post->ID, 'booking_from_date', true);
$booking_to_date    =   get_post_meta($post->ID, 'booking_to_date', true);
$booking_guests     =   get_post_meta($post->ID, 'booking_guests', true);
$preview            =   wp_get_attachment_image_src(get_post_thumbnail_id($booking_id), 'wpestate_blog_unit');
$author             =   get_the_author();
$invoice_no         =   get_post_meta($post->ID, 'booking_invoice_no', true);
$booking_pay        =   get_post_meta($post->ID, 'booking_pay_ammount', true);
$booking_company    =   get_post_meta($post->ID, 'booking_company', true);

if ( $booking_status=='confirmed'){
    $total_price        =   floatval( get_post_meta($post->ID, 'total_price', true) );
    $to_be_paid         =   floatval( get_post_meta($post->ID, 'to_be_paid', true) );
    $to_be_paid         =   $total_price-$to_be_paid;
    $to_be_paid_show    =   wpestate_show_price_booking ( $to_be_paid ,$wpestate_currency,$wpestate_where_currency,1);
}else{
    $to_be_paid         =   floatval( get_post_meta($post->ID, 'total_price', true) );
    $to_be_paid_show    =   wpestate_show_price_booking ( $to_be_paid ,$wpestate_currency,$wpestate_where_currency,1);
}

$no_of_days         =   ( strtotime($booking_to_date)-strtotime($booking_from_date) ) / (60*60*24);
$property_price     =   get_post_meta($booking_id, 'property_price', true);
$price_per_option   =   intval(get_post_meta($booking_id, 'price_per', true));
if ($price_per_option!=0){
    $property_price     =   round ( $property_price/$price_per_option,2);
}
$price_per_booking  =   $no_of_days *$property_price;
$event_description  =   get_the_content();   

if($invoice_no== 0){
    $invoice_no='-';
}else{
    $price_per_booking=get_post_meta($invoice_no, 'item_price', true);
}
$price_per_booking=floatval($price_per_booking);
$price_per_booking = number_format($price_per_booking,2,'.',',');

if ($wpestate_where_currency == 'before') {
    $price_per_booking = $wpestate_currency . ' ' . $price_per_booking;
} else {
    $price_per_booking = $price_per_booking . ' ' . $wpestate_currency;
}
?>

<div class="col-md-12 ">
    <div class="dasboard-prop-listing ">
        <div class="blog_listing_image book_image">
           <a href="<?php print esc_url ( get_permalink($booking_id) );?>"> 
            <?php if (has_post_thumbnail($booking_id)){?>
            <img  src="<?php  print esc_url($preview[0]); ?>"  class="img-responsive" alt="<?php esc_html_e('image','wprentals');?>" />
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
                print ' <strong>'. esc_html__( 'for','wprentals').'</strong> <a href="'.esc_url ( get_permalink($booking_id)).'">'.get_the_title($booking_id).'</a>'; 
                ?>      
            </h4>

            <div class="user_dashboard_listed">
                <strong><?php esc_html_e('Period: ','wprentals');?>   </strong>  <?php print wpestate_convert_dateformat_reverse($booking_from_date).' <strong>'.esc_html__( 'to','wprentals').'</strong> '.wpestate_convert_dateformat_reverse($booking_to_date); ?>
            </div>

            <div class="user_dashboard_listed">
                <strong><?php esc_html_e('Invoice No: ','wprentals');?></strong> <span class="invoice_list_id"><?php print esc_html($invoice_no);?></span>   
            </div>

            <div class="user_dashboard_listed">
                <strong><?php esc_html_e('Guests: ','wprentals');?> </strong> <?php print esc_html($booking_guests); ?>  
            </div>    

            <?php  if($to_be_paid>0 && $booking_status_full!='confirmed') { ?>
                <div class="user_dashboard_listed" style="color:red;">
                   <strong><?php esc_html_e('Balance: ','wprentals');?> </strong> <?php print esc_html($to_be_paid_show).' '.__('to be paid until ','wprentals').' '.wpestate_convert_dateformat_reverse($booking_from_date); ?>  
                </div> 
            <?php } ?>
            
            <?php 
            if($event_description!=''){
                print ' <div class="user_dashboard_listed event_desc"><strong>'.esc_html__( 'Comment: ','wprentals').'</strong>'.esc_html($event_description).'</div>';
            }
            ?>
        </div>

        <div class="info-container_booking">
            <?php
   
            if ($booking_status=='confirmed'){
                if($booking_status_full=='confirmed'){
                    print '<span class="tag-published">'.esc_html__( 'Confirmed & Paid','wprentals').'</span>';
                }else{
                    print '<span class="tag-published">'.esc_html__( 'Confirmed','wprentals').'</span>';
                    print '<span class="proceed-payment_full" data-invoiceid="'.esc_attr($invoice_no).'" data-bookid="'.esc_attr($post->ID).'">'.esc_html__( 'Pay Invoice in Full','wprentals').'</span>';                  
              
                }
                
                print '<span class="tag-published confirmed_booking" data-invoice-confirmed="'.esc_attr($invoice_no).'" data-booking-confirmed="'.esc_attr($post->ID).'">'.esc_html__( 'View Details','wprentals').'</span>';       
                                
                if(strtotime($booking_to_date) < time() ){
                    if ( get_post_meta($booking_id,'review_by_'.$userID,true) != 'has' ){
                        print '<span class="tag-post-review post_review" data-bookid="'.esc_attr($post->ID).'" data-listing-review="'.esc_attr($booking_id).'">'.esc_html__( 'Post Review','wprentals').'</span>';
                    }else{
                        print '<span class="tag-published">'.esc_html__( 'You already reviewed this property!','wprentals').'</span>';
                    }
                }else{
                    print '<span class="tag-published">'.esc_html__( 'You can post the review after the trip!','wprentals').'</span>'; 
                }
                        
            }else if( $booking_status=='waiting'){
                print '<span class="proceed-payment" data-invoiceid="'.esc_attr($invoice_no).'" data-bookid="'.esc_attr($post->ID).'">'.esc_html__( 'Invoice Created - Check & Pay','wprentals').'</span>';                  
                print '<span class="delete_booking usercancel" data-bookid="'.esc_attr($post->ID).'">'.esc_html__( 'Cancel Booking Request','wprentals').'</span>';              
            }else{
                print '<span class="waiting_payment_user" data-bookid="'.esc_attr($post->ID).'">'.esc_html__( 'Request Pending','wprentals').'</span>';            
                print '<span class="delete_booking usercancel" data-bookid="'.esc_attr($post->ID).'">'.esc_html__( 'Cancel Booking Request','wprentals').'</span>';  
            } 
            print '<span class="contact_owner_reservation" data-bookid="'.esc_attr($booking_id).'">'.esc_html__( 'Contact Owner','wprentals').'</span>';  
            ?>
        </div>
    </div>
</div>