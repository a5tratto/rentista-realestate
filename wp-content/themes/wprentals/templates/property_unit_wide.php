<?php
global $wpestate_curent_fav;
global $wpestate_currency;
global $wpestate_where_currency;
global $show_compare;
global $wpestate_show_compare_only;
global $show_remove_fav;
global $wpestate_options;
global $isdashabord;
global $align;
global $align_class;
global $is_shortcode;
global $is_widget;
global $wpestate_row_number_col;
global $wpestate_full_page;
global $wpestate_listing_type;
global $wpestate_property_unit_slider;
global $wpestate_book_from;
global $wpestate_book_to;
global $wpestate_guest_no;
global $post;
$booking_type       =   wprentals_return_booking_type($post->ID);
$rental_type        =   wprentals_get_option('wp_estate_item_rental_type');

$pinterest          =   '';
$previe             =   '';
$compare            =   '';
$extra              =   '';
$property_size      =   '';
$property_bathrooms =   '';
$property_rooms     =   '';
$measure_sys        =   '';

$col_class  =   'col-md-6';
$col_org    =   4;
$title=get_the_title($post->ID);

if(isset($is_shortcode) && $is_shortcode==1 ){
    $col_class='col-md-'.esc_attr($wpestate_row_number_col).' shortcode-col';
}

if(isset($is_widget) && $is_widget==1 ){
    $col_class='col-md-12';
    $col_org    =   12;
}

if(isset($wpestate_full_page) && $wpestate_full_page==1 ){
    $col_class='col-md-4 ';
    $col_org    =   3;
}

$link           =   esc_url ( get_permalink());
$preview        =   array();
$preview[0]     =   '';
$favorite_class =   'icon-fav-off';
$fav_mes        =   esc_html__( 'add to favorites','wprentals');
if($wpestate_curent_fav){
    if ( in_array ($post->ID,$wpestate_curent_fav) ){
    $favorite_class =   'icon-fav-on';   
    $fav_mes        =   esc_html__( 'remove from favorites','wprentals');
    } 
}

$listing_type_class='property_unit_v2';
if($wpestate_listing_type==1){
    $listing_type_class='';
}

$link               =  esc_url ( get_permalink());
$wprentals_is_per_hour      =   wprentals_return_booking_type($post->ID);



if ( isset($_REQUEST['check_in']) && isset($_REQUEST['check_out']) ){
    $check_out  =   sanitize_text_field ( $_REQUEST['check_out'] );
    $check_in   =   sanitize_text_field ( $_REQUEST['check_in'] ); 
    if($wprentals_is_per_hour==2){
        $check_in=$check_in.' '.get_post_meta($post->ID, 'booking_start_hour', true);
        $check_out=$check_out.' '.get_post_meta($post->ID, 'booking_end_hour', true);
    }
    
    $link       =   add_query_arg( 'check_in_prop', (trim($check_in)), $link);
    $link       =   add_query_arg( 'check_out_prop',(trim($check_out)), $link);
    
   
    if(isset($_REQUEST['guest_no'])){
        $wpestate_guest_no   =   intval($_REQUEST['guest_no']);
        $link                =   add_query_arg( 'guest_no_prop', $wpestate_guest_no, $link);
    }
}else{
    if ($wpestate_book_from!='' && $wpestate_book_to!=''){
        $wpestate_book_from  =   sanitize_text_field ($wpestate_book_from);
        $wpestate_book_to    =   sanitize_text_field ( $wpestate_book_to );
        if($wprentals_is_per_hour==2){
            $wpestate_book_from=$wpestate_book_from.' '.get_post_meta($post->ID, 'booking_start_hour', true);
            $wpestate_book_to=$wpestate_book_to.' '.get_post_meta($post->ID, 'booking_end_hour', true);
        }
    
        $link       =   add_query_arg( 'check_in_prop', trim($wpestate_book_from), $link);
        $link       =   add_query_arg( 'check_out_prop', trim($wpestate_book_to), $link);
    
        if($wpestate_guest_no!=''){
            $link   =   add_query_arg( 'guest_no_prop', intval($wpestate_guest_no), $link);
        }
        
    }
}


global $schema_flag;
if( $schema_flag==1) {
   $schema_data='itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" ';
}else{
   $schema_data=' itemscope itemtype="http://schema.org/Product" ';
}
?>  

<div  <?php print trim($schema_data);?>  class="listing_wrapper col-md-12 wide_property property_flex <?php print esc_attr($listing_type_class);?>" data-org="<?php print esc_attr($col_org);?>" data-listid="<?php print esc_attr($post->ID);?>" > 
    <?php if( $schema_flag==1) {?>
        <meta itemprop="position" content="<?php print esc_html($prop_selection->current_post);?>" />
    <?php } ?>
    
    <div class="property_listing " data-link="<?php print esc_url($link);?>">
        <?php
            $featured                 =   intval  ( get_post_meta($post->ID, 'prop_featured', true) );
            $price                    =   intval( get_post_meta($post->ID, 'property_price', true) );
            $property_city            =   get_the_term_list($post->ID, 'property_city', '', ', ', '') ;
            $property_area            =   get_the_term_list($post->ID, 'property_area', '', ', ', '');
            $property_action          =   get_the_term_list($post->ID, 'property_action_category', '', ', ', '');   
            $property_categ           =   get_the_term_list($post->ID, 'property_category', '', ', ', '');
            $wpestate_listing_type    =   wprentals_get_option('wp_estate_listing_unit_type','');
            ?>
        
          
            <?php wpestate_print_property_unit_slider($post->ID,$wpestate_property_unit_slider,$wpestate_listing_type,$wpestate_currency,$wpestate_where_currency,$link,''); ?>
                         
            <?php        
            if($featured==1){
                print '<div class="featured_div">'.esc_html__( 'featured','wprentals').'</div>';
            }
            
            echo wpestate_return_property_status($post->ID);
            
            ?>
          
            <div class="title-container">
                
                <?php echo wprentals_card_owner_image($post->ID); ?>
                
                <?php 
                if(wpestate_has_some_review($post->ID)!==0){
                    print wpestate_display_property_rating( $post->ID ); 
                }else{
                    print '  <div class="rating_placeholder"> </div>';
                }
                ?>
                
                <div class="category_name">
                    <a itemprop="url" href="<?php print esc_url($link);?>" class="listing_title_unit">
                        <span itemprop="name">
                            <?php 
                            echo mb_substr ( html_entity_decode ($title), 0, 36, "UTF8") ; 
                            if(strlen($title)>36){
                                echo '...';   
                            } 
                            ?>
                        </span>    
                    </a>
                    
                    <div class="listing_content">     
                       <?php print wpestate_strip_words( get_the_excerpt(),15).' ...'; ?>
                    </div>
                    
                </div>
            </div>    
                <div class="category_tagline_wrapper">
                    <div class="category_tagline map_icon">
                        <?php  
                        if ($property_area != '') {
                            print trim($property_area).', ';
                        } 
                        print trim($property_city); //escaped abvove                      
                        ?>
                    </div>
                    
                    <div class="category_tagline actions_icon"> 
                        <?php if($wpestate_listing_type==3){
                            $custom_listing_fields = wprentals_get_option('wp_estate_custom_listing_fields', true);
                            foreach ($custom_listing_fields as $field) {
                                if ($field[2] != 'none') {
                                    if ($field[2] == 'property_category' || $field[2] == 'property_action_category' || $field[2] == 'property_city' || $field[2] == 'property_area') {
                                        $value = get_the_term_list($post->ID, $field[2], '', ', ', '');
                                    } else {
                                        $value = get_post_meta($post->ID, $field[2], true);
                                    }

                                    if ($value != '') {
                                        print '<div class="custom_listing_data">';
                                        if ($field[0] != '') {
                                            print '<span class="custom_listing_data_label">' . $field[0] . ':</span>';
                                        } else {
                                            if ($field[1] != '') {
                                                print '<i class="fab fa ' . $field[1] . '"></i>';
                                            }
                                        }

                                        print trim($value);
                                        print '</div>';
                                    }
                                }
                            }
                        } else {?> 
                          
                            <?php print wp_kses_post($property_categ.' / '.$property_action);//escaped above?>
                        <?php }?>
                    </div>
             
                
                    <div class="property_unit_action">                  
                        <span class="icon-fav <?php print esc_attr($favorite_class); ?>" data-original-title="<?php print esc_attr($fav_mes); ?>" data-postid="<?php print intval($post->ID); ?>"><i class="fas fa-heart"></i></span>
                    </div>   
                </div>

        <?php 
 
        if ( isset($show_remove_fav) && $show_remove_fav==1 ) {
            print '<span class="icon-fav icon-fav-on-remove" data-postid="'.intval($post->ID).'"> '.esc_html($fav_mes).'</span>';
        }
        ?>

        </div>          
    </div>