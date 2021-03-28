<?php
global $edit_link;
global $token;
global $processor_link;
global $paid_submission_status;
global $submission_curency_status;
global $price_submission;
global $floor_link;
global $show_remove_fav;
global $wpestate_curent_fav;
global $th_separator;
global $user_pack;
global $userID;
$extra= array(
        'class'         =>  'lazyload img-responsive',    
        );

$post_id                    =   get_the_ID();
$preview                    =   wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'wpestate_property_listings',$extra);
$edit_link                  =   esc_url_raw ( add_query_arg( 'listing_edit', $post_id, $edit_link) ) ;
$edit_link                  =   esc_url_raw ( add_query_arg( 'action', 'description', $edit_link) ) ;               
$floor_link                 =   esc_url_raw ( add_query_arg( 'floor_edit', esc_url($post_id), $floor_link) ) ;
$post_status                =   get_post_status($post_id);
$property_address           =   esc_html ( get_post_meta($post_id, 'property_address', true) );
$property_city              =   get_the_term_list($post_id, 'property_city', '', ', ', '') ;
$property_category          =   get_the_term_list($post_id, 'property_category', '', ', ', '');
$property_action_category   =   get_the_term_list($post_id, 'property_action_category', '', ', ', '');
$price_label                =   esc_html ( get_post_meta($post_id, 'property_label', true) );
$price                      =   intval( get_post_meta($post->ID, 'property_price', true) );
$wpestate_currency          =   wpestate_curency_submission_pick();
$currency_title             =   esc_html( wprentals_get_option('wp_estate_currency_label_main', '') );
$wpestate_where_currency    =   esc_html( wprentals_get_option('wp_estate_where_currency_symbol', '') );
$status                     =   '';
$link                       =   '';
$pay_status                 =   '';
$is_pay_status              =   '';
$paid_submission_status     =   esc_html ( wprentals_get_option('wp_estate_paid_submission','') );
$price_submission           =   floatval( wprentals_get_option('wp_estate_price_submission','') );
$price_featured_submission  =   floatval( wprentals_get_option('wp_estate_price_featured_submission','') );

if ($price != 0) {
    
   $price =   number_format($price,0,'.',$th_separator);
 
   if ($wpestate_where_currency == 'before') {
       $price_title =   $currency_title . ' ' . $price;
       $price       =   $wpestate_currency . ' ' . $price;
   } else {
       $price_title = $price . ' ' . $currency_title;
       $price       = $price . ' ' . $wpestate_currency;
     
   }
}else{
    $price='';
    $price_title='';
}

$fav_mes        =   esc_html__( 'add to favorites','wprentals');
if($wpestate_curent_fav){
    if ( in_array ($post->ID,$wpestate_curent_fav) ){
    $favorite_class =   'icon-fav-on';   
    $fav_mes        =   esc_html__( 'remove from favorites','wprentals');
    } 
}

if($post_status=='expired'){ 
    $status='<span class="label label-danger">'.esc_html__( 'Expired','wprentals').'</span>';
}else if($post_status=='publish'){ 
    $link= esc_url ( get_permalink() );
    $status='<span class="label label-success">'.esc_html__( 'Published','wprentals').'</span>';
}else if($post_status=='disabled'){ 
    $link= '';
    $status='<span class="label label-disabled">'.esc_html__( 'Disabled','wprentals').'</span>';
}else{
    $link='';
    $status='<span class="label label-info">'.esc_html__( 'Waiting for approval','wprentals').'</span>';
}


if ($paid_submission_status=='per listing'){
    $pay_status    = get_post_meta(get_the_ID(), 'pay_status', true);
    if($pay_status=='paid'){
        $is_pay_status.='<span class="label label-success">'.esc_html__( 'Paid','wprentals').'</span>';
    }
    if($pay_status=='not paid'){
        $is_pay_status.='<span class="label label-info">'.esc_html__( 'Not Paid','wprentals').'</span>';
    }
}
$featured  = intval  ( get_post_meta($post->ID, 'prop_featured', true) );

$free_feat_list_expiration  =   intval ( wprentals_get_option('wp_estate_free_feat_list_expiration','') );
$pfx_date                   =   strtotime ( get_the_date("Y-m-d",  $post->ID ) );
$expiration_date            =   $pfx_date+$free_feat_list_expiration*24*60*60;
$user_pack                  =   get_the_author_meta( 'package_id' , $userID );
$favorite_pay_details       =   '';

if( is_page_template( 'user_dashboard_favorite.php' ) ){
    $favorite_pay_details='favorite pay_details';
}
?>

<div class="col-md-4 flexdashbaord">
    <div class="dasboard-prop-listing">
    
        <div class="blog_listing_image dashboard_imagine">
            <?php
            if($featured==1){
                print '<span class="label label-primary featured_div">'.esc_html__( 'featured','wprentals').'</span>';
            }
            if (has_post_thumbnail($post_id)){
            ?>
            <a href="<?php print esc_url($link); ?>"><img src="<?php  print esc_url($preview[0]); ?>" class="b-lazy img-responsive " alt="<?php esc_html_e('image','wprentals');?>" /></a>
            
            <?php 
            } else{ 
                $thumb_prop_default =  get_stylesheet_directory_uri().'/img/defaultimage_prop.jpg';?>
                <img src="<?php print esc_url($thumb_prop_default);?>"   class="b-lazy img-responsive wp-post-image " alt="<?php esc_html_e('image','wprentals');?>" />         
            <?php    
            }
            ?>
        </div>
        
        <div class="user_dashboard_status">
            <?php print trim($status.$is_pay_status);?>      
        </div>

         <div class="prop-info">         
            <h4 class="listing_title">
                <a href="<?php print esc_url($link); ?>">
                <?php
             
                $title=get_the_title();
                echo mb_substr( html_entity_decode( $title ), 0, 20); 
                if(strlen($title)>20){
                    echo '...';   
                }
                ?>
                </a> 
            </h4>

            <div class="user_dashboard_listed <?php print esc_attr($favorite_pay_details); ?>">
                <?php                  
                    if ( $paid_submission_status=='membership' && $user_pack=='') {
                        if($price_title!=''){
                            print esc_html__( 'Price','wprentals').': <span class="price_label"> '. esc_html($price_title).' '.esc_html($price_label).'</span> | ' ;
                        }
                        print esc_html__('expires on','wprentals').' '.date("Y-m-d",$expiration_date);
                    }
                ?>
            </div>

            <div class="user_dashboard_listed">
                 <?php esc_html_e('Listed in','wprentals');?>  
                 <?php print trim($property_action_category); ?> 
                 <?php if( $property_action_category!='') {
                         print' '.esc_html__( 'and','wprentals').' ';
                         } 
                       print trim($property_category);?>                     
            </div>

            <div class="user_dashboard_listed">
                 <?php print esc_html__( 'City','wprentals').': ';?>            
                 <?php print get_the_term_list($post_id, 'property_city', '', ', ', '');?>
                 <?php print ', '.esc_html__( 'Area','wprentals').': '?>
                 <?php print get_the_term_list($post_id, 'property_area', '', ', ', '');?>          
            </div>

            <?php 
            if ( isset($show_remove_fav) && $show_remove_fav==1 ) {
                print '<div class="info-container-payments favorite-wrapper"><span class="icon-fav icon-fav-on-remove" data-postid="'.esc_attr($post->ID).'"> '.esc_html($fav_mes).'</span></div>';
            } else{ 
            ?>
         
             
                <div class="info-container">
                    <a  data-original-title="<?php esc_attr_e('Edit property','wprentals');?>"   class="dashboad-tooltip" href="<?php  print esc_url($edit_link);?>"><i class="fas fa-pencil-alt editprop"></i></a>
                    <a  data-original-title="<?php esc_attr_e('Delete property','wprentals');?>" class="dashboad-tooltip" onclick="return confirm(' <?php echo esc_html__( 'Are you sure you wish to delete ','wprentals').get_the_title(); ?>?')" href="<?php print esc_url ( add_query_arg( 'delete_id', $post_id, wpestate_get_template_link('user_dashboard.php') ) );?>"><i class="fas fa-times deleteprop"></i></a>  
                    <?php
                    if( $post_status == 'expired' ){ 
                        print'<span data-original-title="'.esc_attr__( 'Resend for approval','wprentals').'" class="dashboad-tooltip resend_pending" data-listingid="'.esc_attr($post_id).'"><i class="fas fa-arrow-up"></i></span>';   
                    }
                    
                    if($paid_submission_status=='per listing'){
                        $pay_status    = get_post_meta($post_id, 'pay_status', true);
                        $featured= intval(get_post_meta($post_id, 'prop_featured', true));
                        if($pay_status=='paid' && $featured==1){
                            //nothing
                        }else{
                            print '<span class="activate_payments">'.esc_html__( 'Publish or Upgrade','wprentals').'</span>';
                        }
                    }
                    
                    if( $post_status == 'publish' ){ 
                        print ' <span  data-original-title="'.esc_attr__( 'Disable Listing','wprentals').'" class="dashboad-tooltip disable_listing" data-postid="'.esc_attr($post_id).'" ><i class="fas fa-pause"></i></span>';
                    }else if($post_status=='disabled') {
                        print ' <span  data-original-title="'.esc_attr__( 'Enable Listing','wprentals').'" class="dashboad-tooltip disable_listing" data-postid="'.esc_attr($post_id).'" ><i class="fas fa-play"></i></span>';
                    }
                    
                    if($paid_submission_status=='membership'){
                        if ( intval(get_post_meta($post_id, 'prop_featured', true))==1){
                            print '<span class="label label-success is_featured">'.esc_html__( 'Property is featured','wprentals').'</span>';       
                        }
                        else{
                            print ' <span  data-original-title="'.esc_attr__( 'Set as featured','wprentals').'" class="dashboad-tooltip make_featured" data-postid="'.esc_attr($post_id).'" ><i class="fas fa-star favprop"></i></span>';
                        }
                    }
                                
                    ?>
                
                </div>
             
                <div class="info-container-payments"> 
                    <?php $pay_status    = get_post_meta($post_id, 'pay_status', true);
                    
               
                            
                        if( $post_status == 'expired' ){ 
                        }else{

                            if($paid_submission_status=='per listing'){
                              
                           
                                    $enable_paypal_status   =   esc_html ( wprentals_get_option('wp_estate_enable_paypal','') );
                                    $enable_stripe_status   =   esc_html ( wprentals_get_option('wp_estate_enable_stripe','') );
                                    $enable_direct_pay      =   esc_html ( wprentals_get_option('wp_estate_enable_direct_pay','') );
                                    if($pay_status!='paid' ){
                                        
                                        print '<div class="listing_submit">
                                        <button type="button"  class="close close_payments" data-dismiss="modal" aria-hidden="true">x</button>
                                        '.esc_html__( 'Submission Fee','wprentals').': <span class="submit-price submit-price-no">'.esc_html($price_submission).'</span><span class="submit-price"> '.esc_html($wpestate_currency).'</span></br>';

                                            global $wpestate_global_payments;
                                            if($wpestate_global_payments->is_woo=='yes'){
                                                    $wpestate_global_payments->show_button_pay($post_id,'','',$price_submission,2);
                                            }else{

                                                $stripe_class='';
                                                if($enable_paypal_status==='yes'){
                                                    $stripe_class=' stripe_paypal ';
                                                    print ' <div class="listing_submit_normal label label-danger" data-listingid="'.esc_attr($post_id).'">'.esc_html__( 'Pay with Paypal','wprentals').'</div>';
                                                }

                                                if($enable_stripe_status==='yes'){
                                                  wpestate_show_stripe_form_per_listing($stripe_class,$post_id,$price_submission,$price_featured_submission);
                                                }

                                                if($enable_direct_pay==='yes'){
                                                    print '<div data-listing="'.esc_attr($post_id).'" class="label label-danger perpack">'.__('Wire Transfer','wprentals').'</div>';
                                                }
                                            }

                                        print  '</div>'; 

                                    }else{
                                        print '<div class="listing_submit">
                                        <button type="button"  class="close close_payments" data-dismiss="modal" aria-hidden="true">x</button>';


                                        if ( $featured ==1 ){
                                            print ' <div class="listing_submit_spacer" style="height:118px;"><span class="label label-success  featured_label">'.esc_html__( 'Property is featured','wprentals').'</span>   </div>';  
                                        }else{
                                            print'
                                            <div class="listing_submit_spacer">
                                                '.esc_html__( 'Featured Fee','wprentals').': <span class="submit-price submit-price-featured">'.esc_html($price_featured_submission).'</span><span class="submit-price"> '.esc_html($wpestate_currency).'</span> </br>
                                            </div>';

                                            global $wpestate_global_payments;
                                            if($wpestate_global_payments->is_woo=='yes'){
                                                $wpestate_global_payments->show_button_pay($post_id,'','',$price_featured_submission,3);
                                            }else{
                                                $stripe_class='';
                                                if($enable_paypal_status==='yes'){
                                                    print'<span class="listing_upgrade label label-danger" data-listingid="'.esc_attr($post_id).'">'.esc_html__( 'Set as Featured','wprentals').'</span>'; 
                                                }
                                                if($enable_stripe_status==='yes'){
                                                    wpestate_show_stripe_form_upgrade($stripe_class,$post_id,$price_submission,$price_featured_submission);
                                                }
                                                if($enable_direct_pay==='yes'){
                                                    print '<div data-listing="'.intval($post_id).'" data-isupgrade="1"  class="label label-danger perpack">'.__('Set as Featured - Wire','wprentals').'</div>';
                                                }
                                            }
                                        } 
                                        print '</div>';
                                    }
                                
                            }

                        }?>

                </div>
            <?php 
            }
            ?>
        </div> 
    </div>
 </div>