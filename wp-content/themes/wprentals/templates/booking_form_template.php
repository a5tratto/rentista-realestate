<?php 
global $post;
global $current_user;
global $feature_list_array;
global $wpestate_propid ;
global $post_attachments;
global $wpestate_options;
global $wpestate_where_currency;
global $wpestate_property_description_text;     
global $wpestate_property_details_text;
global $wpestate_property_details_text;
global $wpestate_property_adr_text;  
global $wpestate_property_price_text;   
global $wpestate_property_pictures_text;    
global $wpestate_propid;
global $wpestate_gmap_lat;  
global $wpestate_gmap_long;
global $wpestate_unit;
global $wpestate_currency;
global $wpestate_use_floor_plans;
global $favorite_text;
global $favorite_class;
global $property_action_terms_icon;
global $property_action;
global $property_category_terms_icon;
global $property_category;
global $guests;
global $bedrooms;
global $bathrooms;
global $show_sim_two;
global $guest_list;
global $post_id;
$rental_type        =   wprentals_get_option('wp_estate_item_rental_type','');
$booking_type       =   wprentals_return_booking_type($post->ID);
?>

<div  itemprop="price"  class="listing_main_image_price">
    <?php  
    $price_per_guest_from_one       =   floatval( get_post_meta($post->ID, 'price_per_guest_from_one', true) ); 
    $price                          =   floatval( get_post_meta($post->ID, 'property_price', true) );
    wpestate_show_price($post->ID,$wpestate_currency,$wpestate_where_currency,0); 
    print '<span class="pernight_label">';
    if($price!=0){
        if( $price_per_guest_from_one == 1){
            echo ' '.esc_html__( 'per guest','wprentals'); 
        }else{
            echo ' '.wpestate_show_labels('per_night',$rental_type,$booking_type); 
        }
    }
    print '</span>';
    ?>
</div>
      
<div class="booking_form_request" id="booking_form_request">
    <div id="booking_form_request_mess"></div>
    <h3><?php esc_html_e('Book Now','wprentals');?></h3>
             
    <div class="has_calendar calendar_icon">
        <input type="text" id="start_date" placeholder="<?php echo wpestate_show_labels('check_in',$rental_type,$booking_type); ?>"  class="form-control calendar_icon" size="40" name="start_date" 
                value="<?php if( isset($_GET['check_in_prop']) ){
                   echo sanitize_text_field ( $_GET['check_in_prop'] );
                }
                ?>">
    </div>

    <div class=" has_calendar calendar_icon">
        <input type="text" id="end_date" disabled placeholder="<?php echo wpestate_show_labels('check_out',$rental_type,$booking_type); ?>" class="form-control calendar_icon" size="40" name="end_date" 
               value="<?php if( isset($_GET['check_out_prop']) ){
                   echo sanitize_text_field ( $_GET['check_out_prop'] );
                }
                ?>">
    </div>

    <?php 
    $max_guest = get_post_meta($post_id,'guest_no',true);
    if($rental_type==0){ 
    ?>
        <div class=" has_calendar guest_icon ">
            <?php 
         
            print '
            <div class="dropdown form-control">
                <div data-toggle="dropdown" id="booking_guest_no_wrapper" class="filter_menu_trigger" data-value="';
                if(isset($_GET['guest_no_prop']) && $_GET['guest_no_prop']!=''){
                    echo esc_html( $_GET['guest_no_prop'] );
                }else{
                  echo esc_html__('all','wprentals');
                }
                print '">';
                print '<div class="text_selection">';
                if(isset($_GET['guest_no_prop']) && $_GET['guest_no_prop']!=''){
                    echo esc_html( $_GET['guest_no_prop'] ).' '.esc_html__( 'guests','wprentals');
                }else{
                    esc_html_e('Guests','wprentals');
                }
                print '</div>';

                print'<span class="caret caret_filter"></span>
                </div>           
                <input type="hidden" name="booking_guest_no"  value="">
                <ul  class="dropdown-menu filter_menu" role="menu" aria-labelledby="booking_guest_no_wrapper" id="booking_guest_no_wrapper_list">
                    '.$guest_list.'
                </ul>        
            </div>';
            ?> 
        </div>
    <?php 
    }else{
    ?>          
        <input type="hidden" name="booking_guest_no"  value="1">
    <?php
    }
    ?>
        
    <?php
    // shw extra options
    wpestate_show_extra_options_booking($post_id)
    ?>
            
    <p class="full_form " id="add_costs_here"></p>            
    <input type="hidden" id="listing_edit" name="listing_edit" value="<?php print intval($post_id);?>" />

    <?php wpestate_show_booking_button($post_id);?>
    
    <div class="third-form-wrapper">
        <div class="col-md-6 reservation_buttons">
            <div id="add_favorites" class=" <?php print esc_attr($favorite_class);?>" data-postid="<?php esc_attr(the_ID());?>">
                <?php print trim($favorite_text);?>
            </div>                 
        </div>

        <div class="col-md-6 reservation_buttons">
            <div id="contact_host" class="col-md-6"  data-postid="<?php esc_attr(the_ID());?>">
                <?php esc_html_e('Contact Owner','wprentals');?>
            </div>  
        </div>
    </div>

    <?php 
    echo wpestate_share_unit_desing($post_id);
    ?>

         
</div>